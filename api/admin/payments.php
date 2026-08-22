<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

// Check for QR code upload action
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleListPaymentMethods();
        break;
    case 'POST':
        if ($action === 'upload_qr') {
            handleUploadQRCode();
        } else {
            handleCreatePaymentMethod();
        }
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdatePaymentMethod();
        break;
    case 'DELETE':
        handleDeletePaymentMethod();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListPaymentMethods() {
    require_admin_auth();
    $db = getDatabase();
    
    $stmt = $db->query(
        'SELECT id, method_type, method_name, is_active, config_data, created_at, updated_at
         FROM payment_methods
         ORDER BY method_type, method_name'
    );
    $methods = $stmt->fetchAll();
    
    // Decode JSON config_data
    foreach ($methods as &$method) {
        if (!empty($method['config_data'])) {
            $method['config_data'] = json_decode($method['config_data'], true) ?? [];
        } else {
            $method['config_data'] = [];
        }
    }
    
    send_json(['success' => true, 'methods' => $methods]);
}

function handleCreatePaymentMethod() {
    require_admin_auth();
    $payload = get_json_input();
    $methodType = sanitize_text($payload['method_type'] ?? '');
    $methodName = sanitize_text($payload['method_name'] ?? '');
    $configData = isset($payload['config_data']) ? $payload['config_data'] : [];
    $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;
    
    if ($methodType === '' || $methodName === '') {
        send_json(['success' => false, 'message' => 'Method type and name are required'], 400);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare(
            'INSERT INTO payment_methods (method_type, method_name, is_active, config_data)
             VALUES (:method_type, :method_name, :is_active, :config_data)'
        );
        $stmt->execute([
            ':method_type' => $methodType,
            ':method_name' => $methodName,
            ':is_active' => $isActive,
            ':config_data' => json_encode($configData),
        ]);
        
        $methodId = (int) $db->lastInsertId();
        
        send_json([
            'success' => true,
            'message' => 'Payment method created successfully',
            'method' => [
                'id' => $methodId,
                'method_type' => $methodType,
                'method_name' => $methodName,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Create payment method failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to create payment method'], 500);
    }
}

function handleUpdatePaymentMethod() {
    require_admin_auth();
    $payload = get_json_input();
    $methodId = isset($payload['id']) ? (int) $payload['id'] : 0;
    
    if ($methodId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid method ID'], 400);
    }
    
    $db = getDatabase();
    
    $updates = [];
    $params = [':id' => $methodId];
    
    if (isset($payload['method_type'])) {
        $updates[] = 'method_type = :method_type';
        $params[':method_type'] = sanitize_text($payload['method_type']);
    }
    
    if (isset($payload['method_name'])) {
        $updates[] = 'method_name = :method_name';
        $params[':method_name'] = sanitize_text($payload['method_name']);
    }
    
    if (isset($payload['is_active'])) {
        $updates[] = 'is_active = :is_active';
        $params[':is_active'] = (int) $payload['is_active'];
    }
    
    if (isset($payload['config_data'])) {
        $updates[] = 'config_data = :config_data';
        $params[':config_data'] = json_encode($payload['config_data']);
    }
    
    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }
    
    try {
        $sql = 'UPDATE payment_methods SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        send_json(['success' => true, 'message' => 'Payment method updated successfully']);
    } catch (Exception $e) {
        error_log('Update payment method failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update payment method'], 500);
    }
}

function handleDeletePaymentMethod() {
    require_admin_auth();
    $methodId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    
    if ($methodId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid method ID'], 400);
    }
    
    $db = getDatabase();
    
    // Check if payment method is in use
    $inUse = $db->prepare('SELECT COUNT(*) FROM transactions WHERE payment_method_id = :id');
    $inUse->execute([':id' => $methodId]);
    if ((int) $inUse->fetchColumn() > 0) {
        send_json(['success' => false, 'message' => 'Cannot delete payment method that is in use'], 400);
    }
    
    try {
        $stmt = $db->prepare('DELETE FROM payment_methods WHERE id = :id');
        $stmt->execute([':id' => $methodId]);
        
        if ($stmt->rowCount() === 0) {
            send_json(['success' => false, 'message' => 'Payment method not found'], 404);
        }
        
        send_json(['success' => true, 'message' => 'Payment method deleted successfully']);
    } catch (Exception $e) {
        error_log('Delete payment method failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to delete payment method'], 500);
    }
}

function handleUploadQRCode() {
    require_admin_auth();
    
    if (!isset($_FILES['qr_code']) || $_FILES['qr_code']['error'] !== UPLOAD_ERR_OK) {
        send_json(['success' => false, 'message' => 'No QR code file uploaded'], 400);
    }
    
    $paymentMethodId = isset($_POST['payment_method_id']) ? (int) $_POST['payment_method_id'] : 0;
    if ($paymentMethodId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid payment method ID'], 400);
    }
    
    $file = $_FILES['qr_code'];
    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        send_json(['success' => false, 'message' => 'Invalid file type. Allowed: PNG, JPG, SVG'], 400);
    }
    
    if ($file['size'] > $maxSize) {
        send_json(['success' => false, 'message' => 'File size exceeds 2MB limit'], 400);
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../uploads/payment_methods/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'qr_' . $paymentMethodId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        send_json(['success' => false, 'message' => 'Failed to save QR code file'], 500);
    }
    
    $relativePath = 'uploads/payment_methods/' . $filename;
    
    // Update payment method config_data with QR code path
    $db = getDatabase();
    
    try {
        // Get existing config
        $stmt = $db->prepare('SELECT config_data FROM payment_methods WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $paymentMethodId]);
        $method = $stmt->fetch();
        
        if (!$method) {
            unlink($filepath); // Clean up uploaded file
            send_json(['success' => false, 'message' => 'Payment method not found'], 404);
        }
        
        $configData = json_decode($method['config_data'], true) ?? [];
        $configData['qr_code'] = $relativePath;
        
        $updateStmt = $db->prepare('UPDATE payment_methods SET config_data = :config_data WHERE id = :id');
        $updateStmt->execute([
            ':config_data' => json_encode($configData),
            ':id' => $paymentMethodId
        ]);
        
        send_json([
            'success' => true,
            'message' => 'QR code uploaded successfully',
            'path' => $relativePath
        ]);
    } catch (Exception $e) {
        unlink($filepath); // Clean up on error
        error_log('Upload QR code failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update payment method with QR code'], 500);
    }
}
