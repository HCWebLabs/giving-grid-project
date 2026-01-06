<?php
/**
 * Authentication Middleware
 * 
 * Ensures user is logged in before accessing protected routes.
 */

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Handle the middleware check
     * 
     * @return bool True if user is authenticated
     */
    public static function handle(): bool
    {
        if (empty($_SESSION['user_id'])) {
            // Store intended destination
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            // Set flash message
            $_SESSION['flash_error'] = 'Please log in to continue.';
            
            // Redirect to login
            header('Location: ' . url('/login'));
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user is authenticated (without redirect)
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Get the redirect URL after login
     */
    public static function getRedirectUrl(): string
    {
        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        
        // Validate redirect is internal
        if (!str_starts_with($redirect, '/')) {
            $redirect = '/dashboard';
        }
        
        return $redirect;
    }
}
