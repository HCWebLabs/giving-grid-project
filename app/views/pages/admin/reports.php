<?php
/**
 * Reports Queue
 */
?>

<section class="admin-page">
    <div class="admin-container">
        
        <header class="admin-header">
            <div class="admin-header-main">
                <a href="<?= url('/admin') ?>" class="admin-back">← Admin Dashboard</a>
                <h1>Reports</h1>
            </div>
        </header>
        
        <!-- Status Tabs -->
        <div class="admin-tabs">
            <a href="<?= url('/admin/reports') ?>" 
               class="tab <?= !$currentStatus ? 'tab-active' : '' ?>">
                Pending
                <?php if ($counts['pending'] + $counts['reviewed'] > 0): ?>
                    <span class="badge-count"><?= $counts['pending'] + $counts['reviewed'] ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('/admin/reports?status=resolved') ?>" 
               class="tab <?= $currentStatus === 'resolved' ? 'tab-active' : '' ?>">
                Resolved
            </a>
            <a href="<?= url('/admin/reports?status=dismissed') ?>" 
               class="tab <?= $currentStatus === 'dismissed' ? 'tab-active' : '' ?>">
                Dismissed
            </a>
        </div>
        
        <!-- Reports List -->
        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <h2>
                    <?php if ($currentStatus): ?>
                        No <?= e($currentStatus) ?> reports
                    <?php else: ?>
                        No pending reports
                    <?php endif; ?>
                </h2>
                <p>All clear for now.</p>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <?php $statusInfo = $report->getStatusInfo(); ?>
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-type">
                                <span class="report-type-badge report-type-<?= e($report->type) ?>">
                                    <?= e(ucfirst($report->type)) ?>
                                </span>
                                <span class="report-reason"><?= e($report->getReasonLabel()) ?></span>
                            </div>
                            <span class="status-badge status-badge-<?= e($report->status) ?>">
                                <?= e($statusInfo['label']) ?>
                            </span>
                        </div>
                        
                        <div class="report-card-target">
                            <strong>Target:</strong>
                            <?php if ($report->type === 'listing' && $report->target_title): ?>
                                <a href="<?= listingUrl($report->target_id) ?>">
                                    <?= e($report->target_title) ?>
                                </a>
                            <?php else: ?>
                                #<?= $report->target_id ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($report->details): ?>
                            <div class="report-card-details">
                                <?= e($report->details) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="report-card-meta">
                            <span>
                                Reported <?= e($report->getTimeAgo()) ?>
                                <?php if ($report->reporter_name): ?>
                                    by <?= e($report->reporter_name) ?>
                                <?php else: ?>
                                    (anonymous)
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($report->isActionable()): ?>
                            <div class="report-card-actions">
                                <?php if ($report->type === 'listing'): ?>
                                    <form method="POST" action="<?= url("/admin/reports/{$report->id}/resolve") ?>" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="close_listing">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            Close Listing
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" action="<?= url("/admin/reports/{$report->id}/resolve") ?>" class="inline-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="dismiss">
                                    <button type="submit" class="btn btn-secondary btn-small">
                                        Dismiss
                                    </button>
                                </form>
                                
                                <?php if ($report->type === 'listing'): ?>
                                    <a href="<?= listingUrl($report->target_id) ?>" class="btn btn-small" target="_blank">
                                        View Listing
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="report-card-resolution">
                                <strong>Resolved:</strong> 
                                <?= $report->resolver_name ? e($report->resolver_name) : 'Admin' ?>
                                on <?= date('M j, Y', strtotime($report->resolved_at ?? $report->created_at)) ?>
                                <?php if ($report->admin_notes): ?>
                                    <p class="resolution-notes"><?= e($report->admin_notes) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
</section>
