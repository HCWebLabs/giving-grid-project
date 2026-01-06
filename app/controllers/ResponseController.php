<?php
/**
 * Response Controller
 * 
 * Handles responses to listings and messaging between users.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/ResponseService.php';
require_once APP_PATH . '/Services/ListingService.php';
require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Validation/ResponseValidator.php';
require_once APP_PATH . '/Models/Response.php';
require_once APP_PATH . '/Models/Message.php';

use App\Services\ResponseService;
use App\Services\ListingService;
use App\Services\AuthService;
use App\Validation\ResponseValidator;

class ResponseController
{
    /**
     * Show the respond form for a listing
     */
    public function create(string $listingId): void
    {
        $listingId = (int) $listingId;
        $userId = AuthService::id();
        
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Cannot respond to your own listing
        if ($listing->user_id === $userId) {
            flashError('You cannot respond to your own listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Check if listing is open
        if (!$listing->isOpen()) {
            flashError('This listing is no longer accepting responses.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Check if already responded
        $existingResponse = ResponseService::getExistingResponse($listingId, $userId);
        if ($existingResponse) {
            flashInfo('You have already responded to this listing.');
            redirectTo("/responses/{$existingResponse->id}");
        }
        
        // Get errors and old input
        $errors = $_SESSION['errors'] ?? [];
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        
        render('pages/respond-form', [
            'pageTitle' => 'Respond to Listing',
            'listing' => $listing,
            'errors' => $errors,
            'oldInput' => $oldInput,
        ]);
    }
    
    /**
     * Store a new response
     */
    public function store(string $listingId): void
    {
        $listingId = (int) $listingId;
        $userId = AuthService::id();
        
        $listing = ListingService::getById($listingId);
        
        if (!$listing) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Listing Not Found']);
            return;
        }
        
        // Cannot respond to your own listing
        if ($listing->user_id === $userId) {
            flashError('You cannot respond to your own listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Check if listing is open
        if (!$listing->isOpen()) {
            flashError('This listing is no longer accepting responses.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Check if already responded
        if (ResponseService::hasResponded($listingId, $userId)) {
            flashError('You have already responded to this listing.');
            redirectTo("/listing/{$listingId}");
        }
        
        // Validate
        $validator = new ResponseValidator();
        $validator->validateCreate($_POST);
        
        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            redirectTo("/listing/{$listingId}/respond");
        }
        
        $data = $validator->validated();
        
        // Create the response
        $responseId = ResponseService::create($listingId, $userId, $data['message']);
        
        flashSuccess('Your response has been sent! You\'ll be notified when the poster replies.');
        redirectTo("/responses/{$responseId}");
    }
    
    /**
     * View a response thread
     */
    public function show(string $id): void
    {
        $responseId = (int) $id;
        $userId = AuthService::id();
        
        $response = ResponseService::getById($responseId);
        
        if (!$response) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Response Not Found']);
            return;
        }
        
        // Check permission
        if (!ResponseService::canView($responseId, $userId)) {
            http_response_code(403);
            render('pages/errors/403', ['pageTitle' => 'Access Denied']);
            return;
        }
        
        // Mark messages as read
        ResponseService::markMessagesRead($responseId, $userId);
        
        // Get messages
        $messages = ResponseService::getMessages($responseId, $userId);
        
        // Get the listing
        $listing = ListingService::getById($response->listing_id);
        
        // Determine user's role in this conversation
        $isListingOwner = $response->listing_user_id === $userId;
        $isResponder = $response->responder_id === $userId;
        
        render('pages/response-thread', [
            'pageTitle' => 'Conversation',
            'response' => $response,
            'messages' => $messages,
            'listing' => $listing,
            'isListingOwner' => $isListingOwner,
            'isResponder' => $isResponder,
        ]);
    }
    
    /**
     * Send a message in a response thread
     */
    public function sendMessage(string $id): void
    {
        $responseId = (int) $id;
        $userId = AuthService::id();
        
        $response = ResponseService::getById($responseId);
        
        if (!$response) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Response Not Found']);
            return;
        }
        
        // Check permission
        if (!ResponseService::canView($responseId, $userId)) {
            http_response_code(403);
            flashError('You do not have permission to send messages in this conversation.');
            redirectTo('/dashboard');
            return;
        }
        
        // Check if response is still active
        if (!$response->isActive()) {
            flashError('This conversation is no longer active.');
            redirectTo("/responses/{$responseId}");
        }
        
        // Validate
        $validator = new ResponseValidator();
        $validator->validateMessage($_POST);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            flashError($errors['content'] ?? 'Invalid message.');
            redirectTo("/responses/{$responseId}");
        }
        
        $data = $validator->validated();
        
        // Add message
        ResponseService::addMessage($responseId, $userId, $data['content']);
        
        redirectTo("/responses/{$responseId}");
    }
    
    /**
     * Update response status (accept, decline, complete)
     */
    public function updateStatus(string $id): void
    {
        $responseId = (int) $id;
        $userId = AuthService::id();
        
        $response = ResponseService::getById($responseId);
        
        if (!$response) {
            http_response_code(404);
            render('pages/errors/404', ['pageTitle' => 'Response Not Found']);
            return;
        }
        
        // Only listing owner can change status
        if (!ResponseService::isListingOwner($responseId, $userId)) {
            flashError('Only the listing owner can change response status.');
            redirectTo("/responses/{$responseId}");
        }
        
        $newStatus = $_POST['status'] ?? '';
        
        // Validate
        $validator = new ResponseValidator();
        $validator->validateStatusChange($newStatus, $response->status);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            flashError($errors['status'] ?? 'Invalid status change.');
            redirectTo("/responses/{$responseId}");
        }
        
        // Update status
        ResponseService::updateStatus($responseId, $newStatus);
        
        $statusLabels = [
            'accepted' => 'accepted',
            'declined' => 'declined',
            'completed' => 'marked as completed',
        ];
        
        flashSuccess('Response ' . ($statusLabels[$newStatus] ?? 'updated') . '.');
        redirectTo("/responses/{$responseId}");
    }
    
    /**
     * List user's responses (inbox/outbox)
     */
    public function index(): void
    {
        $userId = AuthService::id();
        $view = $_GET['view'] ?? 'received';
        
        if ($view === 'sent') {
            // Responses user has sent
            $responses = ResponseService::getByResponderId($userId);
            $pageTitle = 'My Responses';
        } else {
            // Responses received on user's listings
            $responses = ResponseService::getReceivedByUserId($userId);
            $pageTitle = 'Received Responses';
        }
        
        // Get counts
        $pendingCount = ResponseService::getPendingResponseCount($userId);
        $unreadCount = ResponseService::getUnreadCount($userId);
        
        render('pages/responses-list', [
            'pageTitle' => $pageTitle,
            'responses' => $responses,
            'currentView' => $view,
            'pendingCount' => $pendingCount,
            'unreadCount' => $unreadCount,
        ]);
    }
}
