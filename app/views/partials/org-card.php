<?php
/**
 * Organization Card Partial
 * 
 * Displays an organization in a card format for the directory.
 * Expected variables: $organization (Organization model)
 */
?>

<article class="org-card">
    <a href="<?= orgUrl($organization->id) ?>" class="org-card-link">
        
        <!-- Header -->
        <header class="org-card-header">
            <h3 class="org-card-name">
                <?= e($organization->name) ?>
                <?php if ($organization->is_verified): ?>
                    <span class="verified-badge" title="Verified Organization">✓</span>
                <?php endif; ?>
            </h3>
            <p class="org-card-location">
                📍 <?= e($organization->getCountyName()) ?>
            </p>
        </header>
        
        <!-- Mission -->
        <?php if ($organization->mission): ?>
            <p class="org-card-mission">
                <?= e(substr(strip_tags($organization->mission), 0, 120)) ?>
                <?= strlen($organization->mission) > 120 ? '...' : '' ?>
            </p>
        <?php endif; ?>
        
        <!-- Stats -->
        <footer class="org-card-footer">
            <div class="org-card-stats">
                <?php if ($organization->active_needs_count > 0): ?>
                    <span class="org-stat org-stat-needs">
                        <?= $organization->active_needs_count ?> <?= $organization->active_needs_count === 1 ? 'Need' : 'Needs' ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($organization->active_offers_count > 0): ?>
                    <span class="org-stat org-stat-offers">
                        <?= $organization->active_offers_count ?> <?= $organization->active_offers_count === 1 ? 'Offer' : 'Offers' ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($organization->volunteer_count > 0): ?>
                    <span class="org-stat org-stat-volunteer">
                        <?= $organization->volunteer_count ?> Volunteer <?= $organization->volunteer_count === 1 ? 'Opp' : 'Opps' ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($organization->getTotalActiveListings() === 0): ?>
                    <span class="org-stat org-stat-none">No active listings</span>
                <?php endif; ?>
            </div>
        </footer>
        
    </a>
</article>
