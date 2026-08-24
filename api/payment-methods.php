<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Public endpoint - no authentication required
$db = getDatabase();

// Get only active payment methods
$stmt = $db->query(
    'SELECT id, method_type, method_name, config_data, created_at
     FROM payment_methods
     WHERE is_active = 1
     ORDER BY 
         CASE method_type 
             WHEN "crypto" THEN 1 
             WHEN "bank_transfer" THEN 2 
             WHEN "paypal" THEN 3 
             ELSE 4 
         END,
         method_name'
);
$methods = $stmt->fetchAll();

// Decode JSON config_data and sanitize sensitive information
foreach ($methods as &$method) {
    if (!empty($method['config_data'])) {
        $config = json_decode($method['config_data'], true) ?? [];
        
        // For security, don't expose full account numbers, only last 4 digits if bank transfer
        if ($method['method_type'] === 'bank_transfer' && isset($config['account_number'])) {
            $accountNumber = $config['account_number'];
            if (strlen($accountNumber) > 4) {
                $config['account_number_masked'] = str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -4);
                unset($config['account_number']); // Remove full account number
            }
        }

        // Crypto: always resolve live address from wallet_addresses when linked
        if ($method['method_type'] === 'crypto') {
            $walletAddressId = isset($config['wallet_address_id']) ? (int) $config['wallet_address_id'] : 0;
            $coinId = isset($config['coin_id']) ? (int) $config['coin_id'] : 0;
            $live = null;
            if ($walletAddressId > 0) {
                $liveStmt = $db->prepare(
                    'SELECT wa.id, wa.address, wa.coin_id, c.coin_key, c.display_name, c.symbol
                     FROM wallet_addresses wa
                     INNER JOIN coins c ON c.id = wa.coin_id
                     WHERE wa.id = :id
                     LIMIT 1'
                );
                $liveStmt->execute([':id' => $walletAddressId]);
                $live = $liveStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } elseif ($coinId > 0) {
                $liveStmt = $db->prepare(
                    'SELECT wa.id, wa.address, wa.coin_id, c.coin_key, c.display_name, c.symbol
                     FROM wallet_addresses wa
                     INNER JOIN coins c ON c.id = wa.coin_id
                     WHERE wa.coin_id = :coin_id
                     LIMIT 1'
                );
                $liveStmt->execute([':coin_id' => $coinId]);
                $live = $liveStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            if ($live) {
                $config['wallet_address_id'] = (int) $live['id'];
                $config['coin_id'] = (int) $live['coin_id'];
                $config['coin_key'] = $live['coin_key'];
                $config['coin_name'] = $live['display_name'];
                $config['coin_symbol'] = $live['symbol'];
                $config['wallet_address'] = $live['address'];
                if (empty($config['network_type'])) {
                    $config['network_type'] = $live['symbol'] ?: $live['display_name'];
                }
            }
        }
        
        $method['config_data'] = $config;
    } else {
        $method['config_data'] = [];
    }
}

send_json(['success' => true, 'methods' => $methods]);
