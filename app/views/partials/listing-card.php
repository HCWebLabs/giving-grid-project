<?php
/**
 * Listing Card Partial
 * 
 * Displays a single listing in a card format for browse grids.
 * Expected variables: $listing (Listing model)
 */

$typeInfo = $listing->getTypeInfo();
$urgencyInfo = $listing->getUrgencyInfo();
?>

<article class="listing-card listing-card-<?= e($listing->type) ?>">
    <a href="<?= listingUrl($listing->id) ?>" class="listing-card-link">
        
        <!-- Type Badge -->
        <div class="listing-card-type">
            <span class="type-badge type-badge-<?= e($listing->type) ?>">
                <?= e($typeInfo['icon']) ?> <?= e($typeInfo['label']) ?>
            </span>
            
            <?php if ($listing->urgency === 'critical' || $listing->urgency === 'high'): ?>
                <span class="urgency-badge urgency-badge-<?= e($listing->urgency) ?>">
                    <?= e($urgencyInfo['label']) ?>
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Title -->
        <h3 class="listing-card-title"><?= e($listing->title) ?></h3>
        
        <!-- Meta Info -->
        <div class="listing-card-meta">
            <!-- Category -->
            <span class="listing-card-category">
                <?= e($listing->getCategoryLabel()) ?>
            </span>
            
            <!-- Location -->
            <span class="listing-card-location">
                📍 <?= e($listing->getCountyName()) ?>
                <?php if ($listing->city): ?>
                    <span class="listing-card-city">(<?= e($listing->city) ?>)</span>
                <?php endif; ?>
            </span>
        </div>
        
        <!-- Description Preview -->
        <p class="listing-card-description">
            <?= e(substr(strip_tags($listing->description), 0, 120)) ?>
            <?= strlen($listing->description) > 120 ? '...' : '' ?>
        </p>
        
        <!-- Footer -->
        <footer class="listing-card-footer">
            <!-- Poster -->
            <span class="listing-card-poster">
                <?php if ($listing->org_id && $listing->is_verified): ?>
                    <span class="verified-badge" title="Verified Organization">✓</span>
                <?php endif; ?>
                <?= e($listing->getPosterName()) ?>
            </span>
            
            <!-- Time -->
            <span class="listing-card-time">
                <?= e($listing->getTimeAgo()) ?>
            </span>
        </footer>
        
    </a>
</article>
