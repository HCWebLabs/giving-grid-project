<?php
/**
 * URL Helper
 * 
 * Functions for generating URLs, routes, and asset paths.
 */

declare(strict_types=1);

/**
 * Generate a full URL for a path
 * 
 * @param string $path Path relative to app root (e.g., '/browse')
 * @return string Full URL
 */
function url(string $path = ''): string
{
    $base = rtrim(APP_URL, '/');
    $path = '/' . ltrim($path, '/');
    
    return $base . $path;
}

/**
 * Generate URL to an asset file
 * 
 * @param string $path Path relative to assets folder (e.g., 'css/style.css')
 * @return string Full URL to asset
 */
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

/**
 * Generate URL for a listing detail page
 * 
 * @param int $id Listing ID
 * @return string URL to listing
 */
function listingUrl(int $id): string
{
    return url("/listing/{$id}");
}

/**
 * Generate URL for an organization profile page
 * 
 * @param int $id Organization ID
 * @return string URL to organization
 */
function orgUrl(int $id): string
{
    return url("/organization/{$id}");
}

/**
 * Generate URL for browse page with optional filters
 * 
 * @param array $filters Filter parameters
 * @return string URL to browse with query string
 */
function browseUrl(array $filters = []): string
{
    $base = url('/browse');
    
    if (empty($filters)) {
        return $base;
    }
    
    // Remove empty values
    $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
    
    if (empty($filters)) {
        return $base;
    }
    
    return $base . '?' . http_build_query($filters);
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param int $status HTTP status code (default 302)
 */
function redirect(string $url, int $status = 302): never
{
    header("Location: {$url}", true, $status);
    exit;
}

/**
 * Redirect to a path within the app
 * 
 * @param string $path Path relative to app root
 * @param int $status HTTP status code (default 302)
 */
function redirectTo(string $path, int $status = 302): never
{
    redirect(url($path), $status);
}

/**
 * Redirect back to the previous page
 * 
 * @param string $fallback Fallback path if no referrer
 */
function redirectBack(string $fallback = '/'): never
{
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    
    if ($referrer && str_starts_with($referrer, APP_URL)) {
        redirect($referrer);
    }
    
    redirectTo($fallback);
}

/**
 * Check if the current URL matches a path
 * 
 * @param string $path Path to check against
 * @return bool
 */
function isCurrentPath(string $path): bool
{
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $current === $path;
}

/**
 * Check if the current URL starts with a path
 * 
 * @param string $path Path prefix to check
 * @return bool
 */
function isCurrentPathPrefix(string $path): bool
{
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return str_starts_with($current, $path);
}

/**
 * Get the current URL with query string
 * 
 * @return string
 */
function currentUrl(): string
{
    return url($_SERVER['REQUEST_URI']);
}

/**
 * Get a query parameter from the current request
 * 
 * @param string $key Parameter name
 * @param mixed $default Default value if not present
 * @return mixed
 */
function queryParam(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}
