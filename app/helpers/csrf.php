<?php
/**
 * CSRF Protection Helper
 * 
 * Functions for generating and validating CSRF tokens.
 */

declare(strict_types=1);

/**
 * Get the current CSRF token
 * 
 * Generates one if it doesn't exist.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Generate a hidden CSRF input field
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Validate a CSRF token
 * 
 * @param string|null $token Token to validate
 * @return bool True if valid
 */
function csrfValidate(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token from current request
 */
function csrfFromRequest(): ?string
{
    // Check POST data first
    if (isset($_POST['csrf_token'])) {
        return $_POST['csrf_token'];
    }
    
    // Check header (for AJAX)
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    
    return null;
}

/**
 * Verify CSRF token from current request
 * 
 * @return bool True if valid
 */
function csrfVerify(): bool
{
    return csrfValidate(csrfFromRequest());
}
