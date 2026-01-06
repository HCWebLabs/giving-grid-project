<?php
/**
 * Sanitize Helper
 * 
 * Functions for cleaning and validating user input.
 */

declare(strict_types=1);

/**
 * Sanitize a string for safe output
 * 
 * @param string|null $value Value to sanitize
 * @return string Sanitized value
 */
function sanitize(?string $value): string
{
    if ($value === null) {
        return '';
    }
    
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an email address
 * 
 * @param string|null $email Email to sanitize
 * @return string Sanitized and lowercased email
 */
function sanitizeEmail(?string $email): string
{
    if ($email === null) {
        return '';
    }
    
    return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
}

/**
 * Sanitize an integer
 * 
 * @param mixed $value Value to sanitize
 * @param int $default Default value if invalid
 * @return int Sanitized integer
 */
function sanitizeInt(mixed $value, int $default = 0): int
{
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    return $filtered !== false ? $filtered : $default;
}

/**
 * Sanitize a URL
 * 
 * @param string|null $url URL to sanitize
 * @return string|null Sanitized URL or null if invalid
 */
function sanitizeUrl(?string $url): ?string
{
    if ($url === null || $url === '') {
        return null;
    }
    
    $url = trim($url);
    
    // Add https if no protocol
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }
    
    $filtered = filter_var($url, FILTER_VALIDATE_URL);
    return $filtered !== false ? $filtered : null;
}

/**
 * Sanitize a phone number (US format)
 * 
 * @param string|null $phone Phone to sanitize
 * @return string|null Formatted phone or null
 */
function sanitizePhone(?string $phone): ?string
{
    if ($phone === null || $phone === '') {
        return null;
    }
    
    // Remove all non-digits
    $digits = preg_replace('/[^0-9]/', '', $phone);
    
    // Handle 10 or 11 digit numbers
    if (strlen($digits) === 10) {
        return sprintf('(%s) %s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 4)
        );
    }
    
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return sprintf('(%s) %s-%s',
            substr($digits, 1, 3),
            substr($digits, 4, 3),
            substr($digits, 7, 4)
        );
    }
    
    // Return original if not standard format
    return trim($phone);
}

/**
 * Get a POST value with optional sanitization
 * 
 * @param string $key POST key
 * @param mixed $default Default value
 * @param bool $sanitize Whether to sanitize the value
 * @return mixed
 */
function post(string $key, mixed $default = null, bool $sanitize = true): mixed
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    
    $value = $_POST[$key];
    
    if ($sanitize && is_string($value)) {
        return sanitize($value);
    }
    
    return $value;
}

/**
 * Get a GET value with optional sanitization
 * 
 * @param string $key GET key
 * @param mixed $default Default value
 * @param bool $sanitize Whether to sanitize the value
 * @return mixed
 */
function get(string $key, mixed $default = null, bool $sanitize = true): mixed
{
    if (!isset($_GET[$key])) {
        return $default;
    }
    
    $value = $_GET[$key];
    
    if ($sanitize && is_string($value)) {
        return sanitize($value);
    }
    
    return $value;
}

/**
 * Check if a value is a valid enum option
 * 
 * @param string $value Value to check
 * @param array $options Valid options
 * @return bool
 */
function isValidOption(string $value, array $options): bool
{
    return isset($options[$value]) || in_array($value, $options, true);
}
