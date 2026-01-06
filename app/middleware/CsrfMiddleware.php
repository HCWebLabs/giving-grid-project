<?php
/**
 * CSRF Middleware
 * 
 * Validates CSRF tokens on POST/PUT/DELETE requests.
 */

declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Handle the middleware check
     * 
     * @return bool True if CSRF token is valid
     */
    public static function handle(): bool
    {
        // Only check for state-changing methods
        $method = $_SERVER['REQUEST_METHOD'];
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }
        
        // Get token from request
        $token = $_POST['csrf_token'] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? null;
        
        // Validate token
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            self::fail();
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            self::fail();
        }
        
        return true;
    }
    
    /**
     * Handle CSRF validation failure
     */
    private static function fail(): never
    {
        http_response_code(403);
        $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
        
        // Redirect back or to home
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer && str_starts_with($referer, APP_URL)) {
            header('Location: ' . $referer);
        } else {
            header('Location: ' . url('/'));
        }
        
        exit;
    }
    
    /**
     * Regenerate the CSRF token
     */
    public static function regenerate(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}
