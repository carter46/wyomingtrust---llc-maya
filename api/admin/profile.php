<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleGetProfile();
        break;
    case 'PATCH':
        handleUpdateProfile();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleGetProfile() {
    require_admin_auth();
    $db = getDatabase();

    $adminId = $_SESSION['admin_id'];
    $stmt = $db->prepare('SELECT id, email, created_at FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $adminId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        send_json(['success' => false, 'message' => 'Admin not found'], 404);
    }

    send_json(['success' => true, 'admin' => $admin]);
}

function handleUpdateProfile() {
    require_admin_auth();
    $payload = get_json_input();
    $db = getDatabase();
    $adminId = $_SESSION['admin_id'];

    $action = $payload['action'] ?? '';

    if ($action === 'change_password') {
        $stmt = $db->prepare('SELECT password FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $adminId]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($payload['current_password'] ?? '', $admin['password'])) {
            send_json(['success' => false, 'message' => 'Current password is incorrect'], 401);
        }

        $newPassword = password_hash($payload['new_password'], PASSWORD_DEFAULT);
        $updateStmt = $db->prepare('UPDATE admins SET password = :password WHERE id = :id');
        $updateStmt->execute([':password' => $newPassword, ':id' => $adminId]);

        send_json(['success' => true, 'message' => 'Password updated successfully']);
    }

    if ($action === 'change_email') {
        $stmt = $db->prepare('SELECT password FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $adminId]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($payload['password'] ?? '', $admin['password'])) {
            send_json(['success' => false, 'message' => 'Password is incorrect'], 401);
        }

        $newEmail = sanitize_text($payload['new_email'] ?? '');
        if (!validate_email($newEmail)) {
            send_json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        $checkStmt = $db->prepare('SELECT id FROM admins WHERE email = :email AND id != :id LIMIT 1');
        $checkStmt->execute([':email' => $newEmail, ':id' => $adminId]);
        if ($checkStmt->fetch()) {
            send_json(['success' => false, 'message' => 'Email address already in use'], 400);
        }

        $updateStmt = $db->prepare('UPDATE admins SET email = :email WHERE id = :id');
        $updateStmt->execute([':email' => $newEmail, ':id' => $adminId]);

        $_SESSION['admin_email'] = $newEmail;

        send_json(['success' => true, 'message' => 'Email updated successfully']);
    }

    send_json(['success' => false, 'message' => 'Invalid action'], 400);
}
