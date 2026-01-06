<?php
/**
 * Report Model
 * 
 * Represents a user report on a listing or user.
 */

declare(strict_types=1);

namespace App\Models;

class Report
{
    public int $id;
    public string $type; // listing, user, response
    public int $target_id;
    public ?int $reporter_id;
    public string $reason;
    public ?string $details;
    public string $status; // pending, reviewed, resolved, dismissed
    public ?string $admin_notes;
    public ?int $resolved_by;
    public ?string $resolved_at;
    public string $created_at;
    
    // Joined fields
    public ?string $reporter_name;
    public ?string $reporter_email;
    public ?string $target_title; // For listing reports
    public ?string $target_name; // For user reports
    public ?string $resolver_name;
    
    /**
     * Create a Report from a database row
     */
    public static function fromRow(array $row): self
    {
        $report = new self();
        
        $report->id = (int) $row['id'];
        $report->type = $row['type'];
        $report->target_id = (int) $row['target_id'];
        $report->reporter_id = isset($row['reporter_id']) ? (int) $row['reporter_id'] : null;
        $report->reason = $row['reason'];
        $report->details = $row['details'] ?? null;
        $report->status = $row['status'];
        $report->admin_notes = $row['admin_notes'] ?? null;
        $report->resolved_by = isset($row['resolved_by']) ? (int) $row['resolved_by'] : null;
        $report->resolved_at = $row['resolved_at'] ?? null;
        $report->created_at = $row['created_at'];
        
        // Joined fields
        $report->reporter_name = $row['reporter_name'] ?? null;
        $report->reporter_email = $row['reporter_email'] ?? null;
        $report->target_title = $row['target_title'] ?? null;
        $report->target_name = $row['target_name'] ?? null;
        $report->resolver_name = $row['resolver_name'] ?? null;
        
        return $report;
    }
    
    /**
     * Get reason display label
     */
    public function getReasonLabel(): string
    {
        return REPORT_REASONS[$this->reason]['label'] ?? ucfirst($this->reason);
    }
    
    /**
     * Get status display info
     */
    public function getStatusInfo(): array
    {
        $statuses = [
            'pending' => ['label' => 'Pending', 'color' => 'yellow'],
            'reviewed' => ['label' => 'Under Review', 'color' => 'blue'],
            'resolved' => ['label' => 'Resolved', 'color' => 'green'],
            'dismissed' => ['label' => 'Dismissed', 'color' => 'gray'],
        ];
        
        return $statuses[$this->status] ?? ['label' => ucfirst($this->status), 'color' => 'gray'];
    }
    
    /**
     * Check if report is actionable
     */
    public function isActionable(): bool
    {
        return in_array($this->status, ['pending', 'reviewed']);
    }
    
    /**
     * Get relative time since creation
     */
    public function getTimeAgo(): string
    {
        $timestamp = strtotime($this->created_at);
        $diff = time() - $timestamp;
        
        if ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}
