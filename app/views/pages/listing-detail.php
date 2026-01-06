<?php
/**
 * Listing Detail Page
 * 
 * Displays full details of a single listing with action panel.
 * Expected variables: $listing, $relatedListings
 */

$typeInfo = $listing->getTypeInfo();
$urgencyInfo = $listing->getUrgencyInfo();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<article class="listing-detail">
    <div class="listing-detail-container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>">Home</a>
            <span aria-hidden="true">›</span>
            <a href="<?= url('/browse?type=' . $listing->type) ?>"><?= e($typeInfo['plural']) ?></a>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($listing->title) ?></span>
        </nav>
        
        <!-- Main Content -->
        <div class="listing-detail-main">
            
            <!-- Header -->
            <header class="listing-detail-header">
                <div class="listing-detail-badges">
                    <span class="type-badge type-badge-<?= e($listing->type) ?> type-badge-large">
                        <?= e($typeInfo['icon']) ?> <?= e($typeInfo['label']) ?>
                    </span>
                    <span class="urgency-badge urgency-badge-<?= e($listing->urgency) ?>">
                        <?= e($urgencyInfo['label']) ?> Urgency
                    </span>
                    <span class="status-badge status-badge-<?= e($listing->status) ?>">
                        <?= e(ucfirst($listing->status)) ?>
                    </span>
                </div>
                
                <h1 class="listing-detail-title"><?= e($listing->title) ?></h1>
                
                <p class="listing-detail-time">
                    Posted <?= e($listing->getTimeAgo()) ?>
                </p>
            </header>
            
            <!-- Core Info -->
            <section class="listing-detail-info">
                <h2 class="sr-only">Listing Information</h2>
                
                <dl class="info-grid">
                    <!-- Organization / Poster -->
                    <div class="info-item">
                        <dt>Posted By</dt>
                        <dd>
                            <?php if ($listing->org_id): ?>
                                <a href="<?= orgUrl($listing->org_id) ?>">
                                    <?= e($listing->org_name) ?>
                                </a>
                                <?php if ($listing->is_verified): ?>
                                    <span class="verified-badge" title="Verified Organization">✓ Verified</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= e($listing->poster_name ?? 'Community Member') ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                    
                    <!-- Category -->
                    <div class="info-item">
                        <dt>Category</dt>
                        <dd><?= e($listing->getCategoryLabel()) ?></dd>
                    </div>
                    
                    <!-- Quantity -->
                    <?php if ($listing->quantity): ?>
                        <div class="info-item">
                            <dt>Quantity</dt>
                            <dd><?= e($listing->quantity) ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Location -->
                    <div class="info-item">
                        <dt>Location</dt>
                        <dd>
                            <?= e($listing->getCountyName()) ?>
                            <?php if ($listing->city): ?>
                                <span class="text-muted">(<?= e($listing->city) ?>)</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    
                    <!-- Logistics -->
                    <?php if ($listing->logistics !== 'na'): ?>
                        <div class="info-item">
                            <dt>Logistics</dt>
                            <dd><?= e($listing->getLogisticsLabel()) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>
            
            <!-- Description -->
            <section class="listing-detail-description">
                <h2>Description</h2>
                <div class="description-content">
                    <?= nl2br(e($listing->description)) ?>
                </div>
            </section>
            
            <!-- Cause Tags -->
            <?php if (!empty($listing->causes)): ?>
                <section class="listing-detail-causes">
                    <h2>Related Causes</h2>
                    <div class="cause-tags">
                        <?php foreach ($listing->causes as $cause): ?>
                            <a 
                                href="<?= browseUrl(['cause' => $cause->slug]) ?>" 
                                class="cause-tag"
                            >
                                #<?= e($cause->name) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
        </div>
        
        <!-- Sidebar / Action Panel -->
        <aside class="listing-detail-sidebar">
            
            <!-- Owner Controls -->
            <?php if ($isOwner): ?>
                <div class="owner-panel">
                    <h2>Manage Listing</h2>
                    
                    <div class="owner-actions">
                        <a href="<?= url("/listing/{$listing->id}/edit") ?>" class="btn btn-secondary btn-block">
                            Edit Listing
                        </a>
                        
                        <?php if ($listing->status === 'open'): ?>
                            <form method="POST" action="<?= url("/listing/{$listing->id}/status") ?>" class="status-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="status" value="fulfilled">
                                <button type="submit" class="btn btn-success btn-block">
                                    Mark as Fulfilled
                                </button>
                            </form>
                            
                            <form method="POST" action="<?= url("/listing/{$listing->id}/status") ?>" class="status-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="btn btn-tertiary btn-block">
                                    Close Listing
                                </button>
                            </form>
                        <?php elseif ($listing->status === 'in_progress'): ?>
                            <form method="POST" action="<?= url("/listing/{$listing->id}/status") ?>" class="status-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="status" value="fulfilled">
                                <button type="submit" class="btn btn-success btn-block">
                                    Mark as Fulfilled
                                </button>
                            </form>
                            
                            <form method="POST" action="<?= url("/listing/{$listing->id}/status") ?>" class="status-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="btn btn-tertiary btn-block">
                                    Reopen Listing
                                </button>
                            </form>
                        <?php elseif ($listing->status === 'closed'): ?>
                            <form method="POST" action="<?= url("/listing/{$listing->id}/status") ?>" class="status-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="btn btn-primary btn-block">
                                    Reopen Listing
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Panel -->
            <?php if ($listing->isOpen()): ?>
                <div class="action-panel">
                    <h2>Ready to Help?</h2>
                    
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= url("/listing/{$listing->id}/respond") ?>" class="btn btn-primary btn-large btn-block">
                            <?php if ($listing->type === 'need'): ?>
                                I Can Help
                            <?php elseif ($listing->type === 'offer'): ?>
                                I'm Interested
                            <?php else: ?>
                                I Want to Volunteer
                            <?php endif; ?>
                        </a>
                        <p class="action-panel-note">
                            You'll be able to send a message to coordinate.
                        </p>
                    <?php else: ?>
                        <p class="action-panel-login-prompt">
                            Log in to respond to this listing.
                        </p>
                        <a href="<?= url('/login?redirect=' . urlencode("/listing/{$listing->id}")) ?>" class="btn btn-primary btn-block">
                            Log In
                        </a>
                        <a href="<?= url('/register') ?>" class="btn btn-secondary btn-block">
                            Create Account
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="action-panel action-panel-closed">
                    <h2>Listing <?= e(ucfirst($listing->status)) ?></h2>
                    <p>This listing is no longer accepting responses.</p>
                </div>
            <?php endif; ?>
            
            <!-- Safety / Report -->
            <div class="safety-panel">
                <h3>Safety First</h3>
                <p>Meet in public places. Never share personal financial information.</p>
                <a href="<?= url("/report/listing/{$listing->id}") ?>" class="report-link">
                    Report this listing
                </a>
            </div>
            
        </aside>
        
    </div>
    
    <!-- Related Listings -->
    <?php if (!empty($relatedListings)): ?>
        <section class="related-listings">
            <div class="related-listings-container">
                <h2>Similar Listings</h2>
                <div class="listings-grid listings-grid-small">
                    <?php foreach ($relatedListings as $related): ?>
                        <?php includePartial('partials/listing-card', ['listing' => $related]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
</article>
