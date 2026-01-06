<?php
/**
 * Dashboard Controller
 * 
 * Handles user and organization dashboards.
 * Full implementation in Batch 4.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/OrgService.php';

use App\Services\AuthService;
use App\Services\ListingService;
use App\Services\OrgService;

class DashboardController
{
    /**
     * Display user dashboard
     */
    public function index(): void
    {
        $user = AuthService::user();
        
        // Get user's listings
        $listings = ListingService::getByUserId(AuthService::id());
        
        // If org member, get org details
        $organization = null;
        if ($user['org_id']) {
            $organization = OrgService::getById($user['org_id']);
        }
        
        render('pages/dashboard-user', [
            'pageTitle' => 'Dashboard',
            'user' => $user,
            'listings' => $listings,
            'organization' => $organization,
        ]);
    }
}
