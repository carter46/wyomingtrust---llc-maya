<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = getDatabase();
    $coins = $db->query(
        'SELECT id, coin_key, display_name, symbol, default_balance, logo
         FROM coins
         ORDER BY display_name ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($coins as &$coin) {
        $coin['id'] = (int) ($coin['id'] ?? 0);
        $coin['default_balance'] = (float) ($coin['default_balance'] ?? 0);
        $coin['coin_key'] = (string) ($coin['coin_key'] ?? '');
        $coin['display_name'] = (string) ($coin['display_name'] ?? '');
        $coin['symbol'] = (string) ($coin['symbol'] ?? '');
        $coin['logo'] = $coin['logo'] !== null ? (string) $coin['logo'] : null;
    }
    unset($coin);

    send_json(['success' => true, 'coins' => $coins]);
} catch (Throwable $e) {
    error_log('[coins.php] ' . $e->getMessage());
    send_json([
        'success' => false,
        'message' => 'Unable to load cryptocurrencies right now.',
        'coins' => [],
    ], 500);
}
