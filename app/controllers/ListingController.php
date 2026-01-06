<?php
/**
 * Listing Controller
 * 
 * Handles individual listing views and CRUD operations.
 * Batch 2 implements: show (detail view)
 * Batch 4 will add: create, store, edit, update, delete
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Models/Listing.php';

use App\Services\ListingService;

class ListingController
{
    /**
     * Display a single listing
     */
    public function show(string $id): void
    {
        $listingId = (int) $id;
        
        if ($listingId <= 0) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Get related listings (same category or county)
        $related = ListingService::browse([
            'type' => $listing->type,
            'county' => $listing->county,
        ], 4);
        
        // Filter out current listing from related
        $relatedListings = array_filter(
            $related['listings'],
            fn($l) => $l->id !== $listing->id
        );
        
        // Limit to 3 related
        $relatedListings = array_slice($relatedListings, 0, 3);
        
        // Build page title
        $typeInfo = $listing->getTypeInfo();
        $pageTitle = $listing->title;
        
        render('pages/listing-detail', [
            'pageTitle' => $pageTitle,
            'metaDescription' => substr(strip_tags($listing->description), 0, 160),
            'listing' => $listing,
            'relatedListings' => $relatedListings,
        ]);
    }
    
    /**
     * Display the post choice page (What would you like to post?)
     * Implemented in Batch 4
     */
    public function create(): void
    {
        // Will be implemented in Batch 4
        render('pages/post-choice', [
            'pageTitle' => 'Post to the Grid'
        ]);
    }
    
    /**
     * Store a new listing
     * Implemented in Batch 4
     */
    public function store(): void
    {
        // Will be implemented in Batch 4
    }
    
    /**
     * Edit a listing
     * Implemented in Batch 4
     */
    public function edit(string $id): void
    {
        // Will be implemented in Batch 4
    }
    
    /**
     * Update a listing
     * Implemented in Batch 4
     */
    public function update(string $id): void
    {
        // Will be implemented in Batch 4
    }
    
    /**
     * Update listing status
     * Implemented in Batch 4
     */
    public function updateStatus(string $id): void
    {
        // Will be implemented in Batch 4
    }
    
    /**
     * Delete a listing
     * Implemented in Batch 4
     */
    public function delete(string $id): void
    {
        // Will be implemented in Batch 4
    }
}
