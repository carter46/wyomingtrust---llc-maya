<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limiting for OTP verification attempts
check_rate_limit('verify_otp', 5, 300); // 5 attempts per 5 minutes

$payload = get_json_input();
$otpCode = sanitize_text($payload['otp'] ?? '');
$email = sanitize_text($payload['email'] ?? '');

if (empty($otpCode) || empty($email)) {
    send_json(['success' => false, 'message' => 'OTP code and email are required'], 400);
}

// Validate OTP format (6 digits)
if (!preg_match('/^\d{6}$/', $otpCode)) {
    send_json(['success' => false, 'message' => 'Invalid OTP code format. Please enter 6 digits.'], 400);
}

if (!validate_email($email)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

$db = getDatabase();

try {
    // Find user by email and OTP code (stored in email_verification_token field)
    $stmt = $db->prepare(
        'SELECT id, email, email_verified, email_verification_token, last_verification_email_sent 
         FROM users 
         WHERE email = :email AND email_verification_token = :otp 
         LIMIT 1'
    );
    $stmt->execute([
        ':email' => $email,
        ':otp' => $otpCode
    ]);
    $user = $stmt->fetch();
    
    if (!$user) {
        send_json(['success' => false, 'message' => 'Invalid OTP code. Please check and try again.'], 400);
    }
    
    // Check if already verified
    if ((int) $user['email_verified'] === 1) {
        send_json([
            'success' => true,
            'message' => 'Email already verified',
            'email_verified' => true
        ]);
    }
    
    // Check if OTP has expired (24 hours)
    $lastSent = $user['last_verification_email_sent'];
    if ($lastSent !== null) {
        $lastSentTimestamp = strtotime($lastSent);
        $currentTimestamp = time();
        $hoursElapsed = ($currentTimestamp - $lastSentTimestamp) / 3600;
        
        if ($hoursElapsed > 24) {
            send_json([
                'success' => false,
                'message' => 'OTP code has expired. Please request a new verification email.',
                'expired' => true
            ], 410);
        }
    }
    
    // Verify email and clear OTP
    $update = $db->prepare('UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = :id');
    $update->execute([':id' => (int) $user['id']]);
    
    send_json([
        'success' => true,
        'message' => 'Email verified successfully!',
        'email_verified' => true
    ]);
    
} catch (Exception $e) {
    error_log('OTP verification failed: ' . $e->getMessage());
    send_json(['success' => false, 'message' => 'Failed to verify OTP. Please try again.'], 500);
}
