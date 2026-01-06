<?php
/**
 * Homepage
 * 
 * Orientation + action entry points.
 */
?>

<section class="hero">
    <div class="hero-container">
        <h1 class="hero-title"><?= e(APP_NAME) ?></h1>
        <p class="hero-tagline">
            Connecting needs, surplus, and volunteers across Tennessee communities.
        </p>
        <div class="hero-actions">
            <a href="<?= url('/browse?type=need') ?>" class="btn btn-primary">Find Help</a>
            <a href="<?= url('/browse?type=offer') ?>" class="btn btn-secondary">Offer Help</a>
            <a href="<?= url('/browse?type=volunteer') ?>" class="btn btn-tertiary">Get Involved</a>
        </div>
    </div>
</section>

<section class="grid-snapshot">
    <div class="snapshot-container">
        <h2 class="sr-only">Current Grid Status</h2>
        <div class="snapshot-stats">
            <a href="<?= url('/browse?type=need') ?>" class="stat stat-link">
                <span class="stat-number"><?= number_format($stats['need'] ?? 0) ?></span>
                <span class="stat-label">Active Needs</span>
            </a>
            <a href="<?= url('/browse?type=offer') ?>" class="stat stat-link">
                <span class="stat-number"><?= number_format($stats['offer'] ?? 0) ?></span>
                <span class="stat-label">Offers</span>
            </a>
            <a href="<?= url('/browse?type=volunteer') ?>" class="stat stat-link">
                <span class="stat-number"><?= number_format($stats['volunteer'] ?? 0) ?></span>
                <span class="stat-label">Volunteer Opps</span>
            </a>
            <a href="<?= url('/organizations') ?>" class="stat stat-link">
                <span class="stat-number"><?= number_format($orgCount ?? 0) ?></span>
                <span class="stat-label">Organizations</span>
            </a>
        </div>
    </div>
</section>

<section class="quick-entry">
    <div class="entry-container">
        <div class="entry-card entry-needs">
            <h3>🟥 Needs Near You</h3>
            <p>See what local organizations need right now.</p>
            
            <?php if (!empty($recentNeeds)): ?>
                <ul class="entry-preview-list">
                    <?php foreach ($recentNeeds as $listing): ?>
                        <li>
                            <a href="<?= listingUrl($listing->id) ?>">
                                <?= e($listing->title) ?>
                                <span class="entry-preview-meta">
                                    <?= e($listing->getCountyName()) ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <a href="<?= url('/browse?type=need') ?>" class="entry-view-all">View All Needs →</a>
        </div>
        
        <div class="entry-card entry-offers">
            <h3>🟩 Offers Available</h3>
            <p>Browse surplus resources from your community.</p>
            
            <?php if (!empty($recentOffers)): ?>
                <ul class="entry-preview-list">
                    <?php foreach ($recentOffers as $listing): ?>
                        <li>
                            <a href="<?= listingUrl($listing->id) ?>">
                                <?= e($listing->title) ?>
                                <span class="entry-preview-meta">
                                    <?= e($listing->getCountyName()) ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <a href="<?= url('/browse?type=offer') ?>" class="entry-view-all">View All Offers →</a>
        </div>
    </div>
</section>

<section class="trust-section">
    <div class="trust-container">
        <p class="trust-message">
            Verified organizations. Safety-first approach. No fees, ever.
        </p>
        <a href="<?= url('/organizations') ?>" class="btn btn-secondary">View Organizations</a>
    </div>
</section>
