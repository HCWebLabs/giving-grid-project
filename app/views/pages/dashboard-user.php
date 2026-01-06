<?php
/**
 * User Dashboard Page
 * 
 * Basic version - full implementation in Batch 4.
 */

$isOrgMember = ($user['role'] ?? '') === 'org_member';
$hasVerifiedOrg = $isOrgMember && ($user['org_verified'] ?? false);
?>

<section class="dashboard-page">
    <div class="dashboard-container">
        
        <!-- Header -->
        <header class="dashboard-header">
            <h1>Dashboard</h1>
            <p class="dashboard-welcome">
                Welcome back, <?= e($user['display_name']) ?>!
            </p>
        </header>
        
        <!-- Quick Actions -->
        <section class="dashboard-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="<?= url('/post') ?>" class="btn btn-primary">
                    Post to the Grid
                </a>
                <a href="<?= url('/browse') ?>" class="btn btn-secondary">
                    Browse Listings
                </a>
            </div>
        </section>
        
        <!-- User Info -->
        <section class="dashboard-profile">
            <h2>Your Profile</h2>
            <dl class="profile-info">
                <div class="profile-item">
                    <dt>Name</dt>
                    <dd><?= e($user['display_name']) ?></dd>
                </div>
                <div class="profile-item">
                    <dt>Email</dt>
                    <dd><?= e($user['email']) ?></dd>
                </div>
                <div class="profile-item">
                    <dt>Role</dt>
                    <dd>
                        <?php if ($isOrgMember): ?>
                            Organization Member
                            <?php if ($hasVerifiedOrg): ?>
                                <span class="verified-badge">✓ Verified</span>
                            <?php endif; ?>
                        <?php else: ?>
                            Individual
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if ($user['county']): ?>
                    <div class="profile-item">
                        <dt>County</dt>
                        <dd><?= e(getCountyName($user['county'])) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($organization): ?>
                    <div class="profile-item">
                        <dt>Organization</dt>
                        <dd>
                            <a href="<?= orgUrl($organization->id) ?>">
                                <?= e($organization->name) ?>
                            </a>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>
        
        <!-- My Listings -->
        <section class="dashboard-listings">
            <h2>My Listings</h2>
            
            <?php if (!empty($listings)): ?>
                <div class="listings-table-wrapper">
                    <table class="listings-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <?php $typeInfo = $listing->getTypeInfo(); ?>
                                <tr>
                                    <td>
                                        <span class="type-badge type-badge-<?= e($listing->type) ?>">
                                            <?= e($typeInfo['icon']) ?> <?= e($typeInfo['label']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= listingUrl($listing->id) ?>">
                                            <?= e($listing->title) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status-badge status-badge-<?= e($listing->status) ?>">
                                            <?= e(ucfirst(str_replace('_', ' ', $listing->status))) ?>
                                        </span>
                                    </td>
                                    <td><?= e($listing->getTimeAgo()) ?></td>
                                    <td>
                                        <a href="<?= listingUrl($listing->id) ?>" class="btn-small">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state empty-state-small">
                    <p>You haven't posted any listings yet.</p>
                    <a href="<?= url('/post') ?>" class="btn btn-primary">Create Your First Listing</a>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Organization Section (if applicable) -->
        <?php if ($isOrgMember && !$hasVerifiedOrg): ?>
            <section class="dashboard-org-pending">
                <h2>Organization Status</h2>
                <div class="alert alert-warning">
                    <p>
                        <strong>Verification Pending:</strong> 
                        Your organization is awaiting verification. Once verified, you'll be able to post 
                        needs and volunteer opportunities.
                    </p>
                </div>
            </section>
        <?php endif; ?>
        
    </div>
</section>
