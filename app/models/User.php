<?php
/**
 * User Model
 * 
 * Represents a user in The Giving Grid.
 */

declare(strict_types=1);

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $password_hash;
    public string $display_name;
    public ?string $county;
    public string $role;
    public ?int $org_id;
    public bool $is_active;
    public string $created_at;
    public string $updated_at;
    
    // Joined fields
    public ?string $org_name;
    public ?bool $org_verified;
    
    /**
     * Create a User from a database row
     */
    public static function fromRow(array $row): self
    {
        $user = new self();
        
        $user->id = (int) $row['id'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'] ?? '';
        $user->display_name = $row['display_name'];
        $user->county = $row['county'] ?? null;
        $user->role = $row['role'];
        $user->org_id = isset($row['org_id']) ? (int) $row['org_id'] : null;
        $user->is_active = (bool) ($row['is_active'] ?? true);
        $user->created_at = $row['created_at'] ?? '';
        $user->updated_at = $row['updated_at'] ?? '';
        
        // Joined fields
        $user->org_name = $row['org_name'] ?? null;
        $user->org_verified = isset($row['org_verified']) ? (bool) $row['org_verified'] : null;
        
        return $user;
    }
    
    /**
     * Convert to array for session storage (excludes sensitive data)
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'display_name' => $this->display_name,
            'county' => $this->county,
            'role' => $this->role,
            'org_id' => $this->org_id,
            'org_name' => $this->org_name,
            'org_verified' => $this->org_verified,
        ];
    }
    
    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is an organization member
     */
    public function isOrgMember(): bool
    {
        return $this->role === 'org_member' && $this->org_id !== null;
    }
    
    /**
     * Check if user is an individual
     */
    public function isIndividual(): bool
    {
        return $this->role === 'individual';
    }
    
    /**
     * Check if user belongs to a verified organization
     */
    public function hasVerifiedOrg(): bool
    {
        return $this->isOrgMember() && $this->org_verified === true;
    }
    
    /**
     * Get the user's role display label
     */
    public function getRoleLabel(): string
    {
        return USER_ROLES[$this->role]['label'] ?? ucfirst($this->role);
    }
    
    /**
     * Get county display name
     */
    public function getCountyName(): ?string
    {
        if (!$this->county) {
            return null;
        }
        return getCountyName($this->county);
    }
    
    /**
     * Check if user can post needs (must be verified org member)
     */
    public function canPostNeeds(): bool
    {
        return $this->hasVerifiedOrg();
    }
    
    /**
     * Check if user can post offers (anyone logged in)
     */
    public function canPostOffers(): bool
    {
        return $this->is_active;
    }
    
    /**
     * Check if user can post volunteer opportunities (verified org only)
     */
    public function canPostVolunteer(): bool
    {
        return $this->hasVerifiedOrg();
    }
}
