<?php
/**
 * Report Form
 */
?>

<section class="report-form-page">
    <div class="report-form-container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>">Home</a>
            <span aria-hidden="true">›</span>
            <?php if ($type === 'listing'): ?>
                <a href="<?= listingUrl($target->id) ?>"><?= e($target->title) ?></a>
            <?php endif; ?>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Report</span>
        </nav>
        
        <header class="report-form-header">
            <h1>Report <?= e(ucfirst($type)) ?></h1>
            <p>Help us keep The Giving Grid safe for everyone.</p>
        </header>
        
        <?php if ($alreadyReported): ?>
            <div class="alert alert-info">
                <p>
                    <strong>You've already reported this <?= e($type) ?>.</strong>
                    Our team will review it shortly. Thank you for helping keep our community safe.
                </p>
            </div>
            <a href="<?= $type === 'listing' ? listingUrl($target->id) : url('/') ?>" class="btn btn-primary">
                Go Back
            </a>
        <?php else: ?>
            
            <!-- Target Summary -->
            <?php if ($type === 'listing'): ?>
                <div class="report-target-summary">
                    <span class="type-badge type-badge-<?= e($target->type) ?>">
                        <?= e($target->getTypeInfo()['icon']) ?> <?= e($target->getTypeInfo()['label']) ?>
                    </span>
                    <h3><?= e($target->title) ?></h3>
                    <p class="target-meta">
                        Posted by <?= e($target->getPosterName()) ?> in <?= e($target->getCountyName()) ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= url('/report') ?>" class="report-form">
                <?= csrfField() ?>
                <input type="hidden" name="type" value="<?= e($type) ?>">
                <input type="hidden" name="target_id" value="<?= e($target->id) ?>">
                
                <!-- Reason -->
                <div class="form-group <?= isset($errors['reason']) ? 'has-error' : '' ?>">
                    <label>Reason for Report <span class="required">*</span></label>
                    
                    <div class="report-reasons">
                        <?php foreach (REPORT_REASONS as $key => $reason): ?>
                            <label class="report-reason-option">
                                <input 
                                    type="radio" 
                                    name="reason" 
                                    value="<?= e($key) ?>"
                                    <?= ($oldInput['reason'] ?? '') === $key ? 'checked' : '' ?>
                                    required
                                >
                                <span class="reason-content">
                                    <strong><?= e($reason['label']) ?></strong>
                                    <span class="reason-description"><?= e($reason['description']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (isset($errors['reason'])): ?>
                        <span class="form-error"><?= e($errors['reason']) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Details -->
                <div class="form-group <?= isset($errors['details']) ? 'has-error' : '' ?>">
                    <label for="details">
                        Additional Details 
                        <span class="optional">(required for "Other")</span>
                    </label>
                    <textarea 
                        id="details" 
                        name="details" 
                        rows="4"
                        maxlength="1000"
                        placeholder="Please provide any additional context that would help us review this report..."
                    ><?= e($oldInput['details'] ?? '') ?></textarea>
                    <?php if (isset($errors['details'])): ?>
                        <span class="form-error"><?= e($errors['details']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Submit Report
                    </button>
                    <a href="<?= $type === 'listing' ? listingUrl($target->id) : url('/') ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
            
            <div class="report-notice">
                <h3>What happens next?</h3>
                <ul>
                    <li>Our team will review your report within 24-48 hours</li>
                    <li>We may contact you if we need more information</li>
                    <li>Appropriate action will be taken if the report is valid</li>
                    <li>All reports are kept confidential</li>
                </ul>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>
