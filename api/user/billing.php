<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

if ($method !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();
$db = getDatabase();

$hasPm = false;
try {
    $stmt = $db->prepare(
        'SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "user_trusts"
           AND COLUMN_NAME = "payment_method_id"'
    );
    $stmt->execute();
    $hasPm = ((int) $stmt->fetchColumn() > 0);
} catch (Exception $e) {
    $hasPm = false;
}

$sql = $hasPm
    ? 'SELECT ut.id AS trust_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
              ts.service_key, ts.service_name, ts.price, ts.is_free,
              pm.method_type AS payment_method_type, pm.method_name AS payment_method_name
       FROM user_trusts ut
       INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
       LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id
       WHERE ut.user_id = :user_id
       ORDER BY ut.created_at DESC'
    : 'SELECT ut.id AS trust_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
              ts.service_key, ts.service_name, ts.price, ts.is_free,
              NULL AS payment_method_type, NULL AS payment_method_name
       FROM user_trusts ut
       INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
       WHERE ut.user_id = :user_id
       ORDER BY ut.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$rows = $stmt->fetchAll();

$payments = [];
foreach ($rows as $row) {
    $trustData = [];
    if (!empty($row['trust_data'])) {
        $trustData = json_decode($row['trust_data'], true) ?? [];
    }

    $paymentInfo = is_array($trustData['payment_info'] ?? null) ? $trustData['payment_info'] : [];
    $isFree = !empty($row['is_free']) || (float) $row['price'] <= 0;
    $amount = isset($paymentInfo['amount']) ? (float) $paymentInfo['amount'] : (float) $row['price'];

    $payments[] = [
        'record_type' => 'trust_payment',
        'trust_id' => (int) $row['trust_id'],
        'service_name' => $row['service_name'],
        'service_key' => $row['service_key'],
        'amount' => $amount,
        'is_free' => $isFree,
        'payment_status' => $row['payment_status'],
        'trust_status' => $row['status'],
        'payment_method_name' => $row['payment_method_name'],
        'payment_method_type' => $row['payment_method_type'],
        'payment_type' => $paymentInfo['type'] ?? ($isFree ? 'free' : 'paid'),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

$depositStmt = $db->prepare(
    'SELECT t.id, t.amount, t.status, t.trust_id, t.created_at, t.updated_at, t.transaction_data,
            c.coin_key, c.display_name, c.symbol
     FROM transactions t
     INNER JOIN coins c ON c.id = t.coin_id
     WHERE t.user_id = :user_id AND t.type = "deposit"
     ORDER BY t.created_at DESC'
);
$depositStmt->execute([':user_id' => $userId]);
$depositRows = $depositStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($depositRows as $row) {
    $txData = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
    $amountUsd = isset($txData['amount_usd']) ? (float) $txData['amount_usd'] : 0.0;
    $coinAmount = (float) $row['amount'];
    $displayName = $row['display_name'] ?? $row['symbol'] ?? 'Cryptocurrency';

    $payments[] = [
        'record_type' => 'crypto_deposit',
        'transaction_id' => (int) $row['id'],
        'trust_id' => !empty($row['trust_id']) ? (int) $row['trust_id'] : null,
        'service_name' => 'Crypto Deposit — ' . $displayName,
        'service_key' => 'crypto_deposit',
        'coin_key' => $row['coin_key'],
        'coin_symbol' => $row['symbol'],
        'coin_amount' => $coinAmount,
        'amount' => $amountUsd > 0 ? $amountUsd : $coinAmount,
        'amount_usd' => $amountUsd,
        'is_free' => false,
        'payment_status' => $row['status'],
        'trust_status' => null,
        'payment_method_name' => 'Cryptocurrency',
        'payment_method_type' => 'crypto',
        'payment_type' => 'crypto_deposit',
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

$liquidationStmt = $db->prepare(
    'SELECT t.id, t.amount, t.fee, t.status, t.trust_id, t.recipient, t.created_at, t.updated_at, t.transaction_data,
            c.coin_key, c.display_name, c.symbol
     FROM transactions t
     INNER JOIN coins c ON c.id = t.coin_id
     WHERE t.user_id = :user_id AND t.type = "liquidation"
     ORDER BY t.created_at DESC'
);
$liquidationStmt->execute([':user_id' => $userId]);
$liquidationRows = $liquidationStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($liquidationRows as $row) {
    $txData = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
    $coinAmount = (float) $row['amount'];
    $feeAmount = (float) $row['fee'];
    $displayName = $row['display_name'] ?? $row['symbol'] ?? 'Cryptocurrency';
    $platformUsd = isset($txData['platform_fee_usd']) ? (float) $txData['platform_fee_usd'] : 0.0;

    $payments[] = [
        'record_type' => 'crypto_liquidation',
        'transaction_id' => (int) $row['id'],
        'trust_id' => !empty($row['trust_id']) ? (int) $row['trust_id'] : null,
        'service_name' => 'Crypto Liquidation — ' . $displayName,
        'service_key' => 'crypto_liquidation',
        'coin_key' => $row['coin_key'],
        'coin_symbol' => $row['symbol'],
        'coin_amount' => $coinAmount,
        'fee_amount' => $feeAmount,
        'amount' => $platformUsd > 0 ? $platformUsd : $coinAmount,
        'amount_usd' => $platformUsd,
        'is_free' => false,
        'payment_status' => $row['status'],
        'trust_status' => null,
        'payment_method_name' => 'Cryptocurrency',
        'payment_method_type' => 'crypto',
        'payment_type' => 'crypto_liquidation',
        'recipient' => $row['recipient'] ?? ($txData['recipient'] ?? null),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

$liquidationFeeStmt = $db->prepare(
    'SELECT t.id, t.amount, t.status, t.trust_id, t.created_at, t.updated_at, t.transaction_data,
            c.coin_key, c.display_name, c.symbol
     FROM transactions t
     LEFT JOIN coins c ON c.id = t.coin_id
     WHERE t.user_id = :user_id AND t.type = "liquidation_fee"
     ORDER BY t.created_at DESC'
);
$liquidationFeeStmt->execute([':user_id' => $userId]);
$liquidationFeeRows = $liquidationFeeStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($liquidationFeeRows as $row) {
    $txData = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
    $purpose = $txData['purpose'] ?? 'liquidation_fee';
    $displayName = $row['display_name'] ?? $row['symbol'] ?? null;
    if ($purpose === 'trust_liquidation') {
        $label = 'Trust Liquidation Fee — ' . ($txData['trust_name'] ?? 'Trust');
    } else {
        $label = 'Liquidation Fee — ' . ($displayName ?? 'Cryptocurrency');
    }
    $amountUsd = isset($txData['amount_usd']) ? (float) $txData['amount_usd'] : (float) $row['amount'];

    $payments[] = [
        'record_type' => 'liquidation_fee',
        'transaction_id' => (int) $row['id'],
        'trust_id' => !empty($row['trust_id']) ? (int) $row['trust_id'] : null,
        'service_name' => $label,
        'service_key' => 'liquidation_fee',
        'coin_key' => $row['coin_key'],
        'coin_symbol' => $row['symbol'],
        'amount' => $amountUsd,
        'amount_usd' => $amountUsd,
        'is_free' => false,
        'payment_status' => $row['status'],
        'trust_status' => null,
        'payment_method_name' => $txData['payment_method_name'] ?? 'Payment',
        'payment_method_type' => $txData['payment_method_type'] ?? null,
        'payment_type' => 'liquidation_fee',
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

$assetFundingStmt = $db->prepare(
    'SELECT t.id, t.amount, t.status, t.trust_id, t.created_at, t.updated_at, t.transaction_data
     FROM transactions t
     WHERE t.user_id = :user_id AND t.type = "asset_funding"
     ORDER BY t.created_at DESC'
);
$assetFundingStmt->execute([':user_id' => $userId]);
$assetFundingRows = $assetFundingStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($assetFundingRows as $row) {
    $txData = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
    $amountUsd = (float) $row['amount'];
    $purpose = $txData['purpose'] ?? 'asset_funding';
    $label = $purpose === 'trust_declared_value'
        ? ('Trust Value — ' . ($txData['trust_name'] ?? 'Trust'))
        : ('Asset Deposit — ' . ($txData['asset_label'] ?? 'Asset'));

    $payments[] = [
        'record_type' => 'asset_funding',
        'transaction_id' => (int) $row['id'],
        'trust_id' => !empty($row['trust_id']) ? (int) $row['trust_id'] : null,
        'service_name' => $label,
        'service_key' => 'asset_funding',
        'amount' => $amountUsd,
        'amount_usd' => $amountUsd,
        'is_free' => false,
        'payment_status' => $row['status'],
        'trust_status' => null,
        'payment_method_name' => $txData['payment_method_name'] ?? 'Payment',
        'payment_method_type' => $txData['payment_method_type'] ?? null,
        'payment_type' => 'asset_funding',
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

usort($payments, static function (array $a, array $b): int {
    return strtotime((string) ($b['created_at'] ?? '')) <=> strtotime((string) ($a['created_at'] ?? ''));
});

$lastPayment = null;
foreach ($payments as $payment) {
    if ($payment['record_type'] === 'crypto_deposit' || $payment['record_type'] === 'crypto_liquidation' || $payment['record_type'] === 'liquidation_fee' || $payment['record_type'] === 'asset_funding' || empty($payment['is_free'])) {
        $lastPayment = $payment;
        break;
    }
}

send_json([
    'success' => true,
    'payments' => $payments,
    'last_payment' => $lastPayment,
]);
