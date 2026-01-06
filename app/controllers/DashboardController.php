<?php
/**
 * Dashboard Controller
 * 
 * Handles user and organization dashboards.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/OrgService.php';
require_once APP_PATH . '/Models/Listing.php';
require_once APP_PATH . '/Models/Organization.php';

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
        $userId = AuthService::id();
        
        // Get user's listings grouped by status
        $allListings = ListingService::getByUserId($userId);
        
        $activeListings = array_filter($allListings, fn($l) => in_array($l->status, ['open', 'in_progress']));
        $fulfilledListings = array_filter($allListings, fn($l) => $l->status === 'fulfilled');
        $closedListings = array_filter($allListings, fn($l) => $l->status === 'closed');
        
        // Stats
        $stats = [
            'total' => count($allListings),
            'active' => count($activeListings),
            'fulfilled' => count($fulfilledListings),
            'closed' => count($closedListings),
        ];
        
        // If org member, get org details
        $organization = null;
        $orgListings = [];
        if ($user['org_id']) {
            $organization = OrgService::getById($user['org_id']);
            
            // Get org's active listings (for org dashboard section)
            $orgListings = ListingService::getByOrgId($user['org_id'], 'open');
        }
        
        render('pages/dashboard-user', [
            'pageTitle' => 'Dashboard',
            'user' => $user,
            'listings' => $activeListings,
            'allListings' => $allListings,
            'stats' => $stats,
            'organization' => $organization,
            'orgListings' => $orgListings,
        ]);
    }
}
