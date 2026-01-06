<?php
/**
 * Home Controller
 * 
 * Handles the homepage and public landing pages.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/OrgService.php';
require_once APP_PATH . '/Models/Listing.php';

use App\Services\ListingService;
use App\Services\OrgService;

class HomeController
{
    /**
     * Display the homepage
     */
    public function index(): void
    {
        // Get live stats
        $stats = ListingService::getActiveCounts();
        $orgCount = OrgService::getVerifiedCount();
        
        // Get recent listings for each type
        $recentNeeds = ListingService::getRecent('need', 3);
        $recentOffers = ListingService::getRecent('offer', 3);
        
        render('pages/home', [
            'pageTitle' => 'Home',
            'metaDescription' => 'The Giving Grid connects needs, surplus, and volunteers across Tennessee communities. Find help, offer resources, or volunteer your time.',
            'stats' => $stats,
            'orgCount' => $orgCount,
            'recentNeeds' => $recentNeeds,
            'recentOffers' => $recentOffers,
        ]);
    }
}
