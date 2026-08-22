<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$payload = get_json_input();
$email = sanitize_text($payload['email'] ?? '');

if ($email === '') {
    send_json(['success' => false, 'message' => 'Email is required'], 400);
}

if (!validate_email($email)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

$db = getDatabase();

try {
    // Get user information
    $stmt = $db->prepare('SELECT id, full_name, email_verified, email_verification_token, last_verification_email_sent FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        send_json(['success' => false, 'message' => 'Email not found'], 404);
    }
    
    // Check if already verified
    if ((int) $user['email_verified'] === 1) {
        send_json(['success' => false, 'message' => 'Email is already verified'], 400);
    }
    
    // Check cooldown (60 seconds)
    $cooldownSeconds = 60;
    $lastSent = $user['last_verification_email_sent'];
    
    if ($lastSent !== null) {
        $lastSentTimestamp = strtotime($lastSent);
        $currentTimestamp = time();
        $timeElapsed = $currentTimestamp - $lastSentTimestamp;
        $cooldownRemaining = $cooldownSeconds - $timeElapsed;
        
        if ($cooldownRemaining > 0) {
            send_json([
                'success' => false,
                'message' => 'Please wait before requesting another verification email',
                'cooldown_remaining' => $cooldownRemaining,
            ], 429);
        }
    }
    
    // Generate new 6-digit OTP code
    $otpCode = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $updateToken = $db->prepare('UPDATE users SET email_verification_token = :otp WHERE id = :id');
    $updateToken->execute([
        ':otp' => $otpCode,
        ':id' => (int) $user['id'],
    ]);
    
    // Send verification email with OTP
    require_once __DIR__ . '/../email.php';
    $emailSent = send_verification_email($email, $user['full_name'], $otpCode);
    
    if ($emailSent) {
        // Update last_verification_email_sent timestamp
        $updateTimestamp = $db->prepare('UPDATE users SET last_verification_email_sent = CURRENT_TIMESTAMP WHERE id = :id');
        $updateTimestamp->execute([':id' => (int) $user['id']]);
        
        send_json([
            'success' => true,
            'message' => 'Verification email sent successfully. Please check your inbox.',
            'cooldown_remaining' => $cooldownSeconds,
        ]);
    } else {
        send_json(['success' => false, 'message' => 'Failed to send verification email. Please try again later.'], 500);
    }
} catch (Exception $e) {
    error_log('Resend verification email failed: ' . $e->getMessage());
    send_json(['success' => false, 'message' => 'Failed to process request'], 500);
}
