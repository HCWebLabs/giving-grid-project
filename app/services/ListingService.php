<?php
/**
 * Listing Service
 * 
 * Business logic and database operations for listings.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Listing;

class ListingService
{
    /**
     * Get listings with filters for public browsing
     * 
     * @param array $filters Associative array of filter options
     * @param int $limit Max results to return
     * @param int $offset Pagination offset
     * @return array{listings: Listing[], total: int}
     */
    public static function browse(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = ['l.status = :status'];
        $params = [':status' => 'open'];
        
        // Type filter (need, offer, volunteer)
        if (!empty($filters['type']) && isset(LISTING_TYPES[$filters['type']])) {
            $where[] = 'l.type = :type';
            $params[':type'] = $filters['type'];
        }
        
        // Category filter
        if (!empty($filters['category']) && isset(CATEGORIES[$filters['category']])) {
            $where[] = 'l.category = :category';
            $params[':category'] = $filters['category'];
        }
        
        // County filter
        if (!empty($filters['county']) && isset(COUNTIES[$filters['county']])) {
            $where[] = 'l.county = :county';
            $params[':county'] = $filters['county'];
        }
        
        // Urgency filter
        if (!empty($filters['urgency']) && isset(URGENCY_LEVELS[$filters['urgency']])) {
            $where[] = 'l.urgency = :urgency';
            $params[':urgency'] = $filters['urgency'];
        }
        
        // Cause filter (requires join)
        $causeJoin = '';
        if (!empty($filters['cause'])) {
            $causeJoin = 'INNER JOIN listing_causes lc ON l.id = lc.listing_id
                          INNER JOIN causes c ON lc.cause_id = c.id AND c.slug = :cause_slug';
            $params[':cause_slug'] = $filters['cause'];
        }
        
        // Search query
        if (!empty($filters['q'])) {
            $where[] = '(l.title LIKE :search OR l.description LIKE :search)';
            $params[':search'] = '%' . $filters['q'] . '%';
        }
        
        // Organization filter
        if (!empty($filters['org_id'])) {
            $where[] = 'l.org_id = :org_id';
            $params[':org_id'] = (int) $filters['org_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "
            SELECT COUNT(DISTINCT l.id)
            FROM listings l
            LEFT JOIN organizations o ON l.org_id = o.id
            {$causeJoin}
            WHERE {$whereClause}
        ";
        
        $total = (int) Database::fetchColumn($countSql, $params);
        
        // Get listings
        $sql = "
            SELECT DISTINCT
                l.*,
                o.name AS org_name,
                o.is_verified,
                u.display_name AS poster_name
            FROM listings l
            LEFT JOIN organizations o ON l.org_id = o.id
            LEFT JOIN users u ON l.user_id = u.id
            {$causeJoin}
            WHERE {$whereClause}
            ORDER BY
                FIELD(l.urgency, 'critical', 'high', 'medium', 'low'),
                l.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $rows = Database::fetchAll($sql, $params);
        
        $listings = array_map(fn($row) => Listing::fromRow($row), $rows);
        
        return [
            'listings' => $listings,
            'total' => $total
        ];
    }
    
    /**
     * Get a single listing by ID with full details
     */
    public static function getById(int $id): ?Listing
    {
        $sql = "
            SELECT
                l.*,
                o.name AS org_name,
                o.is_verified,
                o.mission AS org_mission,
                u.display_name AS poster_name
            FROM listings l
            LEFT JOIN organizations o ON l.org_id = o.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
        ";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        $listing = Listing::fromRow($row);
        
        // Load causes
        $listing->causes = self::getCausesForListing($id);
        
        return $listing;
    }
    
    /**
     * Get causes attached to a listing
     */
    public static function getCausesForListing(int $listingId): array
    {
        $sql = "
            SELECT c.*
            FROM causes c
            INNER JOIN listing_causes lc ON c.id = lc.cause_id
            WHERE lc.listing_id = :listing_id
            ORDER BY c.name
        ";
        
        $rows = Database::fetchAll($sql, [':listing_id' => $listingId]);
        
        return array_map(fn($row) => \App\Models\Cause::fromRow($row), $rows);
    }
    
    /**
     * Get listing counts by type (for homepage stats)
     */
    public static function getActiveCounts(): array
    {
        $sql = "
            SELECT 
                type,
                COUNT(*) as count
            FROM listings
            WHERE status = 'open'
            GROUP BY type
        ";
        
        $rows = Database::fetchAll($sql);
        
        $counts = [
            'need' => 0,
            'offer' => 0,
            'volunteer' => 0,
            'total' => 0
        ];
        
        foreach ($rows as $row) {
            $counts[$row['type']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Get recent listings (for homepage)
     */
    public static function getRecent(string $type = null, int $limit = 5): array
    {
        $where = 'l.status = :status';
        $params = [':status' => 'open'];
        
        if ($type && isset(LISTING_TYPES[$type])) {
            $where .= ' AND l.type = :type';
            $params[':type'] = $type;
        }
        
        $sql = "
            SELECT
                l.*,
                o.name AS org_name,
                o.is_verified,
                u.display_name AS poster_name
            FROM listings l
            LEFT JOIN organizations o ON l.org_id = o.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE {$where}
            ORDER BY l.created_at DESC
            LIMIT :limit
        ";
        
        $params[':limit'] = $limit;
        
        $rows = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => Listing::fromRow($row), $rows);
    }
    
    /**
     * Get listings by user ID
     */
    public static function getByUserId(int $userId, ?string $status = null): array
    {
        $where = 'l.user_id = :user_id';
        $params = [':user_id' => $userId];
        
        if ($status) {
            $where .= ' AND l.status = :status';
            $params[':status'] = $status;
        }
        
        $sql = "
            SELECT
                l.*,
                o.name AS org_name,
                o.is_verified
            FROM listings l
            LEFT JOIN organizations o ON l.org_id = o.id
            WHERE {$where}
            ORDER BY l.created_at DESC
        ";
        
        $rows = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => Listing::fromRow($row), $rows);
    }
    
    /**
     * Get listings by organization ID
     */
    public static function getByOrgId(int $orgId, ?string $status = null, ?string $type = null): array
    {
        $where = 'l.org_id = :org_id';
        $params = [':org_id' => $orgId];
        
        if ($status) {
            $where .= ' AND l.status = :status';
            $params[':status'] = $status;
        }
        
        if ($type) {
            $where .= ' AND l.type = :type';
            $params[':type'] = $type;
        }
        
        $sql = "
            SELECT l.*
            FROM listings l
            WHERE {$where}
            ORDER BY
                FIELD(l.urgency, 'critical', 'high', 'medium', 'low'),
                l.created_at DESC
        ";
        
        $rows = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => Listing::fromRow($row), $rows);
    }
    
    /**
     * Increment response count (placeholder for future)
     */
    public static function incrementResponseCount(int $listingId): void
    {
        // Will be implemented when we add response tracking
    }
    
    /**
     * Create a new listing
     * 
     * @param array $data Validated listing data
     * @param int $userId User creating the listing
     * @param int|null $orgId Organization ID (if posting as org)
     * @return int New listing ID
     */
    public static function create(array $data, int $userId, ?int $orgId = null): int
    {
        $sql = "
            INSERT INTO listings (
                type, title, description, category, quantity,
                county, city, urgency, status, logistics,
                contact_method, user_id, org_id, created_at, updated_at
            ) VALUES (
                :type, :title, :description, :category, :quantity,
                :county, :city, :urgency, 'open', :logistics,
                :contact_method, :user_id, :org_id, NOW(), NOW()
            )
        ";
        
        $listingId = Database::insert($sql, [
            ':type' => $data['type'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':category' => $data['category'],
            ':quantity' => $data['quantity'] ?? null,
            ':county' => $data['county'],
            ':city' => $data['city'] ?? null,
            ':urgency' => $data['urgency'] ?? 'medium',
            ':logistics' => $data['logistics'] ?? 'na',
            ':contact_method' => $data['contact_method'] ?? null,
            ':user_id' => $userId,
            ':org_id' => $orgId,
        ]);
        
        // Attach causes if provided
        if (!empty($data['causes'])) {
            self::syncCauses($listingId, $data['causes']);
        }
        
        return $listingId;
    }
    
    /**
     * Update an existing listing
     * 
     * @param int $listingId Listing to update
     * @param array $data Validated update data
     * @return bool Success
     */
    public static function update(int $listingId, array $data): bool
    {
        $sets = ['updated_at = NOW()'];
        $params = [':id' => $listingId];
        
        $allowedFields = [
            'title', 'description', 'category', 'quantity',
            'county', 'city', 'urgency', 'logistics', 'contact_method'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        $sql = "UPDATE listings SET " . implode(', ', $sets) . " WHERE id = :id";
        
        $affected = Database::execute($sql, $params);
        
        // Update causes if provided
        if (isset($data['causes'])) {
            self::syncCauses($listingId, $data['causes']);
        }
        
        return $affected > 0;
    }
    
    /**
     * Update listing status
     * 
     * @param int $listingId Listing to update
     * @param string $status New status
     * @return bool Success
     */
    public static function updateStatus(int $listingId, string $status): bool
    {
        $params = [
            ':id' => $listingId,
            ':status' => $status,
        ];
        
        $fulfilledAt = $status === 'fulfilled' ? ', fulfilled_at = NOW()' : '';
        
        $sql = "UPDATE listings SET status = :status, updated_at = NOW(){$fulfilledAt} WHERE id = :id";
        
        return Database::execute($sql, $params) > 0;
    }
    
    /**
     * Delete a listing (soft delete by closing)
     * 
     * @param int $listingId Listing to delete
     * @return bool Success
     */
    public static function delete(int $listingId): bool
    {
        // We'll soft delete by setting status to closed
        return self::updateStatus($listingId, 'closed');
    }
    
    /**
     * Hard delete a listing (admin only)
     * 
     * @param int $listingId Listing to delete
     * @return bool Success
     */
    public static function hardDelete(int $listingId): bool
    {
        // Delete cause associations first
        Database::execute("DELETE FROM listing_causes WHERE listing_id = :id", [':id' => $listingId]);
        
        // Delete responses
        Database::execute("DELETE FROM responses WHERE listing_id = :id", [':id' => $listingId]);
        
        // Delete the listing
        return Database::execute("DELETE FROM listings WHERE id = :id", [':id' => $listingId]) > 0;
    }
    
    /**
     * Sync causes for a listing
     * 
     * @param int $listingId Listing ID
     * @param array $causeIds Array of cause IDs
     */
    public static function syncCauses(int $listingId, array $causeIds): void
    {
        // Remove existing
        Database::execute("DELETE FROM listing_causes WHERE listing_id = :id", [':id' => $listingId]);
        
        // Add new (max 2)
        $causeIds = array_slice(array_unique(array_filter($causeIds)), 0, 2);
        
        foreach ($causeIds as $causeId) {
            Database::execute(
                "INSERT IGNORE INTO listing_causes (listing_id, cause_id) VALUES (:listing_id, :cause_id)",
                [':listing_id' => $listingId, ':cause_id' => (int) $causeId]
            );
        }
    }
    
    /**
     * Check if a user owns a listing
     * 
     * @param int $listingId Listing ID
     * @param int $userId User ID
     * @return bool
     */
    public static function isOwner(int $listingId, int $userId): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM listings WHERE id = :listing_id AND user_id = :user_id",
            [':listing_id' => $listingId, ':user_id' => $userId]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Check if a user can edit a listing (owner or admin)
     * 
     * @param int $listingId Listing ID
     * @param int $userId User ID
     * @param bool $isAdmin Is the user an admin
     * @return bool
     */
    public static function canEdit(int $listingId, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return true;
        }
        
        return self::isOwner($listingId, $userId);
    }
}
