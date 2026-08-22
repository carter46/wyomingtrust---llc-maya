<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limiting for registration attempts
check_rate_limit('register', 3, 600); // 3 attempts per 10 minutes

$payload = get_json_input();
$fullName = sanitize_text($payload['full_name'] ?? '');
$email = sanitize_text($payload['email'] ?? '');
$password = $payload['password'] ?? '';

if ($fullName === '' || $email === '' || $password === '') {
    send_json(['success' => false, 'message' => 'Name, email and password are required'], 400);
}

if (!validate_email($email)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

$validation = validate_password($password);
if (!$validation['valid']) {
    send_json(['success' => false, 'message' => $validation['message']], 400);
}

$db = getDatabase();

// Check if email already exists
$exists = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
$exists->execute([':email' => $email]);
if ((int) $exists->fetchColumn() > 0) {
    send_json(['success' => false, 'message' => 'Email already in use'], 409);
}

try {
    // Check if email verification is required
    $settings = $db->query('SELECT require_email_verification FROM site_settings WHERE id = 1 LIMIT 1')->fetch();
    $requireVerification = $settings ? (int) $settings['require_email_verification'] : 1;
    
    $emailVerified = 0;
    $verificationToken = null;
    $otpCode = null;
    
    if ($requireVerification) {
        // Generate 6-digit OTP code for email verification
        $otpCode = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // Store OTP in verification_token field (6 digits)
        $verificationToken = $otpCode;
    } else {
        $emailVerified = 1;
    }
    
    // Prepare INSERT - set timestamp only if verification is required
    if ($requireVerification && $verificationToken) {
        $stmt = $db->prepare(
            'INSERT INTO users (full_name, email, password, email_verified, email_verification_token, last_verification_email_sent)
             VALUES (:full_name, :email, :password, :email_verified, :verification_token, CURRENT_TIMESTAMP)'
        );
    } else {
        $stmt = $db->prepare(
            'INSERT INTO users (full_name, email, password, email_verified, email_verification_token, last_verification_email_sent)
             VALUES (:full_name, :email, :password, :email_verified, :verification_token, NULL)'
        );
    }
    
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':email_verified' => $emailVerified,
        ':verification_token' => $verificationToken,
    ]);
    
    $userId = (int) $db->lastInsertId();
    
    // Send verification email if required
    $emailSent = false;
    if ($requireVerification && $otpCode) {
        require_once __DIR__ . '/email.php';
        // Send OTP code via email
        $emailSent = send_verification_email($email, $fullName, $otpCode);
        
        // Update last_verification_email_sent timestamp if email was sent (in case INSERT didn't set it)
        if ($emailSent) {
            $updateTimestamp = $db->prepare('UPDATE users SET last_verification_email_sent = CURRENT_TIMESTAMP WHERE id = :id');
            $updateTimestamp->execute([':id' => $userId]);
        }
    }
    
    // Initialize user assets with default coins
    $coins = $db->query('SELECT id, default_balance FROM coins WHERE is_default = 1')->fetchAll();
    if ($coins) {
        $assetStmt = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user_id, :coin_id, :balance)');
        foreach ($coins as $coin) {
            $assetStmt->execute([
                ':user_id' => $userId,
                ':coin_id' => (int) $coin['id'],
                ':balance' => (float) $coin['default_balance'],
            ]);
        }
    }
    
    require_once __DIR__ . '/config.php';
    $siteUrl = getSiteUrl();
    $redirectUrl = $requireVerification ? $siteUrl . '/verify-status.php?email=' . urlencode($email) : $siteUrl . '/login.php';

    // Start session for newly registered user (onboarding uses registration as the entrypoint)
    $_SESSION['user_id'] = (int) $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $fullName;
    
    send_json([
        'success' => true,
        'message' => 'Registration successful' . ($requireVerification ? '. Please check your email for the verification code.' : ''),
        'email_sent' => $emailSent,
        'requires_verification' => $requireVerification,
        'email' => $email, // Include email for OTP verification step
        'user' => [
            'id' => $userId,
            'email' => $email,
            'email_verified' => $emailVerified,
        ],
    ]);
} catch (Exception $e) {
    error_log('Registration failed: ' . $e->getMessage());
    send_json(['success' => false, 'message' => 'Registration failed'], 500);
}
