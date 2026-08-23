<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListUsers();
        break;
    case 'POST':
        handleCreateUser();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdateUser();
        break;
    case 'DELETE':
        handleDeleteUser();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListUsers() {
    require_admin_auth();
    $db = getDatabase();
    
    $stmt = $db->query(
        'SELECT u.id, u.full_name, u.email, u.email_verified, u.created_at, u.updated_at,
                COUNT(DISTINCT ut.id) AS trusts_count,
                SUM(CASE WHEN LOWER(ut.status) = \'pending\' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN LOWER(ut.status) = \'active\' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN LOWER(ut.status) = \'inactive\' OR LOWER(ut.payment_status) = \'rejected\' THEN 1 ELSE 0 END) AS rejected_count
         FROM users u
         LEFT JOIN user_trusts ut ON ut.user_id = u.id
         GROUP BY u.id, u.full_name, u.email, u.email_verified, u.created_at, u.updated_at
         ORDER BY u.created_at DESC'
    );
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as &$user) {
        $user['id'] = (int) ($user['id'] ?? 0);
        $user['trusts_count'] = (int) ($user['trusts_count'] ?? 0);
        $user['email_verified'] = (int) ($user['email_verified'] ?? 0);
        $pending = (int) ($user['pending_count'] ?? 0);
        $active = (int) ($user['active_count'] ?? 0);
        $rejected = (int) ($user['rejected_count'] ?? 0);
        unset($user['pending_count'], $user['active_count'], $user['rejected_count']);

        // Aggregate LLC status for the user row (pending > rejected > approved > none)
        if ($user['trusts_count'] <= 0) {
            $user['llc_status'] = 'none';
        } elseif ($pending > 0) {
            $user['llc_status'] = 'pending';
        } elseif ($rejected > 0 && $active <= 0) {
            $user['llc_status'] = 'rejected';
        } elseif ($active > 0) {
            $user['llc_status'] = 'approved';
        } else {
            // e.g. only liquidated/suspended — treat as previously approved
            $user['llc_status'] = 'approved';
        }
    }
    unset($user);
    
    send_json(['success' => true, 'users' => $users]);
}

function handleCreateUser() {
    require_admin_auth();
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
    
    $exists = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $exists->execute([':email' => $email]);
    if ((int) $exists->fetchColumn() > 0) {
        send_json(['success' => false, 'message' => 'Email already in use'], 409);
    }
    
    try {
        $stmt = $db->prepare('INSERT INTO users (full_name, email, password) VALUES (:full_name, :email, :password)');
        $stmt->execute([
            ':full_name' => $fullName,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        
        $userId = (int) $db->lastInsertId();
        
        send_json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => [
                'id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Create user failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to create user'], 500);
    }
}

function handleUpdateUser() {
    require_admin_auth();
    $payload = get_json_input();
    $userId = isset($payload['id']) ? (int) $payload['id'] : 0;
    
    if ($userId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid user ID'], 400);
    }
    
    $db = getDatabase();
    
    // Check if user exists
    $check = $db->prepare('SELECT id FROM users WHERE id = :id');
    $check->execute([':id' => $userId]);
    if (!$check->fetch()) {
        send_json(['success' => false, 'message' => 'User not found'], 404);
    }
    
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
            $params[':email'] = $email;
        }
    }
    
    if (isset($payload['email_verified'])) {
        $updates[] = 'email_verified = :email_verified';
        $params[':email_verified'] = (int) $payload['email_verified'];
    }
    
    if (isset($payload['password'])) {
        $password = $payload['password'];
        $validation = validate_password($password);
        if (!$validation['valid']) {
            send_json(['success' => false, 'message' => $validation['message']], 400);
        }
        $updates[] = 'password = :password';
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }
    
    try {
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        send_json(['success' => true, 'message' => 'User updated successfully']);
    } catch (Exception $e) {
        error_log('Update user failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update user'], 500);
    }
}

function handleDeleteUser() {
    require_admin_auth();
    $userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    
    if ($userId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid user ID'], 400);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            send_json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        send_json(['success' => true, 'message' => 'User deleted successfully']);
    } catch (Exception $e) {
        error_log('Delete user failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to delete user'], 500);
    }
}
