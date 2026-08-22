<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleGetProfile();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdateProfile();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleGetProfile() {
    // Check if this is a verification status check (for onboarding)
    if (isset($_GET['check_verification'])) {
        handleCheckVerification();
        return;
    }
    
    $userId = require_user_auth();
    $db = getDatabase();
    
    $stmt = $db->prepare('SELECT id, full_name, email, email_verified, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        send_json(['success' => false, 'message' => 'User not found'], 404);
    }
    
    send_json(['success' => true, 'user' => $user]);
}

function handleUpdateProfile() {
    $userId = require_user_auth();
    $payload = get_json_input();
    
    $db = getDatabase();
    
    $updates = [];
    $params = [':id' => $userId];
    
    if (isset($payload['full_name'])) {
        $fullName = sanitize_text($payload['full_name']);
        if ($fullName !== '') {
            $updates[] = 'full_name = :full_name';
            $params[':full_name'] = $fullName;
        }
    }
    
    if (isset($payload['email'])) {
        $email = sanitize_text($payload['email']);
        if ($email !== '' && validate_email($email)) {
            // Check if email is already in use by another user
            $emailCheck = $db->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
            $emailCheck->execute([':email' => $email, ':id' => $userId]);
            if ($emailCheck->fetch()) {
                send_json(['success' => false, 'message' => 'Email already in use'], 409);
            }
            $updates[] = 'email = :email';
            $updates[] = 'email_verified = 0'; // Reset verification when email changes
            $params[':email'] = $email;
            
            // Generate new 6-digit OTP code
            $otpCode = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $updates[] = 'email_verification_token = :verification_token';
            $params[':verification_token'] = $otpCode;
        }
    }
    
    // Handle password change
    if (isset($payload['password']) && isset($payload['current_password'])) {
        $currentPassword = $payload['current_password'];
        $newPassword = $payload['password'];
        
        // Verify current password
        $userStmt = $db->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $userStmt->execute([':id' => $userId]);
        $user = $userStmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            send_json(['success' => false, 'message' => 'Current password is incorrect'], 401);
        }
        
        // Validate new password
        $validation = validate_password($newPassword);
        if (!$validation['valid']) {
            send_json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        $updates[] = 'password = :password';
        $params[':password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }
    
    try {
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Update session if name or email changed
        if (isset($params[':full_name'])) {
            $_SESSION['user_name'] = $params[':full_name'];
        }
        if (isset($params[':email'])) {
            $_SESSION['user_email'] = $params[':email'];
            
            // Send verification email if email changed
            if (isset($params[':verification_token']) && isset($params[':email'])) {
                require_once __DIR__ . '/../email.php';
                $userStmt = $db->prepare('SELECT full_name FROM users WHERE id = :id');
                $userStmt->execute([':id' => $userId]);
                $user = $userStmt->fetch();
                if ($user) {
                    send_verification_email($params[':email'], $user['full_name'], $params[':verification_token']);
                }
            }
        }
        
        send_json(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (Exception $e) {
        error_log('Update profile failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update profile'], 500);
    }
}

function handleCheckVerification() {
    $userId = require_user_auth();
    $db = getDatabase();
    
    try {
        // Check if email verification is required
        $settings = $db->query('SELECT require_email_verification FROM site_settings WHERE id = 1 LIMIT 1')->fetch();
        $requireVerification = $settings ? (int) $settings['require_email_verification'] : 1;
        
        // Get user email verification status
        $stmt = $db->prepare('SELECT email, email_verified FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            send_json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        send_json([
            'success' => true,
            'requires_verification' => $requireVerification,
            'email_verified' => (int) $user['email_verified'] === 1,
            'email' => $user['email']
        ]);
        
    } catch (Exception $e) {
        error_log('Check verification failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to check verification status'], 500);
    }
}
