<?php
/**
 * Organization Verification Queue
 */
?>

<section class="admin-page">
    <div class="admin-container">
        
        <header class="admin-header">
            <div class="admin-header-main">
                <a href="<?= url('/admin') ?>" class="admin-back">← Admin Dashboard</a>
                <h1>Organization Verification</h1>
            </div>
        </header>
        
        <!-- Pending Queue -->
        <section class="admin-section">
            <h2>Pending Verification (<?= count($pendingOrgs) ?>)</h2>
            
            <?php if (empty($pendingOrgs)): ?>
                <div class="empty-state empty-state-small">
                    <p>No organizations waiting for verification.</p>
                </div>
            <?php else: ?>
                <div class="verification-queue">
                    <?php foreach ($pendingOrgs as $org): ?>
                        <div class="verification-card">
                            <div class="verification-card-header">
                                <h3><?= e($org->name) ?></h3>
                                <span class="verification-county"><?= e($org->getCountyName()) ?></span>
                            </div>
                            
                            <?php if ($org->mission): ?>
                                <p class="verification-mission"><?= e($org->mission) ?></p>
                            <?php endif; ?>
                            
                            <dl class="verification-details">
                                <div>
                                    <dt>Contact Email</dt>
                                    <dd>
                                        <a href="mailto:<?= e($org->contact_email) ?>">
                                            <?= e($org->contact_email) ?>
                                        </a>
                                    </dd>
                                </div>
                                <?php if ($org->contact_phone): ?>
                                    <div>
                                        <dt>Phone</dt>
                                        <dd><?= e($org->contact_phone) ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($org->hasWebsite()): ?>
                                    <div>
                                        <dt>Website</dt>
                                        <dd>
                                            <a href="<?= e($org->getWebsiteUrl()) ?>" target="_blank" rel="noopener">
                                                <?= e($org->getWebsiteDisplay()) ?>
                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($org->address): ?>
                                    <div>
                                        <dt>Address</dt>
                                        <dd><?= e($org->address) ?></dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                            
                            <div class="verification-actions">
                                <form method="POST" action="<?= url("/admin/verify/{$org->id}") ?>" class="inline-form">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-success">
                                        ✓ Verify
                                    </button>
                                </form>
                                <form method="POST" action="<?= url("/admin/reject/{$org->id}") ?>" class="inline-form" 
                                      onsubmit="return confirm('Are you sure you want to reject this organization? This will remove it and notify any associated users.')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-danger">
                                        ✗ Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Recently Verified -->
        <section class="admin-section">
            <h2>Recently Verified</h2>
            
            <?php if (empty($verifiedOrgs)): ?>
                <p class="text-muted">No verified organizations yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>County</th>
                            <th>Verified</th>
                            <th>Listings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($verifiedOrgs as $org): ?>
                            <tr>
                                <td>
                                    <a href="<?= orgUrl($org->id) ?>"><?= e($org->name) ?></a>
                                </td>
                                <td><?= e($org->getCountyName()) ?></td>
                                <td>
                                    <?= $org->verified_at ? date('M j, Y', strtotime($org->verified_at)) : 'N/A' ?>
                                </td>
                                <td><?= $org->listing_count ?? 0 ?></td>
                                <td>
                                    <a href="<?= orgUrl($org->id) ?>" class="btn-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        
    </div>
</section>
