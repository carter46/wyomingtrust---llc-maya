<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();
$coinKey = sanitize_text($_GET['coin_key'] ?? '');
$trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;

if ($coinKey === '') {
    send_json(['success' => false, 'message' => 'coin_key is required'], 400);
}

$db = getDatabase();
$feeInfo = resolve_liquidation_fee_usd($db, $userId, $coinKey, $trustId);

send_json([
    'success' => true,
    'fee' => $feeInfo['fee'],
    'has_fee' => $feeInfo['has_fee'],
    'fee_source' => $feeInfo['fee_source'],
]);
