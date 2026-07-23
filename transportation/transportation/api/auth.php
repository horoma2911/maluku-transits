<?php
/**
 * Authentication endpoints.
 *
 * POST /api/auth/login              { "email", "password" }
 * POST /api/auth/forgot-password    { "email" }
 * POST /api/auth/reset-password     { "token", "password" }
 * POST /api/auth/change-password    { "current_password", "new_password" }
 */

require_once __DIR__ . '/bootstrap.php';

function generate_reset_token(): string {
    return bin2hex(random_bytes(32));
}

function detect_auth_action(): string {
    $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $reqPath = trim($reqPath, '/');
    if (strpos($reqPath, 'api/') === 0) {
        $reqPath = substr($reqPath, strlen('api/'));
    }
    $reqPath = trim($reqPath, '/');
    $segments = explode('/', $reqPath);
    return $segments[1] ?? 'login';
}

$action = detect_auth_action();

if ($action === 'forgot-password') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Method not allowed', 405);
    }

    $body = request_json();
    $email = clean_string($body['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('A valid email address is required', 422);
    }

    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = generate_reset_token();
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $pdo->prepare('DELETE FROM password_resets WHERE email = :email')
            ->execute(['email' => $email]);
        $stmt = $pdo->prepare(
            'INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)'
        );
        $stmt->execute([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ]);
        json_response([
            'message' => 'Reset token generated. Use it within 1 hour.',
            'token'   => $token,
        ], 200);
    }

    json_error('No account found with that email address.', 404);
}

if ($action === 'reset-password') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Method not allowed', 405);
    }

    $body = request_json();
    $token = clean_string($body['token'] ?? '');
    $newPassword = $body['password'] ?? '';

    if ($token === '' || $newPassword === '' || strlen($newPassword) < 6) {
        json_error('Token and a new password (min 6 characters) are required', 422);
    }

    $pdo = db_connect();
    $stmt = $pdo->prepare(
        'SELECT id, email FROM password_resets WHERE token = :token AND expires_at > CURRENT_TIMESTAMP LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        json_error('Invalid or expired reset token', 400);
    }

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $pdo->prepare('UPDATE users SET password = :hash WHERE email = :email')
        ->execute(['hash' => $hash, 'email' => $reset['email']]);

    $pdo->prepare('DELETE FROM password_resets WHERE id = :id')
        ->execute(['id' => $reset['id']]);

    json_response(['message' => 'Password has been reset successfully.'], 200);
}

if ($action === 'change-password') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Method not allowed', 405);
    }

    $userId = verify_token(get_bearer_token());
    if ($userId === null) {
        json_error('Authentication required', 401);
    }

    $body = request_json();
    $current = $body['current_password'] ?? '';
    $new = $body['new_password'] ?? '';

    if ($current === '' || $new === '' || strlen($new) < 6) {
        json_error('Current password and a new password (min 6 characters) are required', 422);
    }

    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        json_error('Current password is incorrect', 401);
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $pdo->prepare('UPDATE users SET password = :hash WHERE id = :id')
        ->execute(['hash' => $hash, 'id' => $userId]);

    json_response(['message' => 'Password updated successfully.'], 200);
}

// Default: login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$body = request_json();
$email = clean_string($body['email'] ?? '');
$password = $body['password'] ?? '';

if ($email === '' || $password === '') {
    json_error('Email and password are required', 422);
}

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
    json_error('Invalid email or password', 401);
}

if (($user['status'] ?? 'active') !== 'active') {
    json_error('Account is inactive. Contact an administrator.', 403);
}

$now = date('Y-m-d H:i');
$pdo->prepare('UPDATE users SET last_login = :ts WHERE id = :id')
    ->execute(['ts' => $now, 'id' => $user['id']]);

$token = issue_token($user['id']);

unset($user['password']);

json_response([
    'token' => $token,
    'user'  => $user,
], 200);
