<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'PUT':
    case 'PATCH':
        handleResetPassword();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleResetPassword() {
    $adminId = require_admin_auth();
    $payload = get_json_input();
    
    $userId = isset($payload['user_id']) ? (int) $payload['user_id'] : null;
    $newPassword = $payload['password'] ?? '';
    
    if ($userId !== null) {
        // Reset user password (admin can reset any user's password)
        if (strlen($newPassword) < 8) {
            send_json(['success' => false, 'message' => 'Password must be at least 8 characters long'], 400);
        }
        
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $userId,
        ]);
        
        send_json(['success' => true, 'message' => 'User password reset successfully']);
    } else {
        // Reset admin's own password
        if (strlen($newPassword) < 8) {
            send_json(['success' => false, 'message' => 'Password must be at least 8 characters long'], 400);
        }
        
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE admins SET password = :password WHERE id = :id');
        $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $adminId,
        ]);
        
        send_json(['success' => true, 'message' => 'Password updated successfully']);
    }
}
