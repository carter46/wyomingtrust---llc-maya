<?php

require_once __DIR__ . '/helpers.php';

// Check if this is an AJAX request or direct link visit
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if ($isAjax) {
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
    } else {
        header('Location: verify-status.php?error=invalid_method');
        exit;
    }
}

$token = isset($_GET['token']) ? sanitize_text($_GET['token']) : '';

if ($token === '') {
    if ($isAjax) {
        send_json(['success' => false, 'message' => 'Verification token is required'], 400);
    } else {
        header('Location: verify-status.php?error=missing_token');
        exit;
    }
}

$db = getDatabase();

$stmt = $db->prepare('SELECT id, email, email_verified FROM users WHERE email_verification_token = :token LIMIT 1');
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    if ($isAjax) {
        send_json(['success' => false, 'message' => 'Invalid or expired verification token'], 404);
    } else {
        header('Location: verify-status.php?error=invalid_token');
        exit;
    }
}

// Check if already verified
if ((int) $user['email_verified'] === 1) {
    require_once __DIR__ . '/config.php';
    $siteUrl = getSiteUrl();
    if ($isAjax) {
        send_json([
            'success' => true,
            'message' => 'Email already verified',
            'redirect_url' => $siteUrl . '/login.php?verified=1'
        ]);
    } else {
        header('Location: ' . $siteUrl . '/login.php?verified=1');
        exit;
    }
}

try {
    $update = $db->prepare('UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = :id');
    $update->execute([':id' => (int) $user['id']]);
    
    require_once __DIR__ . '/config.php';
    $siteUrl = getSiteUrl();
    $redirectUrl = $siteUrl . '/login.php?verified=1&email=' . urlencode($user['email']);
    
    if ($isAjax) {
        send_json([
            'success' => true,
            'message' => 'Email verified successfully! You can now log in.',
            'redirect_url' => $redirectUrl
        ]);
    } else {
        // Redirect to login page with success message
        header('Location: ' . $redirectUrl);
        exit;
    }
} catch (Exception $e) {
    error_log('Email verification failed: ' . $e->getMessage());
    if ($isAjax) {
        send_json(['success' => false, 'message' => 'Failed to verify email'], 500);
    } else {
        header('Location: verify-status.php?error=verification_failed');
        exit;
    }
}
