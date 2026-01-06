<?php
/**
 * Front Controller
 * 
 * All requests route through this file.
 * Handles routing, middleware, and dispatching to controllers.
 */

declare(strict_types=1);

// ─────────────────────────────────────────────────────────────────────────────
// Bootstrap
// ─────────────────────────────────────────────────────────────────────────────

// Load configuration
require_once dirname(__DIR__) . '/config/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/constants.php';

// Load helpers
require_once APP_PATH . '/Helpers/view.php';
require_once APP_PATH . '/Helpers/url.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ─────────────────────────────────────────────────────────────────────────────
// Routing
// ─────────────────────────────────────────────────────────────────────────────

// Load routes
$routes = require CONFIG_PATH . '/routes.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash (except for root)
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

/**
 * Match a route pattern against the current URI
 * 
 * @param string $pattern Route pattern (e.g., '/listing/{id}')
 * @param string $uri Current URI
 * @return array|null Matched parameters or null
 */
function matchRoute(string $pattern, string $uri): ?array
{
    // Convert route pattern to regex
    // {param} becomes a named capture group
    $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    
    if (preg_match($regex, $uri, $matches)) {
        // Filter out numeric keys, keep only named parameters
        return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
    }
    
    return null;
}

/**
 * Run middleware checks
 * 
 * @param array $middleware List of middleware names
 * @return bool True if all middleware pass
 */
function runMiddleware(array $middleware): bool
{
    foreach ($middleware as $name) {
        switch ($name) {
            case 'auth':
                if (empty($_SESSION['user_id'])) {
                    $_SESSION['flash_error'] = 'Please log in to continue.';
                    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
                    redirectTo('/login');
                }
                break;
                
            case 'admin':
                if (($_SESSION['user']['role'] ?? '') !== 'admin') {
                    http_response_code(403);
                    render('pages/errors/403', ['pageTitle' => 'Access Denied']);
                    return false;
                }
                break;
                
            case 'csrf':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                        http_response_code(403);
                        $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
                        redirectBack();
                    }
                }
                break;
                
            case 'guest':
                if (!empty($_SESSION['user_id'])) {
                    redirectTo('/dashboard');
                }
                break;
        }
    }
    
    return true;
}

// ─────────────────────────────────────────────────────────────────────────────
// Dispatch
// ─────────────────────────────────────────────────────────────────────────────

$matched = false;

foreach ($routes as $routeKey => $routeConfig) {
    // Parse route key: "METHOD /path"
    [$routeMethod, $routePattern] = explode(' ', $routeKey, 2);
    
    // Check method match
    if ($routeMethod !== $method) {
        continue;
    }
    
    // Check pattern match
    $params = matchRoute($routePattern, $uri);
    
    if ($params !== null) {
        $matched = true;
        
        // Extract controller, method, and middleware from config
        $controllerName = $routeConfig[0];
        $actionName = $routeConfig[1];
        $middleware = array_slice($routeConfig, 2);
        
        // Run middleware
        if (!runMiddleware($middleware)) {
            exit;
        }
        
        // Build controller class name
        $controllerClass = "App\\Controllers\\{$controllerName}";
        $controllerFile = APP_PATH . "/Controllers/{$controllerName}.php";
        
        // Load and instantiate controller
        if (!file_exists($controllerFile)) {
            throw new RuntimeException("Controller not found: {$controllerName}");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller class not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $actionName)) {
            throw new RuntimeException("Action not found: {$controllerName}@{$actionName}");
        }
        
        // Call the action with route parameters
        $controller->$actionName(...array_values($params));
        
        break;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 404 Handler
// ─────────────────────────────────────────────────────────────────────────────

if (!$matched) {
    http_response_code(404);
    render('pages/errors/404', ['pageTitle' => 'Page Not Found']);
}
