<?php
/**
 * Listing Model
 * 
 * Represents a Need, Offer, or Volunteer opportunity in The Giving Grid.
 */

declare(strict_types=1);

namespace App\Models;

use App\Services\Database;

class Listing
{
    public int $id;
    public string $type;
    public string $title;
    public string $description;
    public string $category;
    public ?string $quantity;
    public string $county;
    public ?string $city;
    public string $urgency;
    public string $status;
    public string $logistics;
    public ?string $contact_method;
    public int $user_id;
    public ?int $org_id;
    public string $created_at;
    public string $updated_at;
    public ?string $fulfilled_at;
    
    // Joined fields
    public ?string $org_name;
    public ?bool $is_verified;
    public ?string $poster_name;
    public ?array $causes;
    
    /**
     * Create a Listing from a database row
     */
    public static function fromRow(array $row): self
    {
        $listing = new self();
        
        $listing->id = (int) $row['id'];
        $listing->type = $row['type'];
        $listing->title = $row['title'];
        $listing->description = $row['description'];
        $listing->category = $row['category'];
        $listing->quantity = $row['quantity'];
        $listing->county = $row['county'];
        $listing->city = $row['city'];
        $listing->urgency = $row['urgency'];
        $listing->status = $row['status'];
        $listing->logistics = $row['logistics'];
        $listing->contact_method = $row['contact_method'];
        $listing->user_id = (int) $row['user_id'];
        $listing->org_id = $row['org_id'] ? (int) $row['org_id'] : null;
        $listing->created_at = $row['created_at'];
        $listing->updated_at = $row['updated_at'];
        $listing->fulfilled_at = $row['fulfilled_at'];
        
        // Joined fields (may not always be present)
        $listing->org_name = $row['org_name'] ?? null;
        $listing->is_verified = isset($row['is_verified']) ? (bool) $row['is_verified'] : null;
        $listing->poster_name = $row['poster_name'] ?? null;
        $listing->causes = null; // Loaded separately if needed
        
        return $listing;
    }
    
    /**
     * Get the listing type info
     */
    public function getTypeInfo(): array
    {
        return getListingType($this->type) ?? [
            'label' => ucfirst($this->type),
            'icon' => '⬜',
            'color' => 'gray'
        ];
    }
    
    /**
     * Get the urgency info
     */
    public function getUrgencyInfo(): array
    {
        return getUrgencyLevel($this->urgency) ?? [
            'label' => ucfirst($this->urgency),
            'color' => 'gray'
        ];
    }
    
    /**
     * Get the category label
     */
    public function getCategoryLabel(): string
    {
        return getCategoryLabel($this->category);
    }
    
    /**
     * Get the county display name
     */
    public function getCountyName(): string
    {
        return getCountyName($this->county);
    }
    
    /**
     * Get the logistics label
     */
    public function getLogisticsLabel(): string
    {
        return getLogisticsLabel($this->logistics);
    }
    
    /**
     * Check if this listing is from a verified organization
     */
    public function isFromVerifiedOrg(): bool
    {
        return $this->org_id !== null && $this->is_verified === true;
    }
    
    /**
     * Get the poster display name
     */
    public function getPosterName(): string
    {
        if ($this->org_name) {
            return $this->org_name;
        }
        
        return $this->poster_name ?? 'Community Member';
    }
    
    /**
     * Check if listing is open for responses
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
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
