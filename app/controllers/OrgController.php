<?php
/**
 * Organization Controller
 * 
 * Handles organization directory and profile views.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/OrgService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Models/Organization.php';

use App\Services\OrgService;
use App\Services\ListingService;

class OrgController
{
    /**
     * Display organization directory
     */
    public function index(): void
    {
        // Get filter parameters
        $filters = [
            'county' => $_GET['county'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        
        // Clean empty filters
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        
        // Pagination
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        
        // Get organizations
        $result = OrgService::browse($filters, $perPage, $offset);
        $organizations = $result['organizations'];
        $total = $result['total'];
        
        // Calculate pagination
        $totalPages = (int) ceil($total / $perPage);
        
        render('pages/organizations', [
            'pageTitle' => 'Organizations',
            'metaDescription' => 'Verified nonprofit organizations and community groups in East Tennessee.',
            'organizations' => $organizations,
            'filters' => $filters,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ]);
    }
    
    /**
     * Display a single organization profile
     */
    public function show(string $id): void
    {
        $orgId = (int) $id;
        
        if ($orgId <= 0) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Organization Not Found']);
            return;
        }
        
        $organization = OrgService::getById($orgId);
        
        if (!$organization) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Organization Not Found']);
            return;
        }
        
        // Non-verified orgs are not publicly visible
        if (!$organization->is_verified) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Organization Not Found']);
            return;
        }
        
        // Get organization's active listings
        $needs = ListingService::getByOrgId($orgId, 'open', 'need');
        $offers = ListingService::getByOrgId($orgId, 'open', 'offer');
        $volunteerOpps = ListingService::getByOrgId($orgId, 'open', 'volunteer');
        
        render('pages/org-profile', [
            'pageTitle' => $organization->name,
            'metaDescription' => $organization->mission 
                ? substr(strip_tags($organization->mission), 0, 160) 
                : "Learn about {$organization->name} and their community work in East Tennessee.",
            'organization' => $organization,
            'needs' => $needs,
            'offers' => $offers,
            'volunteerOpps' => $volunteerOpps,
        ]);
    }
}
