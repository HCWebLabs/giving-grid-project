<?php
/**
 * Listing Validator
 * 
 * Validation rules for creating and editing listings.
 */

declare(strict_types=1);

namespace App\Validation;

class ListingValidator
{
    /**
     * Validation errors
     */
    private array $errors = [];
    
    /**
     * Validated data
     */
    private array $data = [];
    
    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->data;
    }
    
    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Validate listing creation data
     */
    public function validateCreate(array $input, array $userPermissions = []): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Type
        $type = $input['type'] ?? '';
        if (empty($type)) {
            $this->errors['type'] = 'Please select a listing type.';
        } elseif (!isset(LISTING_TYPES[$type])) {
            $this->errors['type'] = 'Invalid listing type.';
        } else {
            // Check permissions for type
            if ($type === 'need' && !($userPermissions['can_post_needs'] ?? false)) {
                $this->errors['type'] = 'Only verified organizations can post needs.';
            } elseif ($type === 'volunteer' && !($userPermissions['can_post_volunteer'] ?? false)) {
                $this->errors['type'] = 'Only verified organizations can post volunteer opportunities.';
            } else {
                $this->data['type'] = $type;
            }
        }
        
        // Title
        $title = trim($input['title'] ?? '');
        if (empty($title)) {
            $this->errors['title'] = 'Title is required.';
        } elseif (strlen($title) < 5) {
            $this->errors['title'] = 'Title must be at least 5 characters.';
        } elseif (strlen($title) > 255) {
            $this->errors['title'] = 'Title must be less than 255 characters.';
        } else {
            $this->data['title'] = $title;
        }
        
        // Description
        $description = trim($input['description'] ?? '');
        if (empty($description)) {
            $this->errors['description'] = 'Description is required.';
        } elseif (strlen($description) < 20) {
            $this->errors['description'] = 'Please provide more detail (at least 20 characters).';
        } elseif (strlen($description) > 5000) {
            $this->errors['description'] = 'Description must be less than 5000 characters.';
        } else {
            $this->data['description'] = $description;
        }
        
        // Category
        $category = $input['category'] ?? '';
        if (empty($category)) {
            $this->errors['category'] = 'Please select a category.';
        } elseif (!isset(CATEGORIES[$category])) {
            $this->errors['category'] = 'Invalid category.';
        } else {
            $this->data['category'] = $category;
        }
        
        // Quantity (optional)
        $quantity = trim($input['quantity'] ?? '');
        if (!empty($quantity) && strlen($quantity) > 100) {
            $this->errors['quantity'] = 'Quantity must be less than 100 characters.';
        } else {
            $this->data['quantity'] = $quantity ?: null;
        }
        
        // County
        $county = $input['county'] ?? '';
        if (empty($county)) {
            $this->errors['county'] = 'Please select a county.';
        } elseif (!isset(COUNTIES[$county])) {
            $this->errors['county'] = 'Invalid county.';
        } else {
            $this->data['county'] = $county;
        }
        
        // City (optional)
        $city = trim($input['city'] ?? '');
        if (!empty($city) && strlen($city) > 100) {
            $this->errors['city'] = 'City must be less than 100 characters.';
        } else {
            $this->data['city'] = $city ?: null;
        }
        
        // Urgency
        $urgency = $input['urgency'] ?? 'medium';
        if (!isset(URGENCY_LEVELS[$urgency])) {
            $this->errors['urgency'] = 'Invalid urgency level.';
        } else {
            $this->data['urgency'] = $urgency;
        }
        
        // Logistics
        $logistics = $input['logistics'] ?? 'na';
        if (!isset(LOGISTICS_OPTIONS[$logistics])) {
            $this->errors['logistics'] = 'Invalid logistics option.';
        } else {
            $this->data['logistics'] = $logistics;
        }
        
        // Contact method (optional)
        $contactMethod = trim($input['contact_method'] ?? '');
        if (!empty($contactMethod) && strlen($contactMethod) > 255) {
            $this->errors['contact_method'] = 'Contact method must be less than 255 characters.';
        } else {
            $this->data['contact_method'] = $contactMethod ?: null;
        }
        
        // Causes (optional, max 2)
        $causes = $input['causes'] ?? [];
        if (!is_array($causes)) {
            $causes = [];
        }
        $causes = array_slice(array_filter($causes), 0, 2); // Max 2 causes
        $this->data['causes'] = array_map('intval', $causes);
        
        return $this;
    }
    
    /**
     * Validate listing update data
     */
    public function validateUpdate(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Title
        if (isset($input['title'])) {
            $title = trim($input['title']);
            if (empty($title)) {
                $this->errors['title'] = 'Title is required.';
            } elseif (strlen($title) < 5) {
                $this->errors['title'] = 'Title must be at least 5 characters.';
            } elseif (strlen($title) > 255) {
                $this->errors['title'] = 'Title must be less than 255 characters.';
            } else {
                $this->data['title'] = $title;
            }
        }
        
        // Description
        if (isset($input['description'])) {
            $description = trim($input['description']);
            if (empty($description)) {
                $this->errors['description'] = 'Description is required.';
            } elseif (strlen($description) < 20) {
                $this->errors['description'] = 'Please provide more detail (at least 20 characters).';
            } elseif (strlen($description) > 5000) {
                $this->errors['description'] = 'Description must be less than 5000 characters.';
            } else {
                $this->data['description'] = $description;
            }
        }
        
        // Category
        if (isset($input['category'])) {
            $category = $input['category'];
            if (empty($category)) {
                $this->errors['category'] = 'Please select a category.';
            } elseif (!isset(CATEGORIES[$category])) {
                $this->errors['category'] = 'Invalid category.';
            } else {
                $this->data['category'] = $category;
            }
        }
        
        // Quantity
        if (array_key_exists('quantity', $input)) {
            $quantity = trim($input['quantity'] ?? '');
            if (strlen($quantity) > 100) {
                $this->errors['quantity'] = 'Quantity must be less than 100 characters.';
            } else {
                $this->data['quantity'] = $quantity ?: null;
            }
        }
        
        // County
        if (isset($input['county'])) {
            $county = $input['county'];
            if (empty($county)) {
                $this->errors['county'] = 'Please select a county.';
            } elseif (!isset(COUNTIES[$county])) {
                $this->errors['county'] = 'Invalid county.';
            } else {
                $this->data['county'] = $county;
            }
        }
        
        // City
        if (array_key_exists('city', $input)) {
            $city = trim($input['city'] ?? '');
            if (strlen($city) > 100) {
                $this->errors['city'] = 'City must be less than 100 characters.';
            } else {
                $this->data['city'] = $city ?: null;
            }
        }
        
        // Urgency
        if (isset($input['urgency'])) {
            $urgency = $input['urgency'];
            if (!isset(URGENCY_LEVELS[$urgency])) {
                $this->errors['urgency'] = 'Invalid urgency level.';
            } else {
                $this->data['urgency'] = $urgency;
            }
        }
        
        // Logistics
        if (isset($input['logistics'])) {
            $logistics = $input['logistics'];
            if (!isset(LOGISTICS_OPTIONS[$logistics])) {
                $this->errors['logistics'] = 'Invalid logistics option.';
            } else {
                $this->data['logistics'] = $logistics;
            }
        }
        
        // Contact method
        if (array_key_exists('contact_method', $input)) {
            $contactMethod = trim($input['contact_method'] ?? '');
            if (strlen($contactMethod) > 255) {
                $this->errors['contact_method'] = 'Contact method must be less than 255 characters.';
            } else {
                $this->data['contact_method'] = $contactMethod ?: null;
            }
        }
        
        // Causes
        if (isset($input['causes'])) {
            $causes = $input['causes'];
            if (!is_array($causes)) {
                $causes = [];
            }
            $causes = array_slice(array_filter($causes), 0, 2);
            $this->data['causes'] = array_map('intval', $causes);
        }
        
        return $this;
    }
    
    /**
     * Validate status change
     */
    public function validateStatusChange(string $newStatus, string $currentStatus): self
    {
        $this->errors = [];
        $this->data = [];
        
        $validStatuses = array_keys(LISTING_STATUSES);
        
        if (!in_array($newStatus, $validStatuses, true)) {
            $this->errors['status'] = 'Invalid status.';
            return $this;
        }
        
        // Define valid transitions
        $validTransitions = [
            'open' => ['in_progress', 'fulfilled', 'closed'],
            'in_progress' => ['open', 'fulfilled', 'closed'],
            'fulfilled' => ['closed'],
            'closed' => ['open'], // Allow reopening
        ];
        
        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [], true)) {
            $this->errors['status'] = "Cannot change status from '{$currentStatus}' to '{$newStatus}'.";
            return $this;
        }
        
        $this->data['status'] = $newStatus;
        
        return $this;
    }
}
