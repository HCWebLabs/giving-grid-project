<?php
/**
 * Response Validator
 * 
 * Validation rules for responses and messages.
 */

declare(strict_types=1);

namespace App\Validation;

class ResponseValidator
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
     * Validate new response data
     */
    public function validateCreate(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Message
        $message = trim($input['message'] ?? '');
        if (empty($message)) {
            $this->errors['message'] = 'Please include a message with your response.';
        } elseif (strlen($message) < 10) {
            $this->errors['message'] = 'Message must be at least 10 characters.';
        } elseif (strlen($message) > 2000) {
            $this->errors['message'] = 'Message must be less than 2000 characters.';
        } else {
            $this->data['message'] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate a reply message
     */
    public function validateMessage(array $input): self
    {
        $this->errors = [];
        $this->data = [];
        
        // Content
        $content = trim($input['content'] ?? '');
        if (empty($content)) {
            $this->errors['content'] = 'Message cannot be empty.';
        } elseif (strlen($content) > 2000) {
            $this->errors['content'] = 'Message must be less than 2000 characters.';
        } else {
            $this->data['content'] = $content;
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
        
        $validStatuses = ['pending', 'accepted', 'declined', 'completed'];
        
        if (!in_array($newStatus, $validStatuses, true)) {
            $this->errors['status'] = 'Invalid status.';
            return $this;
        }
        
        // Define valid transitions
        $validTransitions = [
            'pending' => ['accepted', 'declined'],
            'accepted' => ['completed', 'declined'],
            'declined' => [], // Cannot change from declined
            'completed' => [], // Cannot change from completed
        ];
        
        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [], true)) {
            $this->errors['status'] = "Cannot change status from '{$currentStatus}' to '{$newStatus}'.";
            return $this;
        }
        
        $this->data['status'] = $newStatus;
        
        return $this;
    }
}
