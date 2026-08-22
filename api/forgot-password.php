<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limiting for forgot password requests
check_rate_limit('forgot_password', 3, 600); // 3 attempts per 10 minutes

$payload = get_json_input();
$email = sanitize_text($payload['email'] ?? '');

if ($email === '') {
    send_json(['success' => false, 'message' => 'Email address is required'], 400);
}

if (!validate_email($email)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

$db = getDatabase();

try {
    // Get user information
    $stmt = $db->prepare('SELECT id, full_name, email FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    // Always return success message to avoid email enumeration
    // If user exists, send reset email; if not, just return success anyway
    if ($user) {
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
        
        // Store token in database
        $updateStmt = $db->prepare(
            'UPDATE users 
             SET password_reset_token = :token, 
                 password_reset_expires = :expires 
             WHERE id = :id'
        );
        $updateStmt->execute([
            ':token' => $resetToken,
            ':expires' => $expiresAt,
            ':id' => (int) $user['id']
        ]);
        
        // Send password reset email
        require_once __DIR__ . '/email.php';
        send_password_reset_email($user['email'], $user['full_name'], $resetToken);
    }
    
    // Always return success to prevent email enumeration (even if user doesn't exist)
    send_json([
        'success' => true,
        'message' => 'If an account exists for this email address, a password reset link has been sent. Please check your inbox and spam folder.'
    ]);
    
} catch (Exception $e) {
    error_log('Forgot password failed: ' . $e->getMessage());
    // Still return success to avoid information leakage
    send_json([
        'success' => true,
        'message' => 'If an account exists for this email address, a password reset link has been sent. Please check your inbox and spam folder.'
    ]);
}
