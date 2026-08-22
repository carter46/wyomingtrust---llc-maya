<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();

// CSRF protection for sensitive operations
require_csrf_token();

$payload = get_json_input();

$coinKey = sanitize_text($payload['coin_key'] ?? '');
$recipient = sanitize_text($payload['recipient'] ?? '');
$amount = isset($payload['amount']) ? (float) $payload['amount'] : 0.0;
$fee = isset($payload['fee']) ? (float) $payload['fee'] : 0.0;
$isLiquidation = !empty($payload['is_liquidation']);
$trustId = isset($payload['trust_id']) ? (int) $payload['trust_id'] : 0;

if ($coinKey === '' || $amount <= 0) {
    send_json(['success' => false, 'message' => 'Invalid request payload'], 400);
}

if ($isLiquidation && $recipient === '') {
    send_json(['success' => false, 'message' => 'Recipient address is required for liquidation'], 400);
}

// Validate recipient address format
if (!empty($recipient) && !validate_crypto_address($recipient, $coinKey)) {
    send_json(['success' => false, 'message' => 'Invalid recipient address format for selected cryptocurrency'], 400);
}

$db = getDatabase();

// Liquidation platform fee is paid separately via checkout (USD), not debited in crypto.
$total = $amount + max($fee, 0);
$db->beginTransaction();

try {
    $stmt = $db->prepare(
        'SELECT c.id AS coin_id, c.symbol, ua.balance
         FROM coins c
         LEFT JOIN user_assets ua ON ua.coin_id = c.id AND ua.user_id = :user
         WHERE c.coin_key = :coin_key
         LIMIT 1'
    );
    $stmt->execute([
        ':user' => $userId,
        ':coin_key' => $coinKey,
    ]);

    $coin = $stmt->fetch();

    if (!$coin) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'Asset not found'], 404);
    }

    $coinId = (int) $coin['coin_id'];
    $currentBalance = isset($coin['balance']) ? (float) $coin['balance'] : 0.0;

    if ($currentBalance < $total) {
        $db->rollBack();
        send_json(['success' => false, 'message' => 'Insufficient balance'], 400);
    }

    if ($isLiquidation) {
        if ($trustId > 0) {
            $trustStmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
            $trustStmt->execute([':id' => $trustId, ':user_id' => $userId]);
            if (!$trustStmt->fetch()) {
                $db->rollBack();
                send_json(['success' => false, 'message' => 'Trust not found'], 404);
            }
        }

        $feeInfo = resolve_liquidation_fee_usd($db, $userId, $coinKey, $trustId);
        $feePayment = null;
        if ($feeInfo['has_fee']) {
            $feePayment = user_has_liquidation_fee_payment($db, $userId, $coinId, $trustId, true);
            if (!$feePayment) {
                $db->rollBack();
                $pending = user_has_liquidation_fee_payment($db, $userId, $coinId, $trustId, false);
                send_json([
                    'success' => false,
                    'message' => $pending
                        ? 'Liquidation fee payment is pending admin approval.'
                        : 'Liquidation fee payment is required. Please complete checkout first.',
                    'redirect_checkout' => !$pending,
                    'payment_pending' => (bool) $pending,
                ], $pending ? 409 : 402);
            }
        }

        $pendingStmt = $db->prepare(
            'SELECT t.id FROM transactions t
             WHERE t.user_id = :user_id AND t.type = "liquidation" AND t.status = "pending" AND t.coin_id = :coin_id
             LIMIT 1'
        );
        $pendingStmt->execute([':user_id' => $userId, ':coin_id' => $coinId]);
        if ($pendingStmt->fetch()) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'A pending liquidation for this asset already exists'], 400);
        }

        $transactionData = [
            'recipient' => $recipient,
            'submitted_at' => date('c'),
            'network_fee' => $fee,
            'total_debit' => $total,
        ];
        if (!empty($feePayment['id'])) {
            $transactionData['liquidation_fee_transaction_id'] = (int) $feePayment['id'];
        }

        $insertTx = $db->prepare(
            'INSERT INTO transactions (user_id, trust_id, coin_id, asset_symbol, amount, fee, recipient, status, type, transaction_data)
             VALUES (:user, :trust_id, :coin, :symbol, :amount, :fee, :recipient, "pending", "liquidation", :transaction_data)'
        );
        $insertTx->execute([
            ':user' => $userId,
            ':trust_id' => $trustId > 0 ? $trustId : null,
            ':coin' => $coinId,
            ':symbol' => $coin['symbol'] ?? strtoupper(substr($coinKey, 0, 3)),
            ':amount' => $amount,
            ':fee' => $fee,
            ':recipient' => $recipient,
            ':transaction_data' => json_encode($transactionData),
        ]);

        $db->commit();

        send_json([
            'success' => true,
            'message' => 'Liquidation request submitted for admin approval.',
            'pending' => true,
            'submission_id' => (int) $db->lastInsertId(),
        ]);
    }

    $newBalance = $currentBalance - $total;

    $update = $db->prepare('UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin');
    $update->execute([
        ':balance' => $newBalance,
        ':user' => $userId,
        ':coin' => $coinId,
    ]);

    if ($update->rowCount() === 0) {
        $insertAsset = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, :balance)');
        $insertAsset->execute([
            ':user' => $userId,
            ':coin' => $coinId,
            ':balance' => $newBalance,
        ]);
    }

    // Create transaction record (using the transactions table structure from node spacedebugger)
    $insertTx = $db->prepare(
        'INSERT INTO transactions (user_id, coin_id, asset_symbol, amount, fee, recipient, status, type)
         VALUES (:user, :coin, :symbol, :amount, :fee, :recipient, :status, :type)'
    );
    $insertTx->execute([
        ':user' => $userId,
        ':coin' => $coinId,
        ':symbol' => $coin['symbol'] ?? strtoupper(substr($coinKey, 0, 3)),
        ':amount' => $amount,
        ':fee' => $fee,
        ':recipient' => $recipient,
        ':status' => 'completed',
        ':type' => 'send',
    ]);

    $db->commit();

    send_json([
        'success' => true,
        'message' => 'Transaction processed successfully',
        'balance' => $newBalance,
    ]);
} catch (Exception $exception) {
    $db->rollBack();
    error_log('Send transaction failed: ' . $exception->getMessage());
    send_json(['success' => false, 'message' => 'Failed to process transaction'], 500);
}
