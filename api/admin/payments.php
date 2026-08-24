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
    $configData = isset($payload['config_data']) && is_array($payload['config_data']) ? $payload['config_data'] : [];
    $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;
    $walletAddressId = isset($payload['wallet_address_id']) ? (int) $payload['wallet_address_id'] : (int) ($configData['wallet_address_id'] ?? 0);

    $db = getDatabase();

    // Crypto: create from an existing wallet address (no re-configuration)
    if ($methodType === 'crypto' && $walletAddressId > 0) {
        $addrStmt = $db->prepare(
            'SELECT wa.id, wa.address, wa.coin_id, c.coin_key, c.display_name, c.symbol
             FROM wallet_addresses wa
             INNER JOIN coins c ON c.id = wa.coin_id
             WHERE wa.id = :id
             LIMIT 1'
        );
        $addrStmt->execute([':id' => $walletAddressId]);
        $addr = $addrStmt->fetch(PDO::FETCH_ASSOC);
        if (!$addr) {
            send_json(['success' => false, 'message' => 'Wallet address not found. Configure it under Wallet Addresses first.'], 404);
        }

        // Block if this wallet address is already linked to a crypto payment method
        $existingMethods = $db->query(
            "SELECT id, config_data FROM payment_methods WHERE method_type = 'crypto'"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($existingMethods as $m) {
            $cfg = json_decode($m['config_data'] ?? '{}', true) ?: [];
            $usedId = (int) ($cfg['wallet_address_id'] ?? 0);
            $usedAddr = strtolower(trim((string) ($cfg['wallet_address'] ?? '')));
            if ($usedId === $walletAddressId || ($usedAddr !== '' && $usedAddr === strtolower(trim($addr['address'])))) {
                send_json(['success' => false, 'message' => 'This wallet address is already added as a payment method'], 400);
            }
        }

        $methodName = $addr['display_name'] ?: ($addr['symbol'] ?: 'Crypto');
        $configData = [
            'wallet_address_id' => (int) $addr['id'],
            'coin_id' => (int) $addr['coin_id'],
            'coin_key' => $addr['coin_key'],
            'coin_name' => $addr['display_name'],
            'coin_symbol' => $addr['symbol'],
            'wallet_address' => $addr['address'],
            'network_type' => $addr['symbol'] ?: $addr['display_name'],
        ];
    }

    if ($methodType === '' || $methodName === '') {
        send_json(['success' => false, 'message' => 'Method type and name are required'], 400);
    }
    
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

        // Auto-generate QR for crypto wallet addresses
        if ($methodType === 'crypto' && !empty($configData['wallet_address'])) {
            $qrPath = generate_payment_method_qr((string) $configData['wallet_address'], $methodId);
            if ($qrPath) {
                $configData['qr_code'] = $qrPath;
                $upd = $db->prepare('UPDATE payment_methods SET config_data = :config_data WHERE id = :id');
                $upd->execute([
                    ':config_data' => json_encode($configData),
                    ':id' => $methodId,
                ]);
            }
        }
        
        send_json([
            'success' => true,
            'message' => 'Payment method created successfully',
            'method' => [
                'id' => $methodId,
                'method_type' => $methodType,
                'method_name' => $methodName,
                'config_data' => $configData,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Create payment method failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to create payment method'], 500);
    }
}

/**
 * Download/generate a QR PNG for a wallet address and store under uploads/payment_methods.
 */
function generate_payment_method_qr(string $address, int $paymentMethodId): ?string {
    $address = trim($address);
    if ($address === '' || $paymentMethodId <= 0) {
        return null;
    }

    $uploadDir = __DIR__ . '/../../uploads/payment_methods/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        return null;
    }

    $png = null;
    $url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=12&data=' . rawurlencode($address);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
        ]);
        $png = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300 || $png === false || strlen($png) < 64) {
            $png = null;
        }
    }
    if ($png === null && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http' => ['timeout' => 12]]);
        $png = @file_get_contents($url, false, $ctx);
        if ($png === false || strlen($png) < 64) {
            $png = null;
        }
    }
    if ($png === null) {
        return null;
    }

    $filename = 'qr_' . $paymentMethodId . '_' . time() . '.png';
    $filepath = $uploadDir . $filename;
    if (@file_put_contents($filepath, $png) === false) {
        return null;
    }
    return 'uploads/payment_methods/' . $filename;
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
