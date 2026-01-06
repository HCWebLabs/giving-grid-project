<?php
/**
 * User Validator
 * 
 * Validation rules for user registration, login, and profile updates.
 */

declare(strict_types=1);

namespace App\Validation;

class UserValidator
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
     * Validate registration data
     */
    public function validateRegistration(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Email
        $email = strtolower(trim($input['email'] ?? ''));
        if (empty($email)) {
            $this->errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 255) {
            $this->errors['email'] = 'Email must be less than 255 characters.';
        } else {
            $this->data['email'] = $email;
        }
        
        // Display name
        $displayName = trim($input['display_name'] ?? '');
        if (empty($displayName)) {
            $this->errors['display_name'] = 'Display name is required.';
        } elseif (strlen($displayName) < 2) {
            $this->errors['display_name'] = 'Display name must be at least 2 characters.';
        } elseif (strlen($displayName) > 100) {
            $this->errors['display_name'] = 'Display name must be less than 100 characters.';
        } elseif (!preg_match('/^[\p{L}\p{N}\s\.\-\']+$/u', $displayName)) {
            $this->errors['display_name'] = 'Display name contains invalid characters.';
        } else {
            $this->data['display_name'] = $displayName;
        }
        
        // Password
        $password = $input['password'] ?? '';
        if (empty($password)) {
            $this->errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters.';
        } elseif (strlen($password) > 72) {
            // bcrypt has a 72 byte limit
            $this->errors['password'] = 'Password must be less than 72 characters.';
        } else {
            $this->data['password'] = $password;
        }
        
        // Password confirmation
        $passwordConfirm = $input['password_confirm'] ?? '';
        if ($password !== $passwordConfirm) {
            $this->errors['password_confirm'] = 'Passwords do not match.';
        }
        
        // County (optional)
        $county = $input['county'] ?? '';
        if (!empty($county)) {
            if (!isset(COUNTIES[$county])) {
                $this->errors['county'] = 'Please select a valid county.';
            } else {
                $this->data['county'] = $county;
            }
        } else {
            $this->data['county'] = null;
        }
        
        return $this;
    }
    
    /**
     * Validate login data
     */
    public function validateLogin(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Email
        $email = strtolower(trim($input['email'] ?? ''));
        if (empty($email)) {
            $this->errors['email'] = 'Email is required.';
        } else {
            $this->data['email'] = $email;
        }
        
        // Password
        $password = $input['password'] ?? '';
        if (empty($password)) {
            $this->errors['password'] = 'Password is required.';
        } else {
            $this->data['password'] = $password;
        }
        
        return $this;
    }
    
    /**
     * Validate profile update data
     */
    public function validateProfileUpdate(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Display name
        if (isset($input['display_name'])) {
            $displayName = trim($input['display_name']);
            if (empty($displayName)) {
                $this->errors['display_name'] = 'Display name is required.';
            } elseif (strlen($displayName) < 2) {
                $this->errors['display_name'] = 'Display name must be at least 2 characters.';
            } elseif (strlen($displayName) > 100) {
                $this->errors['display_name'] = 'Display name must be less than 100 characters.';
            } else {
                $this->data['display_name'] = $displayName;
            }
        }
        
        // County
        if (array_key_exists('county', $input)) {
            $county = $input['county'];
            if (!empty($county) && !isset(COUNTIES[$county])) {
                $this->errors['county'] = 'Please select a valid county.';
            } else {
                $this->data['county'] = $county ?: null;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate password change
     */
    public function validatePasswordChange(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Current password
        $currentPassword = $input['current_password'] ?? '';
        if (empty($currentPassword)) {
            $this->errors['current_password'] = 'Current password is required.';
        } else {
            $this->data['current_password'] = $currentPassword;
        }
        
        // New password
        $newPassword = $input['new_password'] ?? '';
        if (empty($newPassword)) {
            $this->errors['new_password'] = 'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $this->errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif (strlen($newPassword) > 72) {
            $this->errors['new_password'] = 'New password must be less than 72 characters.';
        } else {
            $this->data['new_password'] = $newPassword;
        }
        
        // Password confirmation
        $passwordConfirm = $input['new_password_confirm'] ?? '';
        if ($newPassword !== $passwordConfirm) {
            $this->errors['new_password_confirm'] = 'Passwords do not match.';
        }
        
        return $this;
    }
}
