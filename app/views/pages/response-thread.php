<?php
/**
 * Response Thread / Conversation View
 */

$statusInfo = $response->getStatusInfo();
$typeInfo = getListingType($response->listing_type);

// Determine the other party
$otherPartyName = $isListingOwner ? $response->responder_name : $response->listing_poster_name;
?>

<section class="response-thread-page">
    <div class="response-thread-container">
        
        <!-- Header -->
        <header class="thread-header">
            <div class="thread-header-main">
                <a href="<?= url('/responses') ?>" class="thread-back">← Back to Messages</a>
                <h1>Conversation with <?= e($otherPartyName) ?></h1>
            </div>
            <span class="status-badge status-badge-<?= e($response->status) ?>">
                <?= e($statusInfo['label']) ?>
            </span>
        </header>
        
        <!-- Listing Context -->
        <div class="thread-listing-context">
            <span class="type-badge type-badge-<?= e($response->listing_type) ?>">
                <?= e($typeInfo['icon'] ?? '') ?> <?= e($typeInfo['label'] ?? ucfirst($response->listing_type)) ?>
            </span>
            <a href="<?= listingUrl($response->listing_id) ?>" class="thread-listing-title">
                <?= e($response->listing_title) ?>
            </a>
        </div>
        
        <!-- Status Actions (for listing owner) -->
        <?php if ($isListingOwner && $response->isActive()): ?>
            <div class="thread-actions">
                <?php if ($response->isPending()): ?>
                    <form method="POST" action="<?= url("/responses/{$response->id}/status") ?>" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-success btn-small">
                            ✓ Accept
                        </button>
                    </form>
                    <form method="POST" action="<?= url("/responses/{$response->id}/status") ?>" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="btn btn-danger btn-small">
                            ✗ Decline
                        </button>
                    </form>
                <?php elseif ($response->isAccepted()): ?>
                    <form method="POST" action="<?= url("/responses/{$response->id}/status") ?>" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success btn-small">
                            ✓ Mark Completed
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Messages -->
        <div class="thread-messages">
            <?php if (empty($messages)): ?>
                <div class="empty-state empty-state-small">
                    <p>No messages yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= $message->is_own_message ? 'message-own' : 'message-other' ?>">
                        <div class="message-header">
                            <span class="message-sender"><?= e($message->sender_name) ?></span>
                            <span class="message-time"><?= e($message->getFormattedTime()) ?></span>
                        </div>
                        <div class="message-content">
                            <?= nl2br(e($message->content)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Reply Form -->
        <?php if ($response->isActive()): ?>
            <form method="POST" action="<?= url("/responses/{$response->id}/message") ?>" class="thread-reply-form">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="content" class="sr-only">Your message</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        required
                        maxlength="2000"
                        rows="3"
                        placeholder="Type your message..."
                    ></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Send Message
                </button>
            </form>
        <?php else: ?>
            <div class="thread-closed-notice">
                <p>
                    <?php if ($response->status === 'completed'): ?>
                        This exchange has been completed. Thank you for using The Giving Grid!
                    <?php elseif ($response->status === 'declined'): ?>
                        This response was declined.
                    <?php else: ?>
                        This conversation is closed.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
    </div>
</section>
