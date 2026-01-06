<?php
/**
 * Organization Service
 * 
 * Business logic and database operations for organizations.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;

class OrgService
{
    /**
     * Get all verified organizations for public directory
     * 
     * @param array $filters Filter options
     * @param int $limit Max results
     * @param int $offset Pagination offset
     * @return array{organizations: Organization[], total: int}
     */
    public static function browse(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = ['o.is_verified = 1'];
        $params = [];
        
        // County filter
        if (!empty($filters['county']) && isset(COUNTIES[$filters['county']])) {
            $where[] = 'o.county_served = :county';
            $params[':county'] = $filters['county'];
        }
        
        // Search query
        if (!empty($filters['q'])) {
            $where[] = '(o.name LIKE :search OR o.mission LIKE :search)';
            $params[':search'] = '%' . $filters['q'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM organizations o WHERE {$whereClause}";
        $total = (int) Database::fetchColumn($countSql, $params);
        
        // Get organizations with listing counts
        $sql = "
            SELECT 
                o.*,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'need' AND l.status = 'open') AS active_needs_count,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'offer' AND l.status = 'open') AS active_offers_count,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'volunteer' AND l.status = 'open') AS volunteer_count
            FROM organizations o
            WHERE {$whereClause}
            ORDER BY o.name ASC
            LIMIT :limit OFFSET :offset
        ";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $rows = Database::fetchAll($sql, $params);
        
        $organizations = array_map(fn($row) => Organization::fromRow($row), $rows);
        
        return [
            'organizations' => $organizations,
            'total' => $total
        ];
    }
    
    /**
     * Get a single organization by ID
     */
    public static function getById(int $id): ?Organization
    {
        $sql = "
            SELECT 
                o.*,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'need' AND l.status = 'open') AS active_needs_count,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'offer' AND l.status = 'open') AS active_offers_count,
                (SELECT COUNT(*) FROM listings l WHERE l.org_id = o.id AND l.type = 'volunteer' AND l.status = 'open') AS volunteer_count
            FROM organizations o
            WHERE o.id = :id
        ";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        return Organization::fromRow($row);
    }
    
    /**
     * Get total count of verified organizations
     */
    public static function getVerifiedCount(): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM organizations WHERE is_verified = 1"
        );
    }
    
    /**
     * Get organizations pending verification (admin)
     */
    public static function getPendingVerification(): array
    {
        $sql = "
            SELECT o.*
            FROM organizations o
            WHERE o.is_verified = 0
            ORDER BY o.created_at ASC
        ";
        
        $rows = Database::fetchAll($sql);
        
        return array_map(fn($row) => Organization::fromRow($row), $rows);
    }
    
    /**
     * Check if a user is a member of an organization
     */
    public static function isUserMember(int $userId, int $orgId): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE id = :user_id AND org_id = :org_id",
            [':user_id' => $userId, ':org_id' => $orgId]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Get organization by user ID
     */
    public static function getByUserId(int $userId): ?Organization
    {
        $sql = "
            SELECT o.*
            FROM organizations o
            INNER JOIN users u ON u.org_id = o.id
            WHERE u.id = :user_id
        ";
        
        $row = Database::fetch($sql, [':user_id' => $userId]);
        
        if (!$row) {
            return null;
        }
        
        return Organization::fromRow($row);
    }
}
