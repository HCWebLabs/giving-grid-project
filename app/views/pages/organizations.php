<?php
/**
 * Organizations Directory Page
 * 
 * Displays verified organizations with filtering.
 * Expected variables: $organizations, $filters, $pagination
 */
?>

<section class="organizations-page">
    <div class="organizations-container">
        
        <!-- Page Header -->
        <header class="organizations-header">
            <h1>Organizations</h1>
            <p class="organizations-subtitle">
                Verified nonprofits and community groups serving East Tennessee.
                <?php if ($pagination['totalItems'] > 0): ?>
                    <strong><?= number_format($pagination['totalItems']) ?></strong> organizations.
                <?php endif; ?>
            </p>
        </header>
        
        <!-- Filters -->
        <form class="organizations-filters" method="GET" action="<?= url('/organizations') ?>">
            <div class="filters-row">
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
                
                <!-- Search -->
                <div class="filter-group filter-search">
                    <label for="filter-q" class="sr-only">Search</label>
                    <input 
                        type="search" 
                        name="q" 
                        id="filter-q"
                        placeholder="Search organizations..."
                        value="<?= e($filters['q'] ?? '') ?>"
                    >
                </div>
                
                <!-- Submit -->
                <button type="submit" class="btn btn-primary">Search</button>
                
                <!-- Clear -->
                <?php if (!empty($filters)): ?>
                    <a href="<?= url('/organizations') ?>" class="btn btn-tertiary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Organizations Grid -->
        <?php if (!empty($organizations)): ?>
            <div class="organizations-grid">
                <?php foreach ($organizations as $org): ?>
                    <?php includePartial('partials/org-card', ['organization' => $org]) ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
                <nav class="pagination" aria-label="Organizations pagination">
                    <?php 
                    $paginationFilters = $filters;
                    ?>
                    
                    <?php if ($pagination['current'] > 1): ?>
                        <a 
                            href="<?= url('/organizations?' . http_build_query(array_merge($paginationFilters, ['page' => $pagination['current'] - 1]))) ?>"
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
                            href="<?= url('/organizations?' . http_build_query(array_merge($paginationFilters, ['page' => $pagination['current'] + 1]))) ?>"
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
                <div class="empty-state-icon">🏢</div>
                <h2>No organizations found</h2>
                <p>
                    <?php if (!empty($filters)): ?>
                        Try adjusting your search or <a href="<?= url('/organizations') ?>">view all organizations</a>.
                    <?php else: ?>
                        Organizations will appear here once they're verified.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
    </div>
</section>
