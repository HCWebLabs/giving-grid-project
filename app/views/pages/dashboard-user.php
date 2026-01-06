<?php
/**
 * User Dashboard Page
 */

$isOrgMember = ($user['role'] ?? '') === 'org_member';
$hasVerifiedOrg = $isOrgMember && ($user['org_verified'] ?? false);
?>

<section class="dashboard-page">
    <div class="dashboard-container">
        
        <!-- Header -->
        <header class="dashboard-header">
            <div class="dashboard-header-main">
                <h1>Dashboard</h1>
                <p class="dashboard-welcome">
                    Welcome back, <?= e($user['display_name']) ?>!
                </p>
            </div>
            <div class="dashboard-header-actions">
                <a href="<?= url('/post') ?>" class="btn btn-primary">
                    + Post to the Grid
                </a>
            </div>
        </header>
        
        <!-- Stats Overview -->
        <section class="dashboard-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?= $stats['active'] ?></span>
                    <span class="stat-label">Active Listings</span>
                </div>
                <div class="stat-card stat-card-success">
                    <span class="stat-number"><?= $stats['fulfilled'] ?></span>
                    <span class="stat-label">Fulfilled</span>
                </div>
                <div class="stat-card stat-card-muted">
                    <span class="stat-number"><?= $stats['total'] ?></span>
                    <span class="stat-label">Total Posted</span>
                </div>
            </div>
        </section>
        
        <!-- Organization Status (if applicable) -->
        <?php if ($isOrgMember && !$hasVerifiedOrg): ?>
            <section class="dashboard-alert">
                <div class="alert alert-warning">
                    <strong>Verification Pending:</strong> 
                    Your organization is awaiting verification. Once verified, you'll be able to post 
                    needs and volunteer opportunities on behalf of <?= e($organization->name ?? 'your organization') ?>.
                </div>
            </section>
        <?php endif; ?>
        
        <?php if ($hasVerifiedOrg && $organization): ?>
            <section class="dashboard-org-summary">
                <div class="org-summary-card">
                    <div class="org-summary-header">
                        <h2>
                            <?= e($organization->name) ?>
                            <span class="verified-badge">✓ Verified</span>
                        </h2>
                        <a href="<?= orgUrl($organization->id) ?>" class="btn btn-small btn-secondary">View Profile</a>
                    </div>
                    <p class="org-summary-stats">
                        <span><?= $organization->active_needs_count ?? 0 ?> active needs</span>
                        <span><?= $organization->active_offers_count ?? 0 ?> offers</span>
                        <span><?= $organization->volunteer_count ?? 0 ?> volunteer opps</span>
                    </p>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- My Active Listings -->
        <section class="dashboard-listings">
            <div class="section-header">
                <h2>My Active Listings</h2>
            </div>
            
            <?php if (!empty($listings)): ?>
                <div class="listings-table-wrapper">
                    <table class="listings-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Posted</th>
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
                                    <td>
                                        <span class="urgency-badge urgency-badge-<?= e($listing->urgency) ?>">
                                            <?= e(ucfirst($listing->urgency)) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= e($listing->getTimeAgo()) ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?= listingUrl($listing->id) ?>" class="btn-small">View</a>
                                            <a href="<?= url("/listing/{$listing->id}/edit") ?>" class="btn-small">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state empty-state-small">
                    <p>You don't have any active listings right now.</p>
                    <a href="<?= url('/post') ?>" class="btn btn-primary">Create Your First Listing</a>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- User Profile Summary -->
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
                    <dt>Account Type</dt>
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
            </dl>
        </section>
        
    </div>
</section>
