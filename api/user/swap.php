<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();

// CSRF protection for sensitive operations
require_csrf_token();

$payload = get_json_input();

$fromCoinKey = sanitize_text($payload['from_coin_key'] ?? '');
$toCoinKey = sanitize_text($payload['to_coin_key'] ?? '');
$fromAmount = isset($payload['from_amount']) ? (float) $payload['from_amount'] : 0.0;
$toAmount = isset($payload['to_amount']) ? (float) $payload['to_amount'] : 0.0;
$fee = isset($payload['fee']) ? (float) $payload['fee'] : 0.0;

if ($fromCoinKey === '' || $toCoinKey === '' || $fromAmount <= 0 || $toAmount <= 0) {
    send_json(['success' => false, 'message' => 'Invalid request payload'], 400);
}

if ($fromCoinKey === $toCoinKey) {
    send_json(['success' => false, 'message' => 'Cannot swap same asset'], 400);
}

$db = getDatabase();
$db->beginTransaction();

try {
    // Get from coin
    $fromStmt = $db->prepare(
        'SELECT c.id AS coin_id, c.symbol, ua.balance
         FROM coins c
         LEFT JOIN user_assets ua ON ua.coin_id = c.id AND ua.user_id = :user
         WHERE c.coin_key = :coin_key
         LIMIT 1'
    );
    $fromStmt->execute([
        ':user' => $userId,
        ':coin_key' => $fromCoinKey,
    ]);
    $fromCoin = $fromStmt->fetch();

    if (!$fromCoin) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'From asset not found'], 404);
    }

    // Get to coin
    $toStmt = $db->prepare(
        'SELECT c.id AS coin_id, c.symbol
         FROM coins c
         WHERE c.coin_key = :to_coin_key
         LIMIT 1'
    );
    $toStmt->execute([
        ':to_coin_key' => $toCoinKey,
    ]);
    $toCoin = $toStmt->fetch();

    if (!$toCoin) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'To asset not found'], 404);
    }

    $fromCoinId = (int) $fromCoin['coin_id'];
    $toCoinId = (int) $toCoin['coin_id'];
    $currentFromBalance = isset($fromCoin['balance']) ? (float) $fromCoin['balance'] : 0.0;
    $totalRequired = $fromAmount + max($fee, 0);

    if ($currentFromBalance < $totalRequired) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'Insufficient balance'], 400);
    }

    // Update from balance
    $newFromBalance = $currentFromBalance - $totalRequired;
    $updateFrom = $db->prepare('UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin');
    $updateFrom->execute([
        ':balance' => $newFromBalance,
        ':user' => $userId,
        ':coin' => $fromCoinId,
    ]);

    if ($updateFrom->rowCount() === 0) {
        $insertFromAsset = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, :balance)');
        $insertFromAsset->execute([
            ':user' => $userId,
            ':coin' => $fromCoinId,
            ':balance' => $newFromBalance,
        ]);
    }

    // Update to balance
    $toAssetStmt = $db->prepare('SELECT balance FROM user_assets WHERE user_id = :user AND coin_id = :coin LIMIT 1');
    $toAssetStmt->execute([
        ':user' => $userId,
        ':coin' => $toCoinId,
    ]);
    $toAsset = $toAssetStmt->fetch();

    $currentToBalance = $toAsset ? (float) $toAsset['balance'] : 0.0;
    $newToBalance = $currentToBalance + $toAmount;

    if ($toAsset) {
        $updateTo = $db->prepare('UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin');
        $updateTo->execute([
            ':balance' => $newToBalance,
            ':user' => $userId,
            ':coin' => $toCoinId,
        ]);
    } else {
        $insertToAsset = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, :balance)');
        $insertToAsset->execute([
            ':user' => $userId,
            ':coin' => $toCoinId,
            ':balance' => $newToBalance,
        ]);
    }

    // Create swap transaction record
    $insertTx = $db->prepare(
        'INSERT INTO transactions (user_id, coin_id, asset_symbol, amount, fee, status, type, metadata)
         VALUES (:user, :coin, :symbol, :amount, :fee, :status, :type, :metadata)'
    );
    $insertTx->execute([
        ':user' => $userId,
        ':coin' => $fromCoinId,
        ':symbol' => $fromCoin['symbol'] ?? strtoupper(substr($fromCoinKey, 0, 3)),
        ':amount' => $fromAmount,
        ':fee' => $fee,
        ':status' => 'completed',
        ':type' => 'swap',
        ':metadata' => json_encode([
            'from_coin' => $fromCoinKey,
            'to_coin' => $toCoinKey,
            'from_amount' => $fromAmount,
            'to_amount' => $toAmount,
        ]),
    ]);

    $db->commit();

    send_json([
        'success' => true,
        'message' => 'Swap completed successfully',
        'from_balance' => $newFromBalance,
        'to_balance' => $newToBalance,
    ]);
} catch (Exception $exception) {
    $db->rollBack();
    error_log('Swap transaction failed: ' . $exception->getMessage());
    send_json(['success' => false, 'message' => 'Failed to process swap'], 500);
}
