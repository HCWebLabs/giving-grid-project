<?php
/**
 * Application Constants
 * 
 * Enums and static data for The Giving Grid.
 * Categories, urgency levels, counties, and other lookup values.
 */

declare(strict_types=1);

// ─────────────────────────────────────────────────────────────────────────────
// Listing Types
// ─────────────────────────────────────────────────────────────────────────────

define('LISTING_TYPES', [
    'need' => [
        'label' => 'Need',
        'plural' => 'Needs',
        'icon' => '🟥',
        'color' => 'need',
        'description' => 'Something an organization needs from the community'
    ],
    'offer' => [
        'label' => 'Offer',
        'plural' => 'Offers',
        'icon' => '🟩',
        'color' => 'offer',
        'description' => 'Resources or items available to share'
    ],
    'volunteer' => [
        'label' => 'Volunteer',
        'plural' => 'Volunteer Opportunities',
        'icon' => '🟦',
        'color' => 'volunteer',
        'description' => 'Opportunities to give your time'
    ]
]);

// ─────────────────────────────────────────────────────────────────────────────
// Categories
// ─────────────────────────────────────────────────────────────────────────────

define('CATEGORIES', [
    'food' => 'Food & Meals',
    'clothing' => 'Clothing & Apparel',
    'household' => 'Household Items',
    'furniture' => 'Furniture',
    'medical' => 'Medical & Health',
    'hygiene' => 'Hygiene & Personal Care',
    'baby' => 'Baby & Children',
    'school' => 'School Supplies',
    'electronics' => 'Electronics',
    'transportation' => 'Transportation',
    'housing' => 'Housing & Shelter',
    'utilities' => 'Utilities & Bills',
    'services' => 'Services',
    'skills' => 'Skills & Expertise',
    'other' => 'Other'
]);

// ─────────────────────────────────────────────────────────────────────────────
// Urgency Levels
// ─────────────────────────────────────────────────────────────────────────────

define('URGENCY_LEVELS', [
    'low' => [
        'label' => 'Low',
        'description' => 'No immediate deadline',
        'color' => 'gray'
    ],
    'medium' => [
        'label' => 'Medium',
        'description' => 'Needed within a few weeks',
        'color' => 'yellow'
    ],
    'high' => [
        'label' => 'High',
        'description' => 'Needed within days',
        'color' => 'orange'
    ],
    'critical' => [
        'label' => 'Critical',
        'description' => 'Immediate need',
        'color' => 'red'
    ]
]);

// ─────────────────────────────────────────────────────────────────────────────
// Listing Statuses
// ─────────────────────────────────────────────────────────────────────────────

define('LISTING_STATUSES', [
    'open' => [
        'label' => 'Open',
        'description' => 'Actively seeking help'
    ],
    'in_progress' => [
        'label' => 'In Progress',
        'description' => 'Being coordinated'
    ],
    'fulfilled' => [
        'label' => 'Fulfilled',
        'description' => 'Successfully completed'
    ],
    'closed' => [
        'label' => 'Closed',
        'description' => 'No longer active'
    ]
]);

// ─────────────────────────────────────────────────────────────────────────────
// Logistics Options
// ─────────────────────────────────────────────────────────────────────────────

define('LOGISTICS_OPTIONS', [
    'pickup' => 'Pickup Required',
    'delivery' => 'Delivery Available',
    'either' => 'Pickup or Delivery',
    'na' => 'Not Applicable'
]);

// ─────────────────────────────────────────────────────────────────────────────
// East Tennessee Counties (Phase 1)
// ─────────────────────────────────────────────────────────────────────────────

define('COUNTIES', [
    'anderson' => 'Anderson County',
    'blount' => 'Blount County',
    'campbell' => 'Campbell County',
    'claiborne' => 'Claiborne County',
    'cocke' => 'Cocke County',
    'grainger' => 'Grainger County',
    'hamblen' => 'Hamblen County',
    'jefferson' => 'Jefferson County',
    'knox' => 'Knox County',
    'loudon' => 'Loudon County',
    'monroe' => 'Monroe County',
    'morgan' => 'Morgan County',
    'roane' => 'Roane County',
    'scott' => 'Scott County',
    'sevier' => 'Sevier County',
    'union' => 'Union County'
]);

// ─────────────────────────────────────────────────────────────────────────────
// User Roles
// ─────────────────────────────────────────────────────────────────────────────

define('USER_ROLES', [
    'individual' => [
        'label' => 'Individual',
        'description' => 'Community member'
    ],
    'org_member' => [
        'label' => 'Organization Member',
        'description' => 'Member of a verified organization'
    ],
    'admin' => [
        'label' => 'Administrator',
        'description' => 'Platform administrator'
    ]
]);

// ─────────────────────────────────────────────────────────────────────────────
// Report Reasons
// ─────────────────────────────────────────────────────────────────────────────

define('REPORT_REASONS', [
    'spam' => 'Spam or misleading',
    'inappropriate' => 'Inappropriate content',
    'scam' => 'Potential scam',
    'harassment' => 'Harassment or abuse',
    'incorrect' => 'Incorrect information',
    'duplicate' => 'Duplicate listing',
    'other' => 'Other concern'
]);

// ─────────────────────────────────────────────────────────────────────────────
// Helper Functions
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get a listing type's display info
 */
function getListingType(string $type): ?array
{
    return LISTING_TYPES[$type] ?? null;
}

/**
 * Get a category's display label
 */
function getCategoryLabel(string $category): string
{
    return CATEGORIES[$category] ?? ucfirst($category);
}

/**
 * Get an urgency level's display info
 */
function getUrgencyLevel(string $urgency): ?array
{
    return URGENCY_LEVELS[$urgency] ?? null;
}

/**
 * Get a county's display name
 */
function getCountyName(string $county): string
{
    return COUNTIES[$county] ?? ucfirst($county) . ' County';
}

/**
 * Get logistics option label
 */
function getLogisticsLabel(string $logistics): string
{
    return LOGISTICS_OPTIONS[$logistics] ?? 'N/A';
}

/**
 * Get status display info
 */
function getStatusInfo(string $status): ?array
{
    return LISTING_STATUSES[$status] ?? null;
}
