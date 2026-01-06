<?php
/**
 * Route Definitions
 * 
 * Maps URL patterns to controller actions.
 * Format: 'METHOD /path' => ['Controller', 'method', ...middleware]
 * 
 * Route parameters use {name} syntax and are passed to the controller method.
 */

declare(strict_types=1);

return [
    // -------------------------------------------------------------------------
    // Public Routes (No Authentication Required)
    // -------------------------------------------------------------------------
    
    // Homepage
    'GET /' => ['HomeController', 'index'],
    
    // Browse listings
    'GET /browse' => ['BrowseController', 'index'],
    
    // Single listing detail
    'GET /listing/{id}' => ['ListingController', 'show'],
    
    // Organizations directory
    'GET /organizations' => ['OrgController', 'index'],
    
    // Single organization profile
    'GET /organization/{id}' => ['OrgController', 'show'],
    
    // -------------------------------------------------------------------------
    // Authentication Routes
    // -------------------------------------------------------------------------
    
    'GET /login' => ['AuthController', 'loginForm'],
    'POST /login' => ['AuthController', 'login'],
    'GET /register' => ['AuthController', 'registerForm'],
    'POST /register' => ['AuthController', 'register'],
    'POST /logout' => ['AuthController', 'logout', 'auth'],
    
    // -------------------------------------------------------------------------
    // Protected Routes (Authentication Required)
    // -------------------------------------------------------------------------
    
    // Dashboard
    'GET /dashboard' => ['DashboardController', 'index', 'auth'],
    
    // Post listings
    'GET /post' => ['ListingController', 'create', 'auth'],
    'POST /post' => ['ListingController', 'store', 'auth', 'csrf'],
    
    // Manage own listings
    'GET /listing/{id}/edit' => ['ListingController', 'edit', 'auth'],
    'POST /listing/{id}/edit' => ['ListingController', 'update', 'auth', 'csrf'],
    'POST /listing/{id}/status' => ['ListingController', 'updateStatus', 'auth', 'csrf'],
    'POST /listing/{id}/delete' => ['ListingController', 'delete', 'auth', 'csrf'],
    
    // Respond to listings ("I Can Help")
    'GET /listing/{id}/respond' => ['ResponseController', 'create', 'auth'],
    'POST /listing/{id}/respond' => ['ResponseController', 'store', 'auth', 'csrf'],
    
    // Messages/coordination
    'GET /responses' => ['ResponseController', 'index', 'auth'],
    'GET /responses/{id}' => ['ResponseController', 'show', 'auth'],
    'POST /responses/{id}/message' => ['ResponseController', 'sendMessage', 'auth', 'csrf'],
    'POST /responses/{id}/status' => ['ResponseController', 'updateStatus', 'auth', 'csrf'],
    
    // -------------------------------------------------------------------------
    // Reporting (Optional Auth)
    // -------------------------------------------------------------------------
    
    'GET /report/listing/{id}' => ['ReportController', 'form'],
    'POST /report' => ['ReportController', 'store', 'csrf'],
    
    // -------------------------------------------------------------------------
    // Admin Routes
    // -------------------------------------------------------------------------
    
    'GET /admin' => ['AdminController', 'index', 'auth', 'admin'],
    'GET /admin/verify' => ['AdminController', 'verifyQueue', 'auth', 'admin'],
    'POST /admin/verify/{id}' => ['AdminController', 'verify', 'auth', 'admin', 'csrf'],
    'POST /admin/reject/{id}' => ['AdminController', 'reject', 'auth', 'admin', 'csrf'],
    'GET /admin/reports' => ['AdminController', 'reportsQueue', 'auth', 'admin'],
    'GET /admin/reports/{id}' => ['AdminController', 'viewReport', 'auth', 'admin'],
    'POST /admin/reports/{id}/resolve' => ['AdminController', 'resolveReport', 'auth', 'admin', 'csrf'],
];
