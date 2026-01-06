<?php
/**
 * Flash Message Helper
 * 
 * Functions for setting and displaying one-time session messages.
 */

declare(strict_types=1);

/**
 * Set a flash message
 * 
 * @param string $type Message type: success, error, warning, info
 * @param string $message The message to display
 */
function flash(string $type, string $message): void
{
    $_SESSION["flash_{$type}"] = $message;
}

/**
 * Set a success flash message
 */
function flashSuccess(string $message): void
{
    flash('success', $message);
}

/**
 * Set an error flash message
 */
function flashError(string $message): void
{
    flash('error', $message);
}

/**
 * Set a warning flash message
 */
function flashWarning(string $message): void
{
    flash('warning', $message);
}

/**
 * Set an info flash message
 */
function flashInfo(string $message): void
{
    flash('info', $message);
}

/**
 * Check if there are any flash messages
 */
function hasFlash(): bool
{
    return !empty($_SESSION['flash_success'])
        || !empty($_SESSION['flash_error'])
        || !empty($_SESSION['flash_warning'])
        || !empty($_SESSION['flash_info']);
}

/**
 * Get and clear a flash message
 * 
 * @param string $type Message type
 * @return string|null The message or null
 */
function getFlash(string $type): ?string
{
    $key = "flash_{$type}";
    $message = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $message;
}
