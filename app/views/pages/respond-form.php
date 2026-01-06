<?php
/**
 * Respond to Listing Form
 */

$typeInfo = $listing->getTypeInfo();
?>

<section class="respond-form-page">
    <div class="respond-form-container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>">Home</a>
            <span aria-hidden="true">›</span>
            <a href="<?= listingUrl($listing->id) ?>"><?= e($listing->title) ?></a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Respond</span>
        </nav>
        
        <!-- Listing Summary -->
        <div class="listing-summary">
            <span class="type-badge type-badge-<?= e($listing->type) ?>">
                <?= e($typeInfo['icon']) ?> <?= e($typeInfo['label']) ?>
            </span>
            <h2><?= e($listing->title) ?></h2>
            <p class="listing-summary-meta">
                Posted by 
                <?php if ($listing->org_id): ?>
                    <strong><?= e($listing->org_name) ?></strong>
                    <?php if ($listing->is_verified): ?>
                        <span class="verified-badge">✓</span>
                    <?php endif; ?>
                <?php else: ?>
                    <?= e($listing->poster_name ?? 'Community Member') ?>
                <?php endif; ?>
                in <?= e($listing->getCountyName()) ?>
            </p>
        </div>
        
        <!-- Response Form -->
        <form method="POST" action="<?= url("/listing/{$listing->id}/respond") ?>" class="respond-form">
            <?= csrfField() ?>
            
            <div class="respond-form-header">
                <h1>
                    <?php if ($listing->type === 'need'): ?>
                        I Can Help
                    <?php elseif ($listing->type === 'offer'): ?>
                        I'm Interested
                    <?php else: ?>
                        I Want to Volunteer
                    <?php endif; ?>
                </h1>
                <p>Send a message to start coordinating.</p>
            </div>
            
            <div class="form-group <?= isset($errors['message']) ? 'has-error' : '' ?>">
                <label for="message">Your Message <span class="required">*</span></label>
                <textarea 
                    id="message" 
                    name="message" 
                    required
                    minlength="10"
                    maxlength="2000"
                    rows="6"
                    placeholder="<?php if ($listing->type === 'need'): ?>Introduce yourself and explain how you can help...<?php elseif ($listing->type === 'offer'): ?>Tell them why you're interested and any relevant details...<?php else: ?>Share your availability and any relevant experience...<?php endif; ?>"
                ><?= e($oldInput['message'] ?? '') ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <span class="form-error"><?= e($errors['message']) ?></span>
                <?php endif; ?>
                <span class="form-hint">Be specific about your availability and how to coordinate.</span>
            </div>
            
            <div class="respond-form-tips">
                <h3>Tips for a Good Response</h3>
                <ul>
                    <li>Introduce yourself briefly</li>
                    <li>Be specific about what you can offer or when you're available</li>
                    <li>Ask any clarifying questions you have</li>
                    <li>Suggest a way to coordinate (pickup, delivery, meeting)</li>
                </ul>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    Send Response
                </button>
                <a href="<?= listingUrl($listing->id) ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
            
        </form>
        
        <div class="safety-reminder">
            <h3>🛡️ Safety First</h3>
            <p>
                Meet in public places when possible. Never share personal financial information. 
                Report any suspicious behavior.
            </p>
        </div>
        
    </div>
</section>
