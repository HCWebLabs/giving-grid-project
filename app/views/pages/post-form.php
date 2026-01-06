<?php
/**
 * Post / Edit Listing Form
 * 
 * Unified form for creating and editing listings.
 */

$formAction = $isEdit ? url("/listing/{$listing->id}/edit") : url('/post');
$submitLabel = $isEdit ? 'Save Changes' : 'Post to the Grid';
?>

<section class="post-form-page">
    <div class="post-form-container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>">Home</a>
            <span aria-hidden="true">›</span>
            <?php if ($isEdit): ?>
                <a href="<?= listingUrl($listing->id) ?>"><?= e($listing->title) ?></a>
                <span aria-hidden="true">›</span>
                <span aria-current="page">Edit</span>
            <?php else: ?>
                <a href="<?= url('/post') ?>">Post</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page"><?= e($typeInfo['label']) ?></span>
            <?php endif; ?>
        </nav>
        
        <header class="post-form-header">
            <h1>
                <?= e($typeInfo['icon']) ?>
                <?= $isEdit ? 'Edit Listing' : 'Post a ' . $typeInfo['label'] ?>
            </h1>
            <p class="post-form-subtitle">
                <?= e($typeInfo['description']) ?>
            </p>
        </header>
        
        <form method="POST" action="<?= $formAction ?>" class="post-form">
            <?= csrfField() ?>
            
            <?php if (!$isEdit): ?>
                <input type="hidden" name="type" value="<?= e($type) ?>">
            <?php endif; ?>
            
            <!-- Title -->
            <div class="form-group <?= isset($errors['title']) ? 'has-error' : '' ?>">
                <label for="title">Title <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?= e($oldInput['title'] ?? '') ?>"
                    required 
                    maxlength="255"
                    placeholder="Brief, descriptive title"
                    autofocus
                >
                <?php if (isset($errors['title'])): ?>
                    <span class="form-error"><?= e($errors['title']) ?></span>
                <?php endif; ?>
                <span class="form-hint">Be specific – "Winter Coats for Children" is better than "Clothing Needed"</span>
            </div>
            
            <!-- Category -->
            <div class="form-group <?= isset($errors['category']) ? 'has-error' : '' ?>">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Select a category...</option>
                    <?php foreach (CATEGORIES as $key => $label): ?>
                        <option 
                            value="<?= e($key) ?>"
                            <?= ($oldInput['category'] ?? '') === $key ? 'selected' : '' ?>
                        >
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <span class="form-error"><?= e($errors['category']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div class="form-group <?= isset($errors['description']) ? 'has-error' : '' ?>">
                <label for="description">Description <span class="required">*</span></label>
                <textarea 
                    id="description" 
                    name="description" 
                    required 
                    minlength="20"
                    maxlength="5000"
                    rows="6"
                    placeholder="Provide details about what you're <?= $type === 'offer' ? 'offering' : 'looking for' ?>..."
                ><?= e($oldInput['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span class="form-error"><?= e($errors['description']) ?></span>
                <?php endif; ?>
                <span class="form-hint">Include relevant details like condition, size, timing, requirements, etc.</span>
            </div>
            
            <!-- Quantity -->
            <div class="form-group <?= isset($errors['quantity']) ? 'has-error' : '' ?>">
                <label for="quantity">Quantity <span class="optional">(optional)</span></label>
                <input 
                    type="text" 
                    id="quantity" 
                    name="quantity" 
                    value="<?= e($oldInput['quantity'] ?? '') ?>"
                    maxlength="100"
                    placeholder='e.g., "10 boxes", "50 lbs", "Ongoing"'
                >
                <?php if (isset($errors['quantity'])): ?>
                    <span class="form-error"><?= e($errors['quantity']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Location Row -->
            <div class="form-row">
                <!-- County -->
                <div class="form-group <?= isset($errors['county']) ? 'has-error' : '' ?>">
                    <label for="county">County <span class="required">*</span></label>
                    <select id="county" name="county" required>
                        <option value="">Select county...</option>
                        <?php foreach (COUNTIES as $key => $label): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($oldInput['county'] ?? '') === $key ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['county'])): ?>
                        <span class="form-error"><?= e($errors['county']) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- City -->
                <div class="form-group <?= isset($errors['city']) ? 'has-error' : '' ?>">
                    <label for="city">City <span class="optional">(optional)</span></label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        value="<?= e($oldInput['city'] ?? '') ?>"
                        maxlength="100"
                        placeholder="e.g., Knoxville"
                    >
                    <?php if (isset($errors['city'])): ?>
                        <span class="form-error"><?= e($errors['city']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Urgency & Logistics Row -->
            <div class="form-row">
                <!-- Urgency -->
                <div class="form-group <?= isset($errors['urgency']) ? 'has-error' : '' ?>">
                    <label for="urgency">Urgency</label>
                    <select id="urgency" name="urgency">
                        <?php foreach (URGENCY_LEVELS as $key => $level): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($oldInput['urgency'] ?? 'medium') === $key ? 'selected' : '' ?>
                            >
                                <?= e($level['label']) ?> – <?= e($level['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['urgency'])): ?>
                        <span class="form-error"><?= e($errors['urgency']) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Logistics -->
                <div class="form-group <?= isset($errors['logistics']) ? 'has-error' : '' ?>">
                    <label for="logistics">Logistics</label>
                    <select id="logistics" name="logistics">
                        <?php foreach (LOGISTICS_OPTIONS as $key => $label): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($oldInput['logistics'] ?? 'na') === $key ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['logistics'])): ?>
                        <span class="form-error"><?= e($errors['logistics']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contact Method -->
            <div class="form-group <?= isset($errors['contact_method']) ? 'has-error' : '' ?>">
                <label for="contact_method">Preferred Contact Method <span class="optional">(optional)</span></label>
                <input 
                    type="text" 
                    id="contact_method" 
                    name="contact_method" 
                    value="<?= e($oldInput['contact_method'] ?? '') ?>"
                    maxlength="255"
                    placeholder='e.g., "Email preferred" or "Call weekdays 9-5"'
                >
                <?php if (isset($errors['contact_method'])): ?>
                    <span class="form-error"><?= e($errors['contact_method']) ?></span>
                <?php endif; ?>
                <span class="form-hint">Responses will come through the platform messaging system</span>
            </div>
            
            <!-- Causes -->
            <?php if (!empty($causes)): ?>
                <div class="form-group">
                    <label>Related Causes <span class="optional">(optional, max 2)</span></label>
                    <div class="causes-grid">
                        <?php 
                        $selectedCauses = $oldInput['causes'] ?? [];
                        foreach ($causes as $cause): 
                        ?>
                            <label class="cause-checkbox">
                                <input 
                                    type="checkbox" 
                                    name="causes[]" 
                                    value="<?= $cause->id ?>"
                                    <?= in_array($cause->id, $selectedCauses) ? 'checked' : '' ?>
                                >
                                <span><?= e($cause->name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <span class="form-hint">Help people discover your listing by tagging related causes</span>
                </div>
            <?php endif; ?>
            
            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <?= e($submitLabel) ?>
                </button>
                
                <?php if ($isEdit): ?>
                    <a href="<?= listingUrl($listing->id) ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                <?php else: ?>
                    <a href="<?= url('/post') ?>" class="btn btn-secondary">
                        Back
                    </a>
                <?php endif; ?>
            </div>
            
        </form>
        
    </div>
</section>
