<?php
/**
 * View Helper
 * 
 * Functions for rendering views and templates.
 */

declare(strict_types=1);

/**
 * Render a view template with data
 * 
 * @param string $view Path to view relative to Views folder (e.g., 'pages/home')
 * @param array $data Variables to extract into view scope
 * @param string|null $layout Layout to wrap the view (null for no layout)
 * @return string Rendered HTML
 */
function view(string $view, array $data = [], ?string $layout = 'main'): string
{
    // Extract data to local variables
    extract($data);
    
    // Build path to view file
    $viewPath = APP_PATH . '/Views/' . $view . '.php';
    
    if (!file_exists($viewPath)) {
        throw new RuntimeException("View not found: {$view}");
    }
    
    // Capture view output
    ob_start();
    require $viewPath;
    $content = ob_get_clean();
    
    // Wrap in layout if specified
    if ($layout !== null) {
        $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new RuntimeException("Layout not found: {$layout}");
        }
        
        // Make $content available to layout
        ob_start();
        require $layoutPath;
        $content = ob_get_clean();
    }
    
    return $content;
}

/**
 * Render a view and send it to the browser
 * 
 * @param string $view Path to view relative to Views folder
 * @param array $data Variables to extract into view scope
 * @param string|null $layout Layout to wrap the view
 */
function render(string $view, array $data = [], ?string $layout = 'main'): void
{
    echo view($view, $data, $layout);
}

/**
 * Render a partial template (no layout)
 * 
 * @param string $partial Path to partial relative to Views folder
 * @param array $data Variables to extract into partial scope
 * @return string Rendered HTML
 */
function partial(string $partial, array $data = []): string
{
    return view($partial, $data, null);
}

/**
 * Include a partial template directly (for use within views)
 * 
 * @param string $partial Path to partial relative to Views folder
 * @param array $data Variables to extract into partial scope
 */
function includePartial(string $partial, array $data = []): void
{
    echo partial($partial, $data);
}

/**
 * Escape HTML entities for safe output
 * 
 * @param string|null $value Value to escape
 * @return string Escaped value
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Escape and echo a value
 * 
 * @param string|null $value Value to escape and output
 */
function ee(?string $value): void
{
    echo e($value);
}
