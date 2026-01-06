<?php
/**
 * Response Model
 * 
 * Represents a response to a listing (someone offering to help or interested).
 */

declare(strict_types=1);

namespace App\Models;

class Response
{
    public int $id;
    public int $listing_id;
    public int $responder_id;
    public string $message;
    public string $status;
    public string $created_at;
    public string $updated_at;
    
    // Joined fields
    public ?string $responder_name;
    public ?string $responder_email;
    public ?string $listing_title;
    public ?string $listing_type;
    public ?int $listing_user_id;
    public ?string $listing_poster_name;
    public ?int $message_count;
    public ?string $last_message_at;
    
    /**
     * Create a Response from a database row
     */
    public static function fromRow(array $row): self
    {
        $response = new self();
        
        $response->id = (int) $row['id'];
        $response->listing_id = (int) $row['listing_id'];
        $response->responder_id = (int) $row['responder_id'];
        $response->message = $row['message'];
        $response->status = $row['status'];
        $response->created_at = $row['created_at'];
        $response->updated_at = $row['updated_at'];
        
        // Joined fields
        $response->responder_name = $row['responder_name'] ?? null;
        $response->responder_email = $row['responder_email'] ?? null;
        $response->listing_title = $row['listing_title'] ?? null;
        $response->listing_type = $row['listing_type'] ?? null;
        $response->listing_user_id = isset($row['listing_user_id']) ? (int) $row['listing_user_id'] : null;
        $response->listing_poster_name = $row['listing_poster_name'] ?? null;
        $response->message_count = isset($row['message_count']) ? (int) $row['message_count'] : null;
        $response->last_message_at = $row['last_message_at'] ?? null;
        
        return $response;
    }
    
    /**
     * Get status display info
     */
    public function getStatusInfo(): array
    {
        $statuses = [
            'pending' => [
                'label' => 'Pending',
                'color' => 'yellow',
                'description' => 'Waiting for response'
            ],
            'accepted' => [
                'label' => 'Accepted',
                'color' => 'green',
                'description' => 'Response accepted'
            ],
            'declined' => [
                'label' => 'Declined',
                'color' => 'red',
                'description' => 'Response declined'
            ],
            'completed' => [
                'label' => 'Completed',
                'color' => 'blue',
                'description' => 'Exchange completed'
            ],
        ];
        
        return $statuses[$this->status] ?? [
            'label' => ucfirst($this->status),
            'color' => 'gray',
            'description' => ''
        ];
    }
    
    /**
     * Check if response is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if response is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
    
    /**
     * Check if response is active (can message)
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }
    
    /**
     * Get relative time since creation
     */
    public function getTimeAgo(): string
    {
        $timestamp = strtotime($this->created_at);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}
