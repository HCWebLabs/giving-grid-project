<?php
/**
 * Message Model
 * 
 * Represents a message within a response thread.
 */

declare(strict_types=1);

namespace App\Models;

class Message
{
    public int $id;
    public int $response_id;
    public int $sender_id;
    public string $content;
    public bool $is_read;
    public string $created_at;
    
    // Joined fields
    public ?string $sender_name;
    public ?bool $is_own_message;
    
    /**
     * Create a Message from a database row
     */
    public static function fromRow(array $row, ?int $currentUserId = null): self
    {
        $message = new self();
        
        $message->id = (int) $row['id'];
        $message->response_id = (int) $row['response_id'];
        $message->sender_id = (int) $row['sender_id'];
        $message->content = $row['content'];
        $message->is_read = (bool) $row['is_read'];
        $message->created_at = $row['created_at'];
        
        // Joined fields
        $message->sender_name = $row['sender_name'] ?? null;
        $message->is_own_message = $currentUserId !== null && $message->sender_id === $currentUserId;
        
        return $message;
    }
    
    /**
     * Get formatted timestamp
     */
    public function getFormattedTime(): string
    {
        $timestamp = strtotime($this->created_at);
        $now = time();
        $diff = $now - $timestamp;
        
        // Today - show time only
        if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
            return date('g:i A', $timestamp);
        }
        
        // This week - show day and time
        if ($diff < 604800) {
            return date('D g:i A', $timestamp);
        }
        
        // Older - show full date
        return date('M j, Y g:i A', $timestamp);
    }
}
