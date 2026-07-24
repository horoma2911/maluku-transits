<?php
/**
 * Application configuration and shared helpers.
 * Values can be overridden via environment variables (see Railway).
 */

define('APP_NAME', 'MALUKU LOGISTICS');
define('APP_VERSION', '1.0');

define('APP_SECRET', getenv('APP_SECRET') ?: 'kasuku-dev-secret');

function env($key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== null) return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '' && $_ENV[$key] !== null) return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '' && $_SERVER[$key] !== null) return $_SERVER[$key];
    return $default;
}

if (env('DATABASE_URL')) {
    $url = parse_url(env('DATABASE_URL'));
    define('DB_DRIVER', 'pgsql');
    define('DB_HOST', $url['host'] ?? '127.0.0.1');
    define('DB_PORT', $url['port'] ?? '5432');
    define('DB_NAME', ltrim($url['path'] ?? 'kasuku_tgs', '/'));
    define('DB_USER', $url['user'] ?? 'postgres');
    define('DB_PASS', $url['pass'] ?? '');
} elseif (env('DB_DRIVER')) {
    define('DB_DRIVER', env('DB_DRIVER'));
    define('DB_HOST', env('DB_HOST', '127.0.0.1'));
    define('DB_PORT', env('DB_PORT', '5432'));
    define('DB_NAME', env('DB_NAME', 'kasuku_tgs'));
    define('DB_USER', env('DB_USER', 'postgres'));
    define('DB_PASS', env('DB_PASS', 'postgres'));
} elseif (env('PGHOST')) {
    define('DB_DRIVER', 'pgsql');
    define('DB_HOST', env('PGHOST', '127.0.0.1'));
    define('DB_PORT', env('PGPORT', '5432'));
    define('DB_NAME', env('PGDATABASE', 'kasuku_tgs'));
    define('DB_USER', env('PGUSER', 'postgres'));
    define('DB_PASS', env('PGPASSWORD', ''));
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
define('APP_URL', env('APP_URL', 'http://localhost:8000'));
define('APP_ALLOWED_ORIGINS', env('APP_ALLOWED_ORIGINS', implode(',', [APP_URL, 'http://127.0.0.1:8000'])));
define('APP_TOKEN_TTL', (int) env('APP_TOKEN_TTL', 43200));

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
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = array_filter(array_map('trim', explode(',', APP_ALLOWED_ORIGINS)), fn($v) => $v !== '');
    $allowOrigin = '';

    if ($origin !== '' && in_array($origin, $allowed, true)) {
        $allowOrigin = $origin;
    } elseif ($allowed !== []) {
        $allowOrigin = $allowed[0];
    }

    if ($allowOrigin !== '') {
        header('Access-Control-Allow-Origin: ' . $allowOrigin);
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
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
 * Format: <user_id>.<issued_at>.<random>.<signature>  (HMAC with APP_SECRET)
 */
function issue_token(int $userId): string
{
    $secret = defined('APP_SECRET') ? APP_SECRET : 'kasuku-dev-secret';
    $issuedAt = time();
    $random = bin2hex(random_bytes(16));
    $payload = $userId . '.' . $issuedAt . '.' . $random;
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
    $secret = defined('APP_SECRET') ? APP_SECRET : 'kasuku-dev-secret';

    if (count($parts) === 4) {
        [$userId, $issuedAt, $random, $sig] = $parts;
        $expected = hash_hmac('sha256', $userId . '.' . $issuedAt . '.' . $random, $secret);
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        if ((time() - (int) $issuedAt) > APP_TOKEN_TTL) {
            return null;
        }
        return (int) $userId;
    }

    if (count($parts) === 3) {
        [$userId, $random, $sig] = $parts;
        $expected = hash_hmac('sha256', $userId . '.' . $random, $secret);
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        return (int) $userId;
    }

    return null;
}

/**
 * Return the authenticated user record from the database if the bearer token is valid.
 */
function current_user(): ?array
{
    $userId = verify_token(get_bearer_token());
    if ($userId === null) {
        return null;
    }

    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT id, name, email, role, status, last_login FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    if (!$user || ($user['status'] ?? 'active') !== 'active') {
        return null;
    }
    return $user;
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
