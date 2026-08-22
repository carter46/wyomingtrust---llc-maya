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
        
        $method['config_data'] = $config;
    } else {
        $method['config_data'] = [];
    }
}

send_json(['success' => true, 'methods' => $methods]);
