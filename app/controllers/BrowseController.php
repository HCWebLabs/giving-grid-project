<?php
/**
 * Browse Controller
 * 
 * Handles public browsing of listings with filters.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/CauseService.php';
require_once APP_PATH . '/Models/Listing.php';
require_once APP_PATH . '/Models/Cause.php';

use App\Services\ListingService;
use App\Services\CauseService;

class BrowseController
{
    /**
     * Display browse listings page with filters
     */
    public function index(): void
    {
        // Get filter parameters from query string
        $filters = [
            'type' => $_GET['type'] ?? null,
            'category' => $_GET['category'] ?? null,
            'county' => $_GET['county'] ?? null,
            'urgency' => $_GET['urgency'] ?? null,
            'cause' => $_GET['cause'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        
        // Clean empty filters
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        
        // Pagination
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        
        // Get listings
        $result = ListingService::browse($filters, $perPage, $offset);
        $listings = $result['listings'];
        $total = $result['total'];
        
        // Calculate pagination
        $totalPages = (int) ceil($total / $perPage);
        
        // Get causes for filter dropdown
        $causes = CauseService::getAllWithCounts();
        
        // Build page title based on filters
        $pageTitle = 'Browse';
        if (!empty($filters['type'])) {
            $typeInfo = getListingType($filters['type']);
            $pageTitle = 'Browse ' . ($typeInfo['plural'] ?? ucfirst($filters['type']));
        }
        
        render('pages/browse', [
            'pageTitle' => $pageTitle,
            'metaDescription' => 'Browse community needs, offers, and volunteer opportunities in East Tennessee.',
            'listings' => $listings,
            'filters' => $filters,
            'causes' => $causes,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ]);
    }
}
