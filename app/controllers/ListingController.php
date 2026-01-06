<?php
/**
 * Listing Controller
 * 
 * Handles individual listing views and CRUD operations.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/CauseService.php';
require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Validation/ListingValidator.php';
require_once APP_PATH . '/Models/Listing.php';

use App\Services\ListingService;
use App\Services\CauseService;
use App\Services\AuthService;
use App\Validation\ListingValidator;

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
        
        // Check if current user is the owner
        $isOwner = AuthService::check() && ListingService::isOwner($listingId, AuthService::id());
        
        // Build page title
        $typeInfo = $listing->getTypeInfo();
        $pageTitle = $listing->title;
        
        render('pages/listing-detail', [
            'pageTitle' => $pageTitle,
            'metaDescription' => substr(strip_tags($listing->description), 0, 160),
            'listing' => $listing,
            'relatedListings' => $relatedListings,
            'isOwner' => $isOwner,
        ]);
    }
    
    /**
     * Display the post choice page (What would you like to post?)
     */
    public function create(): void
    {
        $user = AuthService::user();
        $hasVerifiedOrg = AuthService::hasVerifiedOrg();
        
        // Determine what types user can post
        $canPostNeeds = $hasVerifiedOrg;
        $canPostOffers = true; // Anyone can post offers
        $canPostVolunteer = $hasVerifiedOrg;
        
        // Check if type is pre-selected via query string
        $preselectedType = $_GET['type'] ?? null;
        
        // If type is specified and valid, go directly to form
        if ($preselectedType && isset(LISTING_TYPES[$preselectedType])) {
            // Check permission for this type
            if ($preselectedType === 'need' && !$canPostNeeds) {
                flashError('Only verified organizations can post needs.');
                redirectTo('/post');
            }
            if ($preselectedType === 'volunteer' && !$canPostVolunteer) {
                flashError('Only verified organizations can post volunteer opportunities.');
                redirectTo('/post');
            }
            
            $this->showPostForm($preselectedType);
            return;
        }
        
        render('pages/post-choice', [
            'pageTitle' => 'Post to the Grid',
            'canPostNeeds' => $canPostNeeds,
            'canPostOffers' => $canPostOffers,
            'canPostVolunteer' => $canPostVolunteer,
            'hasVerifiedOrg' => $hasVerifiedOrg,
        ]);
    }
    
    /**
     * Show the post form for a specific type
     */
    private function showPostForm(string $type): void
    {
        $user = AuthService::user();
        $typeInfo = LISTING_TYPES[$type];
        $causes = CauseService::getAll();
        
        // Get old input if validation failed
        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['errors']);
        
        // Pre-fill county from user profile
        if (empty($oldInput['county']) && !empty($user['county'])) {
            $oldInput['county'] = $user['county'];
        }
        
        render('pages/post-form', [
            'pageTitle' => 'Post a ' . $typeInfo['label'],
            'type' => $type,
            'typeInfo' => $typeInfo,
            'causes' => $causes,
            'oldInput' => $oldInput,
            'errors' => $errors,
            'isEdit' => false,
        ]);
    }
    
    /**
     * Store a new listing
     */
    public function store(): void
    {
        $user = AuthService::user();
        $hasVerifiedOrg = AuthService::hasVerifiedOrg();
        
        // Build permissions array
        $permissions = [
            'can_post_needs' => $hasVerifiedOrg,
            'can_post_offers' => true,
            'can_post_volunteer' => $hasVerifiedOrg,
        ];
        
        // Validate
        $validator = new ListingValidator();
        $validator->validateCreate($_POST, $permissions);
        
        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            
            $type = $_POST['type'] ?? '';
            $redirect = $type ? "/post?type={$type}" : '/post';
            redirectTo($redirect);
        }
        
        $data = $validator->validated();
        
        // Determine org_id (only if user is org member and posting need/volunteer)
        $orgId = null;
        if ($hasVerifiedOrg && in_array($data['type'], ['need', 'volunteer'])) {
            $orgId = $user['org_id'];
        }
        
        // Create the listing
        $listingId = ListingService::create($data, AuthService::id(), $orgId);
        
        flashSuccess('Your listing has been posted to the Grid!');
        redirectTo("/listing/{$listingId}");
    }
    
    /**
     * Edit a listing
     */
    public function edit(string $id): void
    {
        $listingId = (int) $id;
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Check permission
        $isAdmin = AuthService::isAdmin();
        if (!ListingService::canEdit($listingId, AuthService::id(), $isAdmin)) {
            flashError('You do not have permission to edit this listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        $typeInfo = LISTING_TYPES[$listing->type];
        $causes = CauseService::getAll();
        
        // Get old input if validation failed, otherwise use listing data
        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['errors']);
        
        if (empty($oldInput)) {
            $oldInput = [
                'title' => $listing->title,
                'description' => $listing->description,
                'category' => $listing->category,
                'quantity' => $listing->quantity,
                'county' => $listing->county,
                'city' => $listing->city,
                'urgency' => $listing->urgency,
                'logistics' => $listing->logistics,
                'contact_method' => $listing->contact_method,
                'causes' => array_map(fn($c) => $c->id, $listing->causes ?? []),
            ];
        }
        
        render('pages/post-form', [
            'pageTitle' => 'Edit Listing',
            'type' => $listing->type,
            'typeInfo' => $typeInfo,
            'causes' => $causes,
            'oldInput' => $oldInput,
            'errors' => $errors,
            'isEdit' => true,
            'listing' => $listing,
        ]);
    }
    
    /**
     * Update a listing
     */
    public function update(string $id): void
    {
        $listingId = (int) $id;
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Check permission
        $isAdmin = AuthService::isAdmin();
        if (!ListingService::canEdit($listingId, AuthService::id(), $isAdmin)) {
            flashError('You do not have permission to edit this listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Validate
        $validator = new ListingValidator();
        $validator->validateUpdate($_POST);
        
        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            redirectTo("/listing/{$listingId}/edit");
        }
        
        $data = $validator->validated();
        
        // Update the listing
        ListingService::update($listingId, $data);
        
        flashSuccess('Listing updated successfully.');
        redirectTo("/listing/{$listingId}");
    }
    
    /**
     * Update listing status
     */
    public function updateStatus(string $id): void
    {
        $listingId = (int) $id;
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Check permission
        $isAdmin = AuthService::isAdmin();
        if (!ListingService::canEdit($listingId, AuthService::id(), $isAdmin)) {
            flashError('You do not have permission to modify this listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        $newStatus = $_POST['status'] ?? '';
        
        // Validate status change
        $validator = new ListingValidator();
        $validator->validateStatusChange($newStatus, $listing->status);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            flashError($errors['status'] ?? 'Invalid status change.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Update status
        ListingService::updateStatus($listingId, $newStatus);
        
        $statusLabel = LISTING_STATUSES[$newStatus]['label'] ?? ucfirst($newStatus);
        flashSuccess("Listing marked as {$statusLabel}.");
        redirectTo("/listing/{$listingId}");
    }
    
    /**
     * Delete a listing
     */
    public function delete(string $id): void
    {
        $listingId = (int) $id;
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Check permission
        $isAdmin = AuthService::isAdmin();
        if (!ListingService::canEdit($listingId, AuthService::id(), $isAdmin)) {
            flashError('You do not have permission to delete this listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Soft delete (close the listing)
        ListingService::delete($listingId);
        
        flashSuccess('Listing has been removed.');
        redirectTo('/dashboard');
    }
}
