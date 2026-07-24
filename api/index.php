<?php
/**
 * API entry point.
 * Routes /api/<resource>[/id] to the matching endpoint file.
 *
 * Public:    GET /api/<resource> (read), POST /api/auth/login
 * Protected: writes (POST/PUT/DELETE) and /api/users require a bearer token.
 *
 * Examples:
 *   GET    /api/vehicles
 *   POST   /api/auth/login        { "email", "password" }
 *   POST   /api/invoices          (JSON body + Authorization: Bearer <token>)
 */

require_once __DIR__ . '/../config.php';
handle_cors();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Strip the leading "api/" segment (works both when hit directly
// as /api/index.php and when rewritten by the dev-server router).
if (strpos($path, 'api/') === 0) {
    $path = substr($path, strlen('api/'));
}
$path = trim($path, '/');
$segments = explode('/', $path);

$resource = $segments[0] ?? '';

if ($resource === '' || $resource === 'index.php') {
    json_response([
        'app'     => APP_NAME,
        'version' => APP_VERSION,
        'resources' => [
            'vehicles', 'trailers', 'drivers', 'trips',
            'customers', 'suppliers', 'inventory', 'purchase-orders',
            'expenses', 'invoices', 'payments', 'users', 'activities',
        ],
    ]);
}

// Authentication endpoint is always public.
if ($resource === 'auth') {
    $file = __DIR__ . '/auth.php';
    if (!file_exists($file)) {
        json_error('Unknown resource: ' . $resource, 404);
    }
    require_once $file;
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Enforce auth for any non-read request, and for the users resource.
$requiresAuth = $method !== 'GET' || $resource === 'users';
if ($requiresAuth) {
    $user = current_user();
    if ($user === null) {
        json_error('Authentication required', 401);
    }
    define('AUTH_USER_ID', (int) $user['id']);
    define('AUTH_USER_ROLE', (string) ($user['role'] ?? 'Guest'));

    if (in_array($resource, ['users', 'activities'], true)) {
        $role = strtolower((string) AUTH_USER_ROLE);
        if ($role !== 'administrator' && $role !== 'admin') {
            json_error('Forbidden', 403);
        }
    }
}

$file = __DIR__ . '/' . $resource . '.php';
if (!file_exists($file)) {
    json_error('Unknown resource: ' . $resource, 404);
}

require_once $file;
