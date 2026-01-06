<?php
/**
 * Flash Message Partial
 * 
 * Displays session flash messages (success, error, warning, info).
 * Messages are cleared after being displayed.
 */

$flashTypes = ['success', 'error', 'warning', 'info'];
$hasFlash = false;

foreach ($flashTypes as $type) {
    if (!empty($_SESSION["flash_{$type}"])) {
        $hasFlash = true;
        break;
    }
}

if (!$hasFlash) {
    return;
}
?>

<div class="flash-container" role="alert" aria-live="polite">
    <?php foreach ($flashTypes as $type): ?>
        <?php if (!empty($_SESSION["flash_{$type}"])): ?>
            <?php 
                $message = $_SESSION["flash_{$type}"];
                unset($_SESSION["flash_{$type}"]);
                
                $icons = [
                    'success' => '✓',
                    'error' => '✕',
                    'warning' => '⚠',
                    'info' => 'ℹ'
                ];
            ?>
            <div class="flash flash-<?= $type ?>">
                <span class="flash-icon" aria-hidden="true"><?= $icons[$type] ?></span>
                <span class="flash-message"><?= e($message) ?></span>
                <button 
                    type="button" 
                    class="flash-close" 
                    aria-label="Dismiss message"
                    onclick="this.parentElement.remove()"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
