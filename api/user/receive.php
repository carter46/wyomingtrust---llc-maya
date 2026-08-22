<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();
$payload = get_json_input();

$coinKey = sanitize_text($payload['coin_key'] ?? '');
$amount = isset($payload['amount']) ? (float) $payload['amount'] : 0.0;
$fromAddress = sanitize_text($payload['from_address'] ?? '');

if ($coinKey === '' || $amount <= 0) {
    send_json(['success' => false, 'message' => 'Invalid request payload'], 400);
}

$db = getDatabase();
$db->beginTransaction();

try {
    $stmt = $db->prepare(
        'SELECT c.id AS coin_id, c.symbol
         FROM coins c
         WHERE c.coin_key = :coin_key
         LIMIT 1'
    );
    $stmt->execute([
        ':coin_key' => $coinKey,
    ]);

    $coin = $stmt->fetch();

    if (!$coin) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'Asset not found'], 404);
    }

    $coinId = (int) $coin['coin_id'];

    // Get or create user asset
    $assetStmt = $db->prepare('SELECT balance FROM user_assets WHERE user_id = :user AND coin_id = :coin LIMIT 1');
    $assetStmt->execute([
        ':user' => $userId,
        ':coin' => $coinId,
    ]);
    $asset = $assetStmt->fetch();

    $currentBalance = $asset ? (float) $asset['balance'] : 0.0;
    $newBalance = $currentBalance + $amount;

    if ($asset) {
        $update = $db->prepare('UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin');
        $update->execute([
            ':balance' => $newBalance,
            ':user' => $userId,
            ':coin' => $coinId,
        ]);
    } else {
        $insertAsset = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, :balance)');
        $insertAsset->execute([
            ':user' => $userId,
            ':coin' => $coinId,
            ':balance' => $newBalance,
        ]);
    }

    // Create transaction record
    $insertTx = $db->prepare(
        'INSERT INTO transactions (user_id, coin_id, asset_symbol, amount, fee, recipient, status, type, metadata)
         VALUES (:user, :coin, :symbol, :amount, :fee, :recipient, :status, :type, :metadata)'
    );
    $insertTx->execute([
        ':user' => $userId,
        ':coin' => $coinId,
        ':symbol' => $coin['symbol'] ?? strtoupper(substr($coinKey, 0, 3)),
        ':amount' => $amount,
        ':fee' => 0,
        ':recipient' => null,
        ':status' => 'completed',
        ':type' => 'receive',
        ':metadata' => json_encode(['from_address' => $fromAddress]),
    ]);

    $db->commit();

    send_json([
        'success' => true,
        'message' => 'Transaction processed successfully',
        'balance' => $newBalance,
    ]);
} catch (Exception $exception) {
    $db->rollBack();
    error_log('Receive transaction failed: ' . $exception->getMessage());
    send_json(['success' => false, 'message' => 'Failed to process transaction'], 500);
}
