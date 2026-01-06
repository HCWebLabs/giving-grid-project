<?php
/**
 * Report Controller
 * 
 * Handles user reports on listings and users.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ReportService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Models/Report.php';

use App\Services\ReportService;
use App\Services\ListingService;
use App\Services\AuthService;

class ReportController
{
    /**
     * Show report form for a listing
     */
    public function form(string $id): void
    {
        $listingId = (int) $id;
        
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Cannot report your own listing
        if (AuthService::check() && $listing->user_id === AuthService::id()) {
            flashError('You cannot report your own listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Check if already reported (if logged in)
        $alreadyReported = false;
        if (AuthService::check()) {
            $alreadyReported = ReportService::hasReported('listing', $listingId, AuthService::id());
        }
        
        $errors = $_SESSION['errors'] ?? [];
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        
        render('pages/report-form', [
            'pageTitle' => 'Report Listing',
            'type' => 'listing',
            'target' => $listing,
            'alreadyReported' => $alreadyReported,
            'errors' => $errors,
            'oldInput' => $oldInput,
        ]);
    }
    
    /**
     * Store a report
     */
    public function store(): void
    {
        $type = $_POST['type'] ?? '';
        $targetId = (int) ($_POST['target_id'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        $details = trim($_POST['details'] ?? '');
        
        // Validate type
        if (!in_array($type, ['listing', 'user', 'response'])) {
            flashError('Invalid report type.');
            redirectTo('/');
            return;
        }
        
        // Validate target exists
        if ($type === 'listing') {
            $target = ListingService::getById($targetId);
            if (!$target) {
                flashError('Listing not found.');
                redirectTo('/browse');
                return;
            }
            $redirectUrl = "/listing/{$targetId}";
        } else {
            $redirectUrl = '/';
        }
        
        // Validate reason
        if (!isset(REPORT_REASONS[$reason])) {
            $_SESSION['errors'] = ['reason' => 'Please select a reason for your report.'];
            $_SESSION['old_input'] = $_POST;
            redirectTo("/report/listing/{$targetId}");
            return;
        }
        
        // Validate details for "other" reason
        if ($reason === 'other' && strlen($details) < 10) {
            $_SESSION['errors'] = ['details' => 'Please provide more details (at least 10 characters).'];
            $_SESSION['old_input'] = $_POST;
            redirectTo("/report/listing/{$targetId}");
            return;
        }
        
        // Check for duplicate (if logged in)
        $userId = AuthService::check() ? AuthService::id() : null;
        if ($userId && ReportService::hasReported($type, $targetId, $userId)) {
            flashInfo('You have already reported this item. Our team will review it.');
            redirectTo($redirectUrl);
            return;
        }
        
        // Create the report
        ReportService::create([
            'type' => $type,
            'target_id' => $targetId,
            'reporter_id' => $userId,
            'reason' => $reason,
            'details' => $details ?: null,
        ]);
        
        flashSuccess('Thank you for your report. Our team will review it shortly.');
        redirectTo($redirectUrl);
    }
}
