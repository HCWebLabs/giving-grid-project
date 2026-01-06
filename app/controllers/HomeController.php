<?php
/**
 * Home Controller
 * 
 * Handles the homepage and public landing pages.
 */

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    /**
     * Display the homepage
     */
    public function index(): void
    {
        // In Batch 2, we'll fetch live stats here:
        // - Active needs count
        // - Active offers count
        // - Volunteer opportunities count
        
        render('pages/home', [
            'pageTitle' => 'Home',
            'metaDescription' => 'The Giving Grid connects needs, surplus, and volunteers across Tennessee communities. Find help, offer resources, or volunteer your time.'
        ]);
    }
}
