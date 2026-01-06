<?php
/**
 * Homepage
 * 
 * Orientation + action entry points.
 * This is a placeholder that will be expanded in Batch 2.
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
            <div class="stat">
                <span class="stat-number">&mdash;</span>
                <span class="stat-label">Active Needs</span>
            </div>
            <div class="stat">
                <span class="stat-number">&mdash;</span>
                <span class="stat-label">Offers</span>
            </div>
            <div class="stat">
                <span class="stat-number">&mdash;</span>
                <span class="stat-label">Volunteers</span>
            </div>
        </div>
        <p class="snapshot-note">
            <em>Live stats coming soon.</em>
        </p>
    </div>
</section>

<section class="quick-entry">
    <div class="entry-container">
        <div class="entry-card entry-needs">
            <h3>🟥 Needs Near You</h3>
            <p>See what local organizations need right now.</p>
            <a href="<?= url('/browse?type=need') ?>">View All Needs →</a>
        </div>
        <div class="entry-card entry-offers">
            <h3>🟩 Offers Available</h3>
            <p>Browse surplus resources from your community.</p>
            <a href="<?= url('/browse?type=offer') ?>">View All Offers →</a>
        </div>
    </div>
</section>

<section class="trust-section">
    <div class="trust-container">
        <p class="trust-message">
            Verified organizations. Safety-first approach. No fees, ever.
        </p>
    </div>
</section>
