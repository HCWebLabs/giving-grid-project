<?php
/**
 * Organization Model
 * 
 * Represents a verified nonprofit or community organization.
 */

declare(strict_types=1);

namespace App\Models;

class Organization
{
    public int $id;
    public string $name;
    public ?string $mission;
    public string $county_served;
    public string $contact_email;
    public ?string $contact_phone;
    public ?string $website;
    public ?string $address;
    public bool $is_verified;
    public ?string $verified_at;
    public ?int $verified_by;
    public string $created_at;
    public string $updated_at;
    
    // Computed/joined fields
    public ?int $active_needs_count;
    public ?int $active_offers_count;
    public ?int $volunteer_count;
    
    /**
     * Create an Organization from a database row
     */
    public static function fromRow(array $row): self
    {
        $org = new self();
        
        $org->id = (int) $row['id'];
        $org->name = $row['name'];
        $org->mission = $row['mission'];
        $org->county_served = $row['county_served'];
        $org->contact_email = $row['contact_email'];
        $org->contact_phone = $row['contact_phone'] ?? null;
        $org->website = $row['website'] ?? null;
        $org->address = $row['address'] ?? null;
        $org->is_verified = (bool) $row['is_verified'];
        $org->verified_at = $row['verified_at'] ?? null;
        $org->verified_by = isset($row['verified_by']) ? (int) $row['verified_by'] : null;
        $org->created_at = $row['created_at'];
        $org->updated_at = $row['updated_at'];
        
        // Computed fields (may not always be present)
        $org->active_needs_count = isset($row['active_needs_count']) ? (int) $row['active_needs_count'] : null;
        $org->active_offers_count = isset($row['active_offers_count']) ? (int) $row['active_offers_count'] : null;
        $org->volunteer_count = isset($row['volunteer_count']) ? (int) $row['volunteer_count'] : null;
        
        return $org;
    }
    
    /**
     * Get the county display name
     */
    public function getCountyName(): string
    {
        return getCountyName($this->county_served);
    }
    
    /**
     * Get the verification badge HTML
     */
    public function getVerificationBadge(): string
    {
        if ($this->is_verified) {
            return '<span class="badge badge-verified" title="Verified Organization">✓ Verified</span>';
        }
        return '';
    }
    
    /**
     * Check if org has a website
     */
    public function hasWebsite(): bool
    {
        return !empty($this->website);
    }
    
    /**
     * Get formatted website URL (ensure https)
     */
    public function getWebsiteUrl(): ?string
    {
        if (empty($this->website)) {
            return null;
        }
        
        $url = $this->website;
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }
        
        return $url;
    }
    
    /**
     * Get display-friendly website (without protocol)
     */
    public function getWebsiteDisplay(): ?string
    {
        if (empty($this->website)) {
            return null;
        }
        
        return preg_replace('#^https?://#', '', $this->website);
    }
    
    /**
     * Get total active listings count
     */
    public function getTotalActiveListings(): int
    {
        return ($this->active_needs_count ?? 0) 
             + ($this->active_offers_count ?? 0) 
             + ($this->volunteer_count ?? 0);
    }
}
