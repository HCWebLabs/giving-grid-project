<?php
/**
 * Admin Controller
 * 
 * Handles admin dashboard, verification queue, and report management.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/AdminService.php';
require_once APP_PATH . '/Services/ReportService.php';
require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Services/OrgService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Models/Report.php';
require_once APP_PATH . '/Models/Organization.php';

use App\Services\AdminService;
use App\Services\ReportService;
use App\Services\AuthService;
use App\Services\OrgService;
use App\Services\ListingService;

class AdminController
{
    /**
     * Admin dashboard
     */
    public function index(): void
    {
        $stats = AdminService::getDashboardStats();
        $recentActivity = AdminService::getRecentActivity(10);
        $pendingVerifications = AdminService::getPendingVerifications();
        $pendingReports = ReportService::getPending(5);
        
        render('pages/admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'pendingVerifications' => array_slice($pendingVerifications, 0, 5),
            'pendingReports' => $pendingReports,
        ]);
    }
    
    /**
     * Organization verification queue
     */
    public function verifyQueue(): void
    {
        $pending = AdminService::getPendingVerifications();
        $verified = AdminService::getOrganizations(20, 0, true);
        
        render('pages/admin/verify-queue', [
            'pageTitle' => 'Organization Verification',
            'pendingOrgs' => $pending,
            'verifiedOrgs' => $verified,
        ]);
    }
    
    /**
     * Verify an organization
     */
    public function verify(string $id): void
    {
        $orgId = (int) $id;
        $adminId = AuthService::id();
        
        $org = OrgService::getById($orgId);
        
        if (!$org) {
            flashError('Organization not found.');
            redirectTo('/admin/verify');
            return;
        }
        
        if ($org->is_verified) {
            flashInfo('This organization is already verified.');
            redirectTo('/admin/verify');
            return;
        }
        
        AdminService::verifyOrganization($orgId, $adminId);
        
        flashSuccess("Organization '{$org->name}' has been verified.");
        redirectTo('/admin/verify');
    }
    
    /**
     * Reject an organization
     */
    public function reject(string $id): void
    {
        $orgId = (int) $id;
        $adminId = AuthService::id();
        $reason = $_POST['reason'] ?? '';
        
        $org = OrgService::getById($orgId);
        
        if (!$org) {
            flashError('Organization not found.');
            redirectTo('/admin/verify');
            return;
        }
        
        if ($org->is_verified) {
            flashError('Cannot reject an already verified organization.');
            redirectTo('/admin/verify');
            return;
        }
        
        AdminService::rejectOrganization($orgId, $adminId, $reason);
        
        flashSuccess("Organization has been rejected and removed.");
        redirectTo('/admin/verify');
    }
    
    /**
     * Reports queue
     */
    public function reportsQueue(): void
    {
        $status = $_GET['status'] ?? null;
        $reports = $status 
            ? ReportService::getAll(50, 0, $status)
            : ReportService::getPending();
        
        $counts = ReportService::getCounts();
        
        render('pages/admin/reports', [
            'pageTitle' => 'Reports',
            'reports' => $reports,
            'counts' => $counts,
            'currentStatus' => $status,
        ]);
    }
    
    /**
     * View a single report
     */
    public function viewReport(string $id): void
    {
        $reportId = (int) $id;
        
        $report = ReportService::getById($reportId);
        
        if (!$report) {
            flashError('Report not found.');
            redirectTo('/admin/reports');
            return;
        }
        
        // Get the target details
        $target = null;
        if ($report->type === 'listing') {
            $target = ListingService::getById($report->target_id);
        }
        
        render('pages/admin/report-detail', [
            'pageTitle' => 'Report #' . $report->id,
            'report' => $report,
            'target' => $target,
        ]);
    }
    
    /**
     * Resolve a report
     */
    public function resolveReport(string $id): void
    {
        $reportId = (int) $id;
        $adminId = AuthService::id();
        $action = $_POST['action'] ?? 'dismiss';
        $notes = trim($_POST['notes'] ?? '');
        
        $report = ReportService::getById($reportId);
        
        if (!$report) {
            flashError('Report not found.');
            redirectTo('/admin/reports');
            return;
        }
        
        if (!$report->isActionable()) {
            flashError('This report has already been processed.');
            redirectTo('/admin/reports');
            return;
        }
        
        if ($action === 'dismiss') {
            ReportService::dismiss($reportId, $adminId, $notes);
            flashSuccess('Report dismissed.');
        } elseif ($action === 'close_listing') {
            ReportService::resolve($reportId, $adminId, 'close_listing', $notes);
            flashSuccess('Report resolved and listing closed.');
        } elseif ($action === 'deactivate_user') {
            ReportService::resolve($reportId, $adminId, 'deactivate_user', $notes);
            flashSuccess('Report resolved and user deactivated.');
        } else {
            ReportService::updateStatus($reportId, 'resolved', $adminId, $notes);
            flashSuccess('Report resolved.');
        }
        
        redirectTo('/admin/reports');
    }
}
