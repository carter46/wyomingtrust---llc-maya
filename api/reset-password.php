<?php

require_once __DIR__ . '/helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleValidateToken();
        break;
    case 'POST':
        handleResetPassword();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleValidateToken() {
    $token = isset($_GET['token']) ? sanitize_text($_GET['token']) : '';
    
    if ($token === '') {
        send_json(['success' => false, 'message' => 'Token is required'], 400);
    }
    
    $db = getDatabase();
    
    try {
        // Find user by reset token
        $stmt = $db->prepare(
            'SELECT id, email, password_reset_expires 
             FROM users 
             WHERE password_reset_token = :token 
             LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            send_json(['success' => false, 'valid' => false, 'message' => 'Invalid reset token'], 404);
        }
        
        // Check if token has expired
        $expiresAt = strtotime($user['password_reset_expires']);
        if ($expiresAt === false || time() > $expiresAt) {
            send_json(['success' => false, 'valid' => false, 'message' => 'Reset token has expired'], 410);
        }
        
        send_json([
            'success' => true,
            'valid' => true,
            'message' => 'Token is valid'
        ]);
        
    } catch (Exception $e) {
        error_log('Validate token failed: ' . $e->getMessage());
        send_json(['success' => false, 'valid' => false, 'message' => 'Failed to validate token'], 500);
    }
}

function handleResetPassword() {

$payload = get_json_input();
$token = sanitize_text($payload['token'] ?? '');
$password = $payload['password'] ?? '';
$confirmPassword = $payload['confirm_password'] ?? '';

if ($token === '') {
    send_json(['success' => false, 'message' => 'Reset token is required'], 400);
}

if ($password === '' || $confirmPassword === '') {
    send_json(['success' => false, 'message' => 'Password and confirmation are required'], 400);
}

if ($password !== $confirmPassword) {
    send_json(['success' => false, 'message' => 'Passwords do not match'], 400);
}

// Validate password strength
if (strlen($password) < 8) {
    send_json(['success' => false, 'message' => 'Password must be at least 8 characters long'], 400);
}

if (!preg_match('/[A-Z]/', $password)) {
    send_json(['success' => false, 'message' => 'Password must contain at least one uppercase letter'], 400);
}

if (!preg_match('/[a-z]/', $password)) {
    send_json(['success' => false, 'message' => 'Password must contain at least one lowercase letter'], 400);
}

if (!preg_match('/[0-9]/', $password)) {
    send_json(['success' => false, 'message' => 'Password must contain at least one number'], 400);
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    send_json(['success' => false, 'message' => 'Password must contain at least one special character'], 400);
}

$db = getDatabase();

try {
    // Find user by reset token
    $stmt = $db->prepare(
        'SELECT id, email, full_name, password_reset_expires 
         FROM users 
         WHERE password_reset_token = :token 
         LIMIT 1'
    );
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        send_json(['success' => false, 'message' => 'Invalid or expired reset token'], 404);
    }
    
    // Check if token has expired
    $expiresAt = strtotime($user['password_reset_expires']);
    if ($expiresAt === false || time() > $expiresAt) {
        // Clear expired token
        $db->prepare('UPDATE users SET password_reset_token = NULL, password_reset_expires = NULL WHERE id = :id')
           ->execute([':id' => (int) $user['id']]);
        
        send_json(['success' => false, 'message' => 'Reset token has expired. Please request a new password reset.'], 410);
    }
    
    // Update password and clear reset token
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $updateStmt = $db->prepare(
        'UPDATE users 
         SET password = :password, 
             password_reset_token = NULL, 
             password_reset_expires = NULL 
         WHERE id = :id'
    );
    $updateStmt->execute([
        ':password' => $hashedPassword,
        ':id' => (int) $user['id']
    ]);
    
    send_json([
        'success' => true,
        'message' => 'Password reset successfully. You can now log in with your new password.'
    ]);
    
} catch (Exception $e) {
    error_log('Reset password failed: ' . $e->getMessage());
    send_json(['success' => false, 'message' => 'Failed to reset password. Please try again.'], 500);
}
}
