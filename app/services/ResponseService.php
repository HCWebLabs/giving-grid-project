<?php
/**
 * Response Service
 * 
 * Business logic for responses and messaging between users.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Response;
use App\Models\Message;

class ResponseService
{
    /**
     * Create a new response to a listing
     * 
     * @param int $listingId The listing being responded to
     * @param int $responderId The user responding
     * @param string $message Initial message
     * @return int New response ID
     */
    public static function create(int $listingId, int $responderId, string $message): int
    {
        // Create the response
        $sql = "
            INSERT INTO responses (listing_id, responder_id, status, created_at, updated_at)
            VALUES (:listing_id, :responder_id, 'pending', NOW(), NOW())
        ";
        
        $responseId = Database::insert($sql, [
            ':listing_id' => $listingId,
            ':responder_id' => $responderId,
        ]);
        
        // Add the initial message
        self::addMessage($responseId, $responderId, $message);
        
        return $responseId;
    }
    
    /**
     * Get a response by ID with full details
     */
    public static function getById(int $id): ?Response
    {
        $sql = "
            SELECT 
                r.*,
                u.display_name AS responder_name,
                u.email AS responder_email,
                l.title AS listing_title,
                l.type AS listing_type,
                l.user_id AS listing_user_id,
                poster.display_name AS listing_poster_name,
                (SELECT COUNT(*) FROM response_messages rm WHERE rm.response_id = r.id) AS message_count,
                (SELECT MAX(created_at) FROM response_messages rm WHERE rm.response_id = r.id) AS last_message_at
            FROM responses r
            INNER JOIN users u ON r.responder_id = u.id
            INNER JOIN listings l ON r.listing_id = l.id
            INNER JOIN users poster ON l.user_id = poster.id
            WHERE r.id = :id
        ";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        return Response::fromRow($row);
    }
    
    /**
     * Get responses for a listing (for listing owner)
     */
    public static function getForListing(int $listingId): array
    {
        $sql = "
            SELECT 
                r.*,
                u.display_name AS responder_name,
                u.email AS responder_email,
                (SELECT COUNT(*) FROM response_messages rm WHERE rm.response_id = r.id) AS message_count,
                (SELECT MAX(created_at) FROM response_messages rm WHERE rm.response_id = r.id) AS last_message_at
            FROM responses r
            INNER JOIN users u ON r.responder_id = u.id
            WHERE r.listing_id = :listing_id
            ORDER BY 
                FIELD(r.status, 'pending', 'accepted', 'completed', 'declined'),
                r.created_at DESC
        ";
        
        $rows = Database::fetchAll($sql, [':listing_id' => $listingId]);
        
        return array_map(fn($row) => Response::fromRow($row), $rows);
    }
    
    /**
     * Get responses made by a user (their outgoing responses)
     */
    public static function getByResponderId(int $userId): array
    {
        $sql = "
            SELECT 
                r.*,
                l.title AS listing_title,
                l.type AS listing_type,
                l.user_id AS listing_user_id,
                poster.display_name AS listing_poster_name,
                (SELECT COUNT(*) FROM response_messages rm WHERE rm.response_id = r.id) AS message_count,
                (SELECT MAX(created_at) FROM response_messages rm WHERE rm.response_id = r.id) AS last_message_at
            FROM responses r
            INNER JOIN listings l ON r.listing_id = l.id
            INNER JOIN users poster ON l.user_id = poster.id
            WHERE r.responder_id = :user_id
            ORDER BY r.updated_at DESC
        ";
        
        $rows = Database::fetchAll($sql, [':user_id' => $userId]);
        
        return array_map(fn($row) => Response::fromRow($row), $rows);
    }
    
    /**
     * Get responses received on user's listings (incoming responses)
     */
    public static function getReceivedByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                r.*,
                u.display_name AS responder_name,
                u.email AS responder_email,
                l.title AS listing_title,
                l.type AS listing_type,
                l.user_id AS listing_user_id,
                (SELECT COUNT(*) FROM response_messages rm WHERE rm.response_id = r.id) AS message_count,
                (SELECT COUNT(*) FROM response_messages rm WHERE rm.response_id = r.id AND rm.is_read = 0 AND rm.sender_id != :user_id) AS unread_count,
                (SELECT MAX(created_at) FROM response_messages rm WHERE rm.response_id = r.id) AS last_message_at
            FROM responses r
            INNER JOIN users u ON r.responder_id = u.id
            INNER JOIN listings l ON r.listing_id = l.id
            WHERE l.user_id = :user_id
            ORDER BY 
                FIELD(r.status, 'pending', 'accepted', 'completed', 'declined'),
                r.updated_at DESC
        ";
        
        $rows = Database::fetchAll($sql, [':user_id' => $userId]);
        
        return array_map(fn($row) => Response::fromRow($row), $rows);
    }
    
    /**
     * Check if user has already responded to a listing
     */
    public static function hasResponded(int $listingId, int $userId): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM responses WHERE listing_id = :listing_id AND responder_id = :user_id",
            [':listing_id' => $listingId, ':user_id' => $userId]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Get user's existing response to a listing
     */
    public static function getExistingResponse(int $listingId, int $userId): ?Response
    {
        $sql = "
            SELECT r.*
            FROM responses r
            WHERE r.listing_id = :listing_id AND r.responder_id = :user_id
        ";
        
        $row = Database::fetch($sql, [':listing_id' => $listingId, ':user_id' => $userId]);
        
        if (!$row) {
            return null;
        }
        
        return Response::fromRow($row);
    }
    
    /**
     * Update response status
     */
    public static function updateStatus(int $responseId, string $status): bool
    {
        $validStatuses = ['pending', 'accepted', 'declined', 'completed'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return Database::execute(
            "UPDATE responses SET status = :status, updated_at = NOW() WHERE id = :id",
            [':status' => $status, ':id' => $responseId]
        ) > 0;
    }
    
    /**
     * Check if user can view a response
     */
    public static function canView(int $responseId, int $userId): bool
    {
        // User can view if they are the responder or the listing owner
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM responses r
             INNER JOIN listings l ON r.listing_id = l.id
             WHERE r.id = :response_id 
             AND (r.responder_id = :user_id OR l.user_id = :user_id)",
            [':response_id' => $responseId, ':user_id' => $userId]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Check if user is the listing owner for a response
     */
    public static function isListingOwner(int $responseId, int $userId): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM responses r
             INNER JOIN listings l ON r.listing_id = l.id
             WHERE r.id = :response_id AND l.user_id = :user_id",
            [':response_id' => $responseId, ':user_id' => $userId]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Add a message to a response thread
     */
    public static function addMessage(int $responseId, int $senderId, string $content): int
    {
        $sql = "
            INSERT INTO response_messages (response_id, sender_id, content, is_read, created_at)
            VALUES (:response_id, :sender_id, :content, 0, NOW())
        ";
        
        $messageId = Database::insert($sql, [
            ':response_id' => $responseId,
            ':sender_id' => $senderId,
            ':content' => $content,
        ]);
        
        // Update response updated_at
        Database::execute(
            "UPDATE responses SET updated_at = NOW() WHERE id = :id",
            [':id' => $responseId]
        );
        
        return $messageId;
    }
    
    /**
     * Get messages for a response thread
     */
    public static function getMessages(int $responseId, ?int $currentUserId = null): array
    {
        $sql = "
            SELECT 
                rm.*,
                u.display_name AS sender_name
            FROM response_messages rm
            INNER JOIN users u ON rm.sender_id = u.id
            WHERE rm.response_id = :response_id
            ORDER BY rm.created_at ASC
        ";
        
        $rows = Database::fetchAll($sql, [':response_id' => $responseId]);
        
        return array_map(fn($row) => Message::fromRow($row, $currentUserId), $rows);
    }
    
    /**
     * Mark messages as read for a user in a thread
     */
    public static function markMessagesRead(int $responseId, int $userId): void
    {
        // Mark all messages NOT sent by this user as read
        Database::execute(
            "UPDATE response_messages 
             SET is_read = 1 
             WHERE response_id = :response_id AND sender_id != :user_id",
            [':response_id' => $responseId, ':user_id' => $userId]
        );
    }
    
    /**
     * Get unread message count for a user
     */
    public static function getUnreadCount(int $userId): int
    {
        // Count unread messages in threads where user is participant
        $sql = "
            SELECT COUNT(DISTINCT rm.id)
            FROM response_messages rm
            INNER JOIN responses r ON rm.response_id = r.id
            INNER JOIN listings l ON r.listing_id = l.id
            WHERE rm.is_read = 0 
            AND rm.sender_id != :user_id
            AND (r.responder_id = :user_id OR l.user_id = :user_id)
        ";
        
        return (int) Database::fetchColumn($sql, [':user_id' => $userId]);
    }
    
    /**
     * Get count of pending responses for user's listings
     */
    public static function getPendingResponseCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM responses r
            INNER JOIN listings l ON r.listing_id = l.id
            WHERE l.user_id = :user_id AND r.status = 'pending'
        ";
        
        return (int) Database::fetchColumn($sql, [':user_id' => $userId]);
    }
    
    /**
     * Get response counts grouped by status for a listing
     */
    public static function getCountsForListing(int $listingId): array
    {
        $sql = "
            SELECT status, COUNT(*) as count
            FROM responses
            WHERE listing_id = :listing_id
            GROUP BY status
        ";
        
        $rows = Database::fetchAll($sql, [':listing_id' => $listingId]);
        
        $counts = [
            'pending' => 0,
            'accepted' => 0,
            'declined' => 0,
            'completed' => 0,
            'total' => 0,
        ];
        
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }
        
        return $counts;
    }
}
