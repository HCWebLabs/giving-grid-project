<?php
/**
 * Admin Service
 * 
 * Business logic for admin functions including org verification.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;

class AdminService
{
    /**
     * Get dashboard stats for admin
     */
    public static function getDashboardStats(): array
    {
        $stats = [];
        
        // Pending verifications
        $stats['pending_verifications'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM organizations WHERE is_verified = 0"
        );
        
        // Pending reports
        $stats['pending_reports'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM reports WHERE status IN ('pending', 'reviewed')"
        );
        
        // Total users
        $stats['total_users'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE is_active = 1"
        );
        
        // Total organizations
        $stats['total_orgs'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM organizations"
        );
        
        // Verified organizations
        $stats['verified_orgs'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM organizations WHERE is_verified = 1"
        );
        
        // Active listings
        $stats['active_listings'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM listings WHERE status = 'open'"
        );
        
        // Completed this week
        $stats['completed_week'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM listings WHERE status = 'fulfilled' AND fulfilled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        // New users this week
        $stats['new_users_week'] = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return $stats;
    }
    
    /**
     * Get organizations pending verification
     */
    public static function getPendingVerifications(): array
    {
        $sql = "
            SELECT 
                o.*,
                u.display_name AS contact_name,
                u.email AS contact_user_email,
                (SELECT COUNT(*) FROM users WHERE org_id = o.id) AS member_count
            FROM organizations o
            LEFT JOIN users u ON u.org_id = o.id
            WHERE o.is_verified = 0
            ORDER BY o.created_at ASC
        ";
        
        $rows = Database::fetchAll($sql);
        
        return array_map(fn($row) => Organization::fromRow($row), $rows);
    }
    
    /**
     * Verify an organization
     */
    public static function verifyOrganization(int $orgId, int $adminId): bool
    {
        $affected = Database::execute(
            "UPDATE organizations SET is_verified = 1, verified_at = NOW(), verified_by = :admin_id WHERE id = :id",
            [':admin_id' => $adminId, ':id' => $orgId]
        );
        
        return $affected > 0;
    }
    
    /**
     * Reject an organization verification (with reason)
     */
    public static function rejectOrganization(int $orgId, int $adminId, ?string $reason = null): bool
    {
        // For now, we just delete the org (in production, you'd want to notify them)
        // First remove any users from the org
        Database::execute(
            "UPDATE users SET org_id = NULL, role = 'individual' WHERE org_id = :id",
            [':id' => $orgId]
        );
        
        // Delete the org
        return Database::execute(
            "DELETE FROM organizations WHERE id = :id AND is_verified = 0",
            [':id' => $orgId]
        ) > 0;
    }
    
    /**
     * Get recent activity log
     */
    public static function getRecentActivity(int $limit = 20): array
    {
        $activities = [];
        
        // Recent listings
        $listings = Database::fetchAll(
            "SELECT 'listing' AS activity_type, id, title AS description, type AS subtype, created_at 
             FROM listings ORDER BY created_at DESC LIMIT :limit",
            [':limit' => $limit]
        );
        
        foreach ($listings as $row) {
            $activities[] = [
                'type' => 'listing',
                'subtype' => $row['subtype'],
                'description' => "New {$row['subtype']}: {$row['description']}",
                'created_at' => $row['created_at'],
            ];
        }
        
        // Recent registrations
        $users = Database::fetchAll(
            "SELECT 'user' AS activity_type, id, display_name AS description, role AS subtype, created_at 
             FROM users ORDER BY created_at DESC LIMIT :limit",
            [':limit' => $limit]
        );
        
        foreach ($users as $row) {
            $activities[] = [
                'type' => 'user',
                'subtype' => $row['subtype'],
                'description' => "New user: {$row['description']}",
                'created_at' => $row['created_at'],
            ];
        }
        
        // Sort by date
        usort($activities, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return array_slice($activities, 0, $limit);
    }
    
    /**
     * Get all users (for admin management)
     */
    public static function getUsers(int $limit = 50, int $offset = 0, ?string $role = null): array
    {
        $params = [':limit' => $limit, ':offset' => $offset];
        $where = '';
        
        if ($role) {
            $where = 'WHERE u.role = :role';
            $params[':role'] = $role;
        }
        
        $sql = "
            SELECT 
                u.*,
                o.name AS org_name,
                o.is_verified AS org_verified,
                (SELECT COUNT(*) FROM listings WHERE user_id = u.id) AS listing_count
            FROM users u
            LEFT JOIN organizations o ON u.org_id = o.id
            {$where}
            ORDER BY u.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Get all organizations (for admin management)
     */
    public static function getOrganizations(int $limit = 50, int $offset = 0, ?bool $verified = null): array
    {
        $params = [':limit' => $limit, ':offset' => $offset];
        $where = '';
        
        if ($verified !== null) {
            $where = 'WHERE o.is_verified = :verified';
            $params[':verified'] = $verified ? 1 : 0;
        }
        
        $sql = "
            SELECT 
                o.*,
                (SELECT COUNT(*) FROM users WHERE org_id = o.id) AS member_count,
                (SELECT COUNT(*) FROM listings WHERE org_id = o.id) AS listing_count,
                verifier.display_name AS verified_by_name
            FROM organizations o
            LEFT JOIN users verifier ON o.verified_by = verifier.id
            {$where}
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $rows = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => Organization::fromRow($row), $rows);
    }
    
    /**
     * Deactivate a user
     */
    public static function deactivateUser(int $userId): bool
    {
        return Database::execute(
            "UPDATE users SET is_active = 0 WHERE id = :id",
            [':id' => $userId]
        ) > 0;
    }
    
    /**
     * Reactivate a user
     */
    public static function reactivateUser(int $userId): bool
    {
        return Database::execute(
            "UPDATE users SET is_active = 1 WHERE id = :id",
            [':id' => $userId]
        ) > 0;
    }
}
