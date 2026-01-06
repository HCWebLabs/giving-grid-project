<?php
/**
 * Responses List (Inbox)
 */
?>

<section class="responses-list-page">
    <div class="responses-list-container">
        
        <header class="responses-header">
            <h1>Messages</h1>
            
            <!-- View Toggle -->
            <div class="responses-tabs">
                <a 
                    href="<?= url('/responses?view=received') ?>" 
                    class="tab <?= $currentView === 'received' ? 'tab-active' : '' ?>"
                >
                    Received
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge badge-count"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                <a 
                    href="<?= url('/responses?view=sent') ?>" 
                    class="tab <?= $currentView === 'sent' ? 'tab-active' : '' ?>"
                >
                    Sent
                </a>
            </div>
        </header>
        
        <?php if (empty($responses)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📬</div>
                <h2>
                    <?php if ($currentView === 'sent'): ?>
                        No responses sent yet
                    <?php else: ?>
                        No responses received yet
                    <?php endif; ?>
                </h2>
                <p>
                    <?php if ($currentView === 'sent'): ?>
                        When you respond to listings, they'll appear here.
                    <?php else: ?>
                        When someone responds to your listings, they'll appear here.
                    <?php endif; ?>
                </p>
                <a href="<?= url('/browse') ?>" class="btn btn-primary">Browse Listings</a>
            </div>
        <?php else: ?>
            <div class="responses-list">
                <?php foreach ($responses as $response): ?>
                    <?php 
                    $statusInfo = $response->getStatusInfo();
                    $typeInfo = getListingType($response->listing_type);
                    $hasUnread = isset($response->unread_count) && $response->unread_count > 0;
                    ?>
                    <a href="<?= url("/responses/{$response->id}") ?>" class="response-item <?= $hasUnread ? 'response-item-unread' : '' ?>">
                        <div class="response-item-main">
                            <div class="response-item-header">
                                <span class="type-badge type-badge-<?= e($response->listing_type) ?> type-badge-small">
                                    <?= e($typeInfo['icon'] ?? '') ?>
                                </span>
                                <span class="response-item-title"><?= e($response->listing_title) ?></span>
                                <span class="status-badge status-badge-<?= e($response->status) ?> status-badge-small">
                                    <?= e($statusInfo['label']) ?>
                                </span>
                            </div>
                            <div class="response-item-meta">
                                <?php if ($currentView === 'sent'): ?>
                                    <span>To: <?= e($response->listing_poster_name) ?></span>
                                <?php else: ?>
                                    <span>From: <?= e($response->responder_name) ?></span>
                                <?php endif; ?>
                                <span class="response-item-time"><?= e($response->getTimeAgo()) ?></span>
                            </div>
                        </div>
                        <div class="response-item-indicators">
                            <?php if ($response->message_count > 1): ?>
                                <span class="message-count" title="<?= $response->message_count ?> messages">
                                    💬 <?= $response->message_count ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($hasUnread): ?>
                                <span class="unread-badge" title="<?= $response->unread_count ?> unread">
                                    <?= $response->unread_count ?>
                                </span>
                            <?php endif; ?>
                            <span class="response-arrow">→</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
</section>
