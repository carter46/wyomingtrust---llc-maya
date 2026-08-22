<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListWallets();
        break;
    case 'POST':
        handleLinkWallet();
        break;
    case 'DELETE':
        handleDeleteWallet();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListWallets() {
    $userId = require_user_auth();
    $db = getDatabase();
    
    $stmt = $db->prepare(
        'SELECT id, wallet_type, wallet_name, encryption_method, created_at, updated_at
         FROM linked_wallets
         WHERE user_id = :user_id
         ORDER BY created_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    $wallets = $stmt->fetchAll();
    
    // Don't send encrypted data to frontend
    foreach ($wallets as &$wallet) {
        unset($wallet['encrypted_data']);
    }
    
    send_json(['success' => true, 'wallets' => $wallets]);
}

function handleLinkWallet() {
    $userId = require_user_auth();
    
    // CSRF protection for wallet linking
    require_csrf_token();
    
    $payload = get_json_input();
    
    $walletType = sanitize_text($payload['wallet_type'] ?? '');
    $walletName = sanitize_text($payload['wallet_name'] ?? '');
    $walletData = $payload['wallet_data'] ?? '';
    
    if ($walletType === '' || $walletData === '') {
        send_json(['success' => false, 'message' => 'Wallet type and data are required'], 400);
    }
    
    // Encrypt wallet data before storing
    try {
        $encryptedData = encrypt_data(json_encode($walletData));
    } catch (Exception $e) {
        error_log('Wallet encryption failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to encrypt wallet data'], 500);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare(
            'INSERT INTO linked_wallets (user_id, wallet_type, wallet_name, encrypted_data, encryption_method)
             VALUES (:user_id, :wallet_type, :wallet_name, :encrypted_data, :encryption_method)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':wallet_type' => $walletType,
            ':wallet_name' => $walletName ?: $walletType,
            ':encrypted_data' => $encryptedData,
            ':encryption_method' => 'aes-256-cbc',
        ]);
        
        $walletId = (int) $db->lastInsertId();
        
        send_json([
            'success' => true,
            'message' => 'Wallet linked successfully',
            'wallet' => [
                'id' => $walletId,
                'wallet_type' => $walletType,
                'wallet_name' => $walletName ?: $walletType,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Link wallet failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to link wallet'], 500);
    }
}

function handleDeleteWallet() {
    $userId = require_user_auth();
    $walletId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    
    if ($walletId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid wallet ID'], 400);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare('DELETE FROM linked_wallets WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $walletId, ':user_id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            send_json(['success' => false, 'message' => 'Wallet not found'], 404);
        }
        
        send_json(['success' => true, 'message' => 'Wallet unlinked successfully']);
    } catch (Exception $e) {
        error_log('Delete wallet failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to unlink wallet'], 500);
    }
}
