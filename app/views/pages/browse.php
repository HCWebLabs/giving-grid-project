<?php
/**
 * Browse Listings Page
 * 
 * Displays filterable grid of needs, offers, and volunteer opportunities.
 */

$currentType = $filters['type'] ?? null;
$typeInfo = $currentType ? getListingType($currentType) : null;
?>

<section class="browse-page">
    <div class="browse-container">
        
        <!-- Page Header -->
        <header class="browse-header">
            <h1>
                <?php if ($typeInfo): ?>
                    <?= e($typeInfo['icon']) ?> <?= e($typeInfo['plural']) ?>
                <?php else: ?>
                    Browse the Grid
                <?php endif; ?>
            </h1>
            <p class="browse-subtitle">
                <?php if ($pagination['totalItems'] > 0): ?>
                    <?= number_format($pagination['totalItems']) ?> 
                    <?= $pagination['totalItems'] === 1 ? 'listing' : 'listings' ?> found
                <?php else: ?>
                    No listings match your filters
                <?php endif; ?>
            </p>
        </header>
        
        <!-- Filters -->
        <form class="browse-filters" method="GET" action="<?= url('/browse') ?>">
            <div class="filters-row">
                <!-- Type Filter -->
                <div class="filter-group">
                    <label for="filter-type">Type</label>
                    <select name="type" id="filter-type">
                        <option value="">All Types</option>
                        <?php foreach (LISTING_TYPES as $key => $type): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>
                            >
                                <?= e($type['icon']) ?> <?= e($type['plural']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="filter-group">
                    <label for="filter-category">Category</label>
                    <select name="category" id="filter-category">
                        <option value="">All Categories</option>
                        <?php foreach (CATEGORIES as $key => $label): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- County Filter -->
                <div class="filter-group">
                    <label for="filter-county">County</label>
                    <select name="county" id="filter-county">
                        <option value="">All Counties</option>
                        <?php foreach (COUNTIES as $key => $label): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($filters['county'] ?? '') === $key ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Urgency Filter -->
                <div class="filter-group">
                    <label for="filter-urgency">Urgency</label>
                    <select name="urgency" id="filter-urgency">
                        <option value="">Any Urgency</option>
                        <?php foreach (URGENCY_LEVELS as $key => $level): ?>
                            <option 
                                value="<?= e($key) ?>"
                                <?= ($filters['urgency'] ?? '') === $key ? 'selected' : '' ?>
                            >
                                <?= e($level['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="filters-row filters-row-secondary">
                <!-- Search -->
                <div class="filter-group filter-search">
                    <label for="filter-q" class="sr-only">Search</label>
                    <input 
                        type="search" 
                        name="q" 
                        id="filter-q"
                        placeholder="Search listings..."
                        value="<?= e($filters['q'] ?? '') ?>"
                    >
                </div>
                
                <!-- Submit -->
                <button type="submit" class="btn btn-primary">
                    Apply Filters
                </button>
                
                <!-- Clear -->
                <?php if (!empty($filters)): ?>
                    <a href="<?= url('/browse') ?>" class="btn btn-tertiary">
                        Clear All
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Active Filters Display -->
        <?php if (!empty($filters)): ?>
            <div class="active-filters">
                <span class="active-filters-label">Filtering by:</span>
                <?php if (!empty($filters['type'])): ?>
                    <span class="filter-tag">
                        <?= e(LISTING_TYPES[$filters['type']]['label'] ?? $filters['type']) ?>
                        <a href="<?= browseUrl(array_diff_key($filters, ['type' => ''])) ?>" aria-label="Remove type filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filters['category'])): ?>
                    <span class="filter-tag">
                        <?= e(CATEGORIES[$filters['category']] ?? $filters['category']) ?>
                        <a href="<?= browseUrl(array_diff_key($filters, ['category' => ''])) ?>" aria-label="Remove category filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filters['county'])): ?>
                    <span class="filter-tag">
                        <?= e(COUNTIES[$filters['county']] ?? $filters['county']) ?>
                        <a href="<?= browseUrl(array_diff_key($filters, ['county' => ''])) ?>" aria-label="Remove county filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filters['urgency'])): ?>
                    <span class="filter-tag">
                        <?= e(URGENCY_LEVELS[$filters['urgency']]['label'] ?? $filters['urgency']) ?> Urgency
                        <a href="<?= browseUrl(array_diff_key($filters, ['urgency' => ''])) ?>" aria-label="Remove urgency filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filters['q'])): ?>
                    <span class="filter-tag">
                        "<?= e($filters['q']) ?>"
                        <a href="<?= browseUrl(array_diff_key($filters, ['q' => ''])) ?>" aria-label="Remove search filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Listings Grid -->
        <?php if (!empty($listings)): ?>
            <div class="listings-grid">
                <?php foreach ($listings as $listing): ?>
                    <?php includePartial('partials/listing-card', ['listing' => $listing]) ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
                <nav class="pagination" aria-label="Listings pagination">
                    <?php if ($pagination['current'] > 1): ?>
                        <a 
                            href="<?= browseUrl(array_merge($filters, ['page' => $pagination['current'] - 1])) ?>"
                            class="pagination-link pagination-prev"
                        >
                            ← Previous
                        </a>
                    <?php endif; ?>
                    
                    <span class="pagination-info">
                        Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?>
                    </span>
                    
                    <?php if ($pagination['current'] < $pagination['total']): ?>
                        <a 
                            href="<?= browseUrl(array_merge($filters, ['page' => $pagination['current'] + 1])) ?>"
                            class="pagination-link pagination-next"
                        >
                            Next →
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2>No listings found</h2>
                <p>
                    <?php if (!empty($filters)): ?>
                        Try adjusting your filters or <a href="<?= url('/browse') ?>">clear all filters</a>.
                    <?php else: ?>
                        Be the first to post to the Grid!
                    <?php endif; ?>
                </p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= url('/post') ?>" class="btn btn-primary">Post to the Grid</a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-primary">Log In to Post</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</section>
