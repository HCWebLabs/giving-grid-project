<?php
/**
 * Report Service
 * 
 * Business logic for user reports and moderation.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Report;

class ReportService
{
    /**
     * Create a new report
     */
    public static function create(array $data): int
    {
        $sql = "
            INSERT INTO reports (type, target_id, reporter_id, reason, details, status, created_at)
            VALUES (:type, :target_id, :reporter_id, :reason, :details, 'pending', NOW())
        ";
        
        return Database::insert($sql, [
            ':type' => $data['type'],
            ':target_id' => $data['target_id'],
            ':reporter_id' => $data['reporter_id'] ?? null,
            ':reason' => $data['reason'],
            ':details' => $data['details'] ?? null,
        ]);
    }
    
    /**
     * Get a report by ID
     */
    public static function getById(int $id): ?Report
    {
        $sql = "
            SELECT 
                r.*,
                reporter.display_name AS reporter_name,
                reporter.email AS reporter_email,
                resolver.display_name AS resolver_name
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users resolver ON r.resolved_by = resolver.id
            WHERE r.id = :id
        ";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        $report = Report::fromRow($row);
        
        // Load target info based on type
        if ($report->type === 'listing') {
            $listing = Database::fetch(
                "SELECT title FROM listings WHERE id = :id",
                [':id' => $report->target_id]
            );
            $report->target_title = $listing['title'] ?? '[Deleted]';
        } elseif ($report->type === 'user') {
            $user = Database::fetch(
                "SELECT display_name FROM users WHERE id = :id",
                [':id' => $report->target_id]
            );
            $report->target_name = $user['display_name'] ?? '[Deleted]';
        }
        
        return $report;
    }
    
    /**
     * Get pending reports (for admin)
     */
    public static function getPending(int $limit = 50): array
    {
        $sql = "
            SELECT 
                r.*,
                reporter.display_name AS reporter_name,
                CASE 
                    WHEN r.type = 'listing' THEN (SELECT title FROM listings WHERE id = r.target_id)
                    WHEN r.type = 'user' THEN (SELECT display_name FROM users WHERE id = r.target_id)
                    ELSE NULL
                END AS target_title
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            WHERE r.status IN ('pending', 'reviewed')
            ORDER BY 
                FIELD(r.status, 'pending', 'reviewed'),
                r.created_at ASC
            LIMIT :limit
        ";
        
        $rows = Database::fetchAll($sql, [':limit' => $limit]);
        
        return array_map(fn($row) => Report::fromRow($row), $rows);
    }
    
    /**
     * Get all reports with pagination
     */
    public static function getAll(int $limit = 50, int $offset = 0, ?string $status = null): array
    {
        $params = [':limit' => $limit, ':offset' => $offset];
        $where = '';
        
        if ($status) {
            $where = 'WHERE r.status = :status';
            $params[':status'] = $status;
        }
        
        $sql = "
            SELECT 
                r.*,
                reporter.display_name AS reporter_name,
                resolver.display_name AS resolver_name,
                CASE 
                    WHEN r.type = 'listing' THEN (SELECT title FROM listings WHERE id = r.target_id)
                    WHEN r.type = 'user' THEN (SELECT display_name FROM users WHERE id = r.target_id)
                    ELSE NULL
                END AS target_title
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users resolver ON r.resolved_by = resolver.id
            {$where}
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $rows = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => Report::fromRow($row), $rows);
    }
    
    /**
     * Get report counts by status
     */
    public static function getCounts(): array
    {
        $sql = "
            SELECT status, COUNT(*) as count
            FROM reports
            GROUP BY status
        ";
        
        $rows = Database::fetchAll($sql);
        
        $counts = [
            'pending' => 0,
            'reviewed' => 0,
            'resolved' => 0,
            'dismissed' => 0,
            'total' => 0,
        ];
        
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Update report status
     */
    public static function updateStatus(int $reportId, string $status, int $adminId, ?string $notes = null): bool
    {
        $resolvedAt = in_array($status, ['resolved', 'dismissed']) ? ', resolved_at = NOW()' : '';
        
        $sql = "
            UPDATE reports 
            SET status = :status, 
                resolved_by = :admin_id, 
                admin_notes = :notes
                {$resolvedAt}
            WHERE id = :id
        ";
        
        return Database::execute($sql, [
            ':status' => $status,
            ':admin_id' => $adminId,
            ':notes' => $notes,
            ':id' => $reportId,
        ]) > 0;
    }
    
    /**
     * Resolve a report (take action)
     */
    public static function resolve(int $reportId, int $adminId, string $action, ?string $notes = null): bool
    {
        $report = self::getById($reportId);
        
        if (!$report) {
            return false;
        }
        
        // Handle the action
        if ($action === 'close_listing' && $report->type === 'listing') {
            ListingService::updateStatus($report->target_id, 'closed');
        } elseif ($action === 'deactivate_user' && $report->type === 'user') {
            Database::execute(
                "UPDATE users SET is_active = 0 WHERE id = :id",
                [':id' => $report->target_id]
            );
        }
        
        // Mark report as resolved
        return self::updateStatus($reportId, 'resolved', $adminId, $notes);
    }
    
    /**
     * Dismiss a report (no action needed)
     */
    public static function dismiss(int $reportId, int $adminId, ?string $notes = null): bool
    {
        return self::updateStatus($reportId, 'dismissed', $adminId, $notes);
    }
    
    /**
     * Check if user has already reported a target
     */
    public static function hasReported(string $type, int $targetId, int $userId): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM reports WHERE type = :type AND target_id = :target_id AND reporter_id = :user_id",
            [':type' => $type, ':target_id' => $targetId, ':user_id' => $userId]
        );
        
        return (int) $count > 0;
    }
}
