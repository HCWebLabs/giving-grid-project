<?php
/**
 * Auth Controller
 * 
 * Handles user authentication: login, registration, logout.
 */

declare(strict_types=1);

namespace App\Controllers;

require_once APP_PATH . '/Services/AuthService.php';
require_once APP_PATH . '/Validation/UserValidator.php';
require_once APP_PATH . '/Models/User.php';

use App\Services\AuthService;
use App\Validation\UserValidator;

class AuthController
{
    /**
     * Display login form
     */
    public function loginForm(): void
    {
        // Redirect if already logged in
        if (AuthService::check()) {
            redirectTo('/dashboard');
        }
        
        render('pages/login', [
            'pageTitle' => 'Log In',
        ], 'auth');
    }
    
    /**
     * Process login
     */
    public function login(): void
    {
        // Redirect if already logged in
        if (AuthService::check()) {
            redirectTo('/dashboard');
        }
        
        // Validate input
        $validator = new UserValidator();
        $validator->validateLogin($_POST);
        
        if ($validator->fails()) {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
            $_SESSION['old_input'] = ['email' => $_POST['email'] ?? ''];
            redirectTo('/login');
        }
        
        $data = $validator->validated();
        
        // Attempt login
        $user = AuthService::attempt($data['email'], $data['password']);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Invalid email or password.';
            $_SESSION['old_input'] = ['email' => $data['email']];
            redirectTo('/login');
        }
        
        // Success - redirect to intended page or dashboard
        $_SESSION['flash_success'] = 'Welcome back, ' . e($user->display_name) . '!';
        
        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        
        // Ensure redirect is internal
        if (!str_starts_with($redirect, '/')) {
            $redirect = '/dashboard';
        }
        
        redirectTo($redirect);
    }
    
    /**
     * Display registration form
     */
    public function registerForm(): void
    {
        // Redirect if already logged in
        if (AuthService::check()) {
            redirectTo('/dashboard');
        }
        
        render('pages/register', [
            'pageTitle' => 'Create Account',
        ], 'auth');
    }
    
    /**
     * Process registration
     */
    public function register(): void
    {
        // Redirect if already logged in
        if (AuthService::check()) {
            redirectTo('/dashboard');
        }
        
        // Validate input
        $validator = new UserValidator();
        $validator->validateRegistration($_POST);
        
        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old_input'] = [
                'email' => $_POST['email'] ?? '',
                'display_name' => $_POST['display_name'] ?? '',
                'county' => $_POST['county'] ?? '',
            ];
            redirectTo('/register');
        }
        
        $data = $validator->validated();
        
        // Check if email already exists
        if (AuthService::emailExists($data['email'])) {
            $_SESSION['errors'] = ['email' => 'An account with this email already exists.'];
            $_SESSION['old_input'] = [
                'email' => $data['email'],
                'display_name' => $data['display_name'],
                'county' => $data['county'] ?? '',
            ];
            redirectTo('/register');
        }
        
        // Register user
        $result = AuthService::register($data);
        
        if (is_array($result)) {
            // Errors returned
            $_SESSION['errors'] = $result;
            $_SESSION['old_input'] = [
                'email' => $data['email'],
                'display_name' => $data['display_name'],
                'county' => $data['county'] ?? '',
            ];
            redirectTo('/register');
        }
        
        // Success - log them in
        AuthService::login($result);
        
        $_SESSION['flash_success'] = 'Welcome to The Giving Grid! Your account has been created.';
        redirectTo('/dashboard');
    }
    
    /**
     * Process logout
     */
    public function logout(): void
    {
        AuthService::logout();
        
        $_SESSION['flash_success'] = 'You have been logged out.';
        redirectTo('/');
    }
}
