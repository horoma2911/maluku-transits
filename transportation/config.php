<?php
/**
 * Application configuration and shared helpers.
 * Values can be overridden via environment variables (see Railway).
 */

define('APP_NAME', 'MALUKU LOGISTICS');
define('APP_VERSION', '1.0');

define('APP_SECRET', getenv('APP_SECRET') ?: 'kasuku-dev-secret');

if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    define('DB_DRIVER', 'pgsql');
    define('DB_HOST', $url['host'] ?? '127.0.0.1');
    define('DB_PORT', $url['port'] ?? '5432');
    define('DB_NAME', ltrim($url['path'] ?? 'kasuku_tgs', '/'));
    define('DB_USER', $url['user'] ?? 'postgres');
    define('DB_PASS', $url['pass'] ?? '');
} elseif (getenv('DB_DRIVER')) {
    define('DB_DRIVER', getenv('DB_DRIVER'));
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    define('DB_PORT', getenv('DB_PORT') ?: '5432');
    define('DB_NAME', getenv('DB_NAME') ?: 'kasuku_tgs');
    define('DB_USER', getenv('DB_USER') ?: 'postgres');
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'postgres');
} else {
    define('DB_DRIVER', 'sqlite');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '5432');
    define('DB_NAME', 'kasuku_tgs');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'postgres');
}

if (!defined('DB_SQLITE_PATH')) {
    define('DB_SQLITE_PATH', __DIR__ . '/db/kasuku_tgs.sqlite');
}

// Base URL used when building API links (no trailing slash)
define('APP_URL', 'http://localhost:8000');

/**
 * Create and return a PDO connection for the configured driver.
 */
function db_connect(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        if (DB_DRIVER === 'sqlite') {
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
    }
    return $pdo;
}

/**
 * Send a JSON response and terminate.
 */
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a JSON error response.
 */
function json_error(string $message, int $status = 400, $details = null): void
{
    $payload = ['error' => $message];
    if ($details !== null && is_array($details)) {
        $payload['details'] = $details;
    }
    json_response($payload, $status);
}

/**
 * Read and decode the JSON request body.
 */
function request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        json_error('Invalid JSON payload', 422);
    }
    return is_array($data) ? $data : [];
}

/**
 * Apply CORS headers and handle preflight requests.
 */
function handle_cors(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Sanitise a string for safe output / insert.
 */
function clean_string($value): string
{
    return is_string($value) ? trim(strip_tags($value)) : '';
}

/**
 * Issue a simple signed token for a user.
 * Format: <user_id>.<random>.<signature>  (HMAC with APP_SECRET)
 */
function issue_token(int $userId): string
{
    $secret = defined('APP_SECRET') ? APP_SECRET : 'kasuku-dev-secret';
    $random = bin2hex(random_bytes(16));
    $payload = $userId . '.' . $random;
    $sig = hash_hmac('sha256', $payload, $secret);
    return $payload . '.' . $sig;
}

/**
 * Validate a token and return the user id, or null if invalid.
 */
function verify_token(?string $token): ?int
{
    if (!$token) {
        return null;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    [$userId, $random, $sig] = $parts;
    $secret = defined('APP_SECRET') ? APP_SECRET : 'kasuku-dev-secret';
    $expected = hash_hmac('sha256', $userId . '.' . $random, $secret);
    if (!hash_equals($expected, $sig)) {
        return null;
    }
    return (int) $userId;
}

/**
 * Extract the bearer token from the Authorization header.
 */
function get_bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        return trim($m[1]);
    }
    return null;
}
