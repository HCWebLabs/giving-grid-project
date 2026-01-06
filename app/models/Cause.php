<?php
/**
 * Cause Model
 * 
 * Represents a cause tag for categorizing and discovering listings.
 */

declare(strict_types=1);

namespace App\Models;

class Cause
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public bool $is_active;
    
    // Computed fields
    public ?int $listing_count;
    
    /**
     * Create a Cause from a database row
     */
    public static function fromRow(array $row): self
    {
        $cause = new self();
        
        $cause->id = (int) $row['id'];
        $cause->name = $row['name'];
        $cause->slug = $row['slug'];
        $cause->description = $row['description'] ?? null;
        $cause->is_active = (bool) ($row['is_active'] ?? true);
        
        // Computed fields
        $cause->listing_count = isset($row['listing_count']) ? (int) $row['listing_count'] : null;
        
        return $cause;
    }
    
    /**
     * Get URL for browsing this cause
     */
    public function getBrowseUrl(): string
    {
        return browseUrl(['cause' => $this->slug]);
    }
}
