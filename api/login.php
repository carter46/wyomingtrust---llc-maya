<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limiting for login attempts
check_rate_limit('login', 5, 300); // 5 attempts per 5 minutes

$payload = get_json_input();
$email = sanitize_text($payload['email'] ?? '');
$password = $payload['password'] ?? '';

if ($email === '' || $password === '') {
    send_json(['success' => false, 'message' => 'Email and password are required'], 400);
}

if (!validate_email($email)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

$db = getDatabase();

$stmt = $db->prepare('SELECT id, full_name, email, password, email_verified FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    send_json(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// Check if email verification is required
$settings = $db->query('SELECT require_email_verification FROM site_settings WHERE id = 1 LIMIT 1')->fetch();
$requireVerification = $settings ? (int) $settings['require_email_verification'] : 1;

if ($requireVerification && !(int) $user['email_verified']) {
    send_json(['success' => false, 'message' => 'Please verify your email address before logging in'], 403);
}

// Set session
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['full_name'];

send_json([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => (int) $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'email_verified' => (int) $user['email_verified'],
    ],
]);
