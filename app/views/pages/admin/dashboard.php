<?php
/**
 * Admin Dashboard
 */
?>

<section class="admin-page">
    <div class="admin-container">
        
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Platform overview and quick actions</p>
        </header>
        
        <!-- Stats Grid -->
        <section class="admin-stats">
            <div class="stats-grid stats-grid-4">
                <div class="stat-card stat-card-alert">
                    <span class="stat-number"><?= $stats['pending_verifications'] ?></span>
                    <span class="stat-label">Pending Verifications</span>
                    <?php if ($stats['pending_verifications'] > 0): ?>
                        <a href="<?= url('/admin/verify') ?>" class="stat-action">Review →</a>
                    <?php endif; ?>
                </div>
                <div class="stat-card stat-card-alert">
                    <span class="stat-number"><?= $stats['pending_reports'] ?></span>
                    <span class="stat-label">Pending Reports</span>
                    <?php if ($stats['pending_reports'] > 0): ?>
                        <a href="<?= url('/admin/reports') ?>" class="stat-action">Review →</a>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $stats['active_listings'] ?></span>
                    <span class="stat-label">Active Listings</span>
                </div>
                <div class="stat-card stat-card-success">
                    <span class="stat-number"><?= $stats['completed_week'] ?></span>
                    <span class="stat-label">Fulfilled This Week</span>
                </div>
            </div>
        </section>
        
        <!-- Secondary Stats -->
        <section class="admin-stats-secondary">
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['total_users'] ?></span>
                    <span class="stat-label">Total Users</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['verified_orgs'] ?>/<?= $stats['total_orgs'] ?></span>
                    <span class="stat-label">Verified Orgs</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">+<?= $stats['new_users_week'] ?></span>
                    <span class="stat-label">New Users (7d)</span>
                </div>
            </div>
        </section>
        
        <div class="admin-grid">
            
            <!-- Pending Verifications -->
            <section class="admin-panel">
                <div class="panel-header">
                    <h2>Pending Verifications</h2>
                    <a href="<?= url('/admin/verify') ?>">View All</a>
                </div>
                
                <?php if (empty($pendingVerifications)): ?>
                    <p class="panel-empty">No pending verifications</p>
                <?php else: ?>
                    <ul class="admin-list">
                        <?php foreach ($pendingVerifications as $org): ?>
                            <li class="admin-list-item">
                                <div class="list-item-main">
                                    <strong><?= e($org->name) ?></strong>
                                    <span class="list-item-meta"><?= e($org->getCountyName()) ?></span>
                                </div>
                                <a href="<?= url('/admin/verify') ?>" class="btn-small">Review</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
            
            <!-- Pending Reports -->
            <section class="admin-panel">
                <div class="panel-header">
                    <h2>Pending Reports</h2>
                    <a href="<?= url('/admin/reports') ?>">View All</a>
                </div>
                
                <?php if (empty($pendingReports)): ?>
                    <p class="panel-empty">No pending reports</p>
                <?php else: ?>
                    <ul class="admin-list">
                        <?php foreach ($pendingReports as $report): ?>
                            <li class="admin-list-item">
                                <div class="list-item-main">
                                    <strong><?= e($report->getReasonLabel()) ?></strong>
                                    <span class="list-item-meta">
                                        <?= e(ucfirst($report->type)) ?>: <?= e($report->target_title ?? "#{$report->target_id}") ?>
                                    </span>
                                </div>
                                <span class="list-item-time"><?= e($report->getTimeAgo()) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
            
        </div>
        
        <!-- Quick Links -->
        <section class="admin-quick-links">
            <h2>Quick Links</h2>
            <div class="quick-links-grid">
                <a href="<?= url('/admin/verify') ?>" class="quick-link">
                    <span class="quick-link-icon">✓</span>
                    <span>Verify Organizations</span>
                </a>
                <a href="<?= url('/admin/reports') ?>" class="quick-link">
                    <span class="quick-link-icon">🚩</span>
                    <span>Review Reports</span>
                </a>
                <a href="<?= url('/browse') ?>" class="quick-link">
                    <span class="quick-link-icon">📋</span>
                    <span>Browse Listings</span>
                </a>
                <a href="<?= url('/organizations') ?>" class="quick-link">
                    <span class="quick-link-icon">🏢</span>
                    <span>Organizations</span>
                </a>
            </div>
        </section>
        
    </div>
</section>
