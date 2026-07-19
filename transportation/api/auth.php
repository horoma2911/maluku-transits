<?php
/**
 * Authentication endpoint.
 *
 * POST /api/auth/login
 *   Body: { "email": "...", "password": "..." }
 *   Returns: { "token": "...", "user": { ... } } on success (200)
 *   Returns: { "error": "..." } on failure (401)
 */

require_once __DIR__ . '/bootstrap.php';

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

// Update last login
$now = date('Y-m-d H:i');
$pdo->prepare('UPDATE users SET last_login = :ts WHERE id = :id')
    ->execute(['ts' => $now, 'id' => $user['id']]);

$token = issue_token($user['id']);

// Never expose the password hash
unset($user['password']);

json_response([
    'token' => $token,
    'user'  => $user,
], 200);
