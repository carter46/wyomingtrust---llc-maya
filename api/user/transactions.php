<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListTransactions();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListTransactions() {
    $userId = require_user_auth();
    $db = getDatabase();
    
    // Support coin_key filter
    $coinKey = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
    
    $sql = 'SELECT t.id, t.amount, t.fee, t.status, t.type, t.recipient, t.asset_symbol, t.metadata, t.transaction_data, t.created_at, t.updated_at,
                pm.method_type, pm.method_name,
                ut.id AS trust_id, ts.service_name,
                c.coin_key, c.display_name AS coin_name, c.symbol AS coin_symbol, c.logo AS coin_logo
         FROM transactions t
         LEFT JOIN payment_methods pm ON pm.id = t.payment_method_id
         LEFT JOIN user_trusts ut ON ut.id = t.trust_id
         LEFT JOIN trust_services ts ON ts.id = ut.trust_service_id
         LEFT JOIN coins c ON c.id = t.coin_id
         WHERE t.user_id = :user_id';
    
    $params = [':user_id' => $userId];
    
    if ($coinKey !== '') {
        $sql .= ' AND c.coin_key = :coin_key';
        $params[':coin_key'] = $coinKey;
    }
    
    $sql .= ' ORDER BY t.created_at DESC LIMIT 100';
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
    // Decode JSON fields
    foreach ($transactions as &$transaction) {
        if (!empty($transaction['metadata'])) {
            $transaction['metadata'] = json_decode($transaction['metadata'], true) ?? [];
        } else {
            $transaction['metadata'] = [];
        }
        if (!empty($transaction['transaction_data'])) {
            $transaction['transaction_data'] = json_decode($transaction['transaction_data'], true) ?? [];
        } else {
            $transaction['transaction_data'] = [];
        }
    }
    
    send_json(['success' => true, 'transactions' => $transactions]);
}
