<?php
/**
 * Role Middleware
 * 
 * Ensures user has the required role before accessing protected routes.
 */

declare(strict_types=1);

namespace App\Middleware;

class RoleMiddleware
{
    /**
     * Check if user has admin role
     */
    public static function requireAdmin(): bool
    {
        if (!AuthMiddleware::check()) {
            return AuthMiddleware::handle();
        }
        
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            $_SESSION['flash_error'] = 'You do not have permission to access this page.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user is an organization member
     */
    public static function requireOrgMember(): bool
    {
        if (!AuthMiddleware::check()) {
            return AuthMiddleware::handle();
        }
        
        if (($_SESSION['user']['role'] ?? '') !== 'org_member') {
            http_response_code(403);
            $_SESSION['flash_error'] = 'This action requires an organization account.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user has a verified organization
     */
    public static function requireVerifiedOrg(): bool
    {
        if (!self::requireOrgMember()) {
            return false;
        }
        
        if (!($_SESSION['user']['org_verified'] ?? false)) {
            $_SESSION['flash_warning'] = 'Your organization must be verified to perform this action.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user has one of the allowed roles
     */
    public static function requireRole(array $allowedRoles): bool
    {
        if (!AuthMiddleware::check()) {
            return AuthMiddleware::handle();
        }
        
        $userRole = $_SESSION['user']['role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'You do not have permission to access this page.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
        
        return true;
    }
}
