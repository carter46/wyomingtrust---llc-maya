<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$db = getDatabase();
$coins = $db->query('SELECT id, coin_key, display_name, symbol, default_balance, logo FROM coins ORDER BY display_name ASC')->fetchAll(PDO::FETCH_ASSOC);

// Convert DECIMAL to float for JSON
foreach ($coins as &$coin) {
    $coin['default_balance'] = (float) $coin['default_balance'];
}

send_json(['success' => true, 'coins' => $coins]);
