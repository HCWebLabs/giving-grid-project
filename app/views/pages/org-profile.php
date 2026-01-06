<?php
/**
 * Organization Profile Page
 * 
 * Displays full details of a verified organization and their listings.
 * Expected variables: $organization, $needs, $offers, $volunteerOpps
 */
?>

<article class="org-profile">
    <div class="org-profile-container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>">Home</a>
            <span aria-hidden="true">›</span>
            <a href="<?= url('/organizations') ?>">Organizations</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($organization->name) ?></span>
        </nav>
        
        <!-- Header -->
        <header class="org-profile-header">
            <div class="org-profile-title-row">
                <h1 class="org-profile-name">
                    <?= e($organization->name) ?>
                </h1>
                <?php if ($organization->is_verified): ?>
                    <span class="verified-badge verified-badge-large">✓ Verified Organization</span>
                <?php endif; ?>
            </div>
            
            <p class="org-profile-location">
                📍 Serving <?= e($organization->getCountyName()) ?>
            </p>
        </header>
        
        <!-- Main Content -->
        <div class="org-profile-main">
            
            <!-- About Section -->
            <section class="org-profile-about">
                <h2>About</h2>
                <?php if ($organization->mission): ?>
                    <div class="org-profile-mission">
                        <?= nl2br(e($organization->mission)) ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No mission statement provided.</p>
                <?php endif; ?>
            </section>
            
            <!-- Contact Info -->
            <section class="org-profile-contact">
                <h2>Contact Information</h2>
                <dl class="contact-list">
                    <div class="contact-item">
                        <dt>Email</dt>
                        <dd>
                            <a href="mailto:<?= e($organization->contact_email) ?>">
                                <?= e($organization->contact_email) ?>
                            </a>
                        </dd>
                    </div>
                    
                    <?php if ($organization->contact_phone): ?>
                        <div class="contact-item">
                            <dt>Phone</dt>
                            <dd>
                                <a href="tel:<?= e($organization->contact_phone) ?>">
                                    <?= e($organization->contact_phone) ?>
                                </a>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($organization->hasWebsite()): ?>
                        <div class="contact-item">
                            <dt>Website</dt>
                            <dd>
                                <a href="<?= e($organization->getWebsiteUrl()) ?>" target="_blank" rel="noopener">
                                    <?= e($organization->getWebsiteDisplay()) ?>
                                </a>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($organization->address): ?>
                        <div class="contact-item">
                            <dt>Address</dt>
                            <dd><?= nl2br(e($organization->address)) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>
            
        </div>
        
        <!-- Sidebar Stats -->
        <aside class="org-profile-sidebar">
            <div class="org-stats-panel">
                <h2>Activity</h2>
                <div class="org-stats-grid">
                    <div class="org-stat-box">
                        <span class="org-stat-number"><?= count($needs) ?></span>
                        <span class="org-stat-label">Active Needs</span>
                    </div>
                    <div class="org-stat-box">
                        <span class="org-stat-number"><?= count($offers) ?></span>
                        <span class="org-stat-label">Active Offers</span>
                    </div>
                    <div class="org-stat-box">
                        <span class="org-stat-number"><?= count($volunteerOpps) ?></span>
                        <span class="org-stat-label">Volunteer Opps</span>
                    </div>
                </div>
            </div>
        </aside>
        
    </div>
    
    <!-- Active Listings -->
    <div class="org-listings-container">
        
        <!-- Needs -->
        <?php if (!empty($needs)): ?>
            <section class="org-listings-section">
                <h2>🟥 Current Needs</h2>
                <div class="listings-grid">
                    <?php foreach ($needs as $listing): ?>
                        <?php includePartial('partials/listing-card', ['listing' => $listing]) ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- Offers -->
        <?php if (!empty($offers)): ?>
            <section class="org-listings-section">
                <h2>🟩 Available Offers</h2>
                <div class="listings-grid">
                    <?php foreach ($offers as $listing): ?>
                        <?php includePartial('partials/listing-card', ['listing' => $listing]) ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- Volunteer Opportunities -->
        <?php if (!empty($volunteerOpps)): ?>
            <section class="org-listings-section">
                <h2>🟦 Volunteer Opportunities</h2>
                <div class="listings-grid">
                    <?php foreach ($volunteerOpps as $listing): ?>
                        <?php includePartial('partials/listing-card', ['listing' => $listing]) ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- No Listings -->
        <?php if (empty($needs) && empty($offers) && empty($volunteerOpps)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h2>No Active Listings</h2>
                <p>This organization doesn't have any active listings right now.</p>
            </div>
        <?php endif; ?>
        
    </div>
    
</article>
