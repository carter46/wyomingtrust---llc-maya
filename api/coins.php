<?php
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Deposit-ready coins = catalog coins that have at least one wallet_addresses row
 * (same identity path as receive.php / deposit-submissions).
 * Query: ?for=depositable
 */
$forDepositable = isset($_GET['for']) && strtolower((string) $_GET['for']) === 'depositable';

try {
    $db = getDatabase();

    if ($forDepositable) {
        $coins = $db->query(
            'SELECT c.id, c.coin_key, c.display_name, c.symbol, c.default_balance, c.logo
             FROM coins c
             WHERE EXISTS (
                 SELECT 1 FROM wallet_addresses wa WHERE wa.coin_id = c.id
             )
             ORDER BY c.display_name ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $coins = $db->query(
            'SELECT id, coin_key, display_name, symbol, default_balance, logo
             FROM coins
             ORDER BY display_name ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($coins as &$coin) {
        $coin['id'] = (int) ($coin['id'] ?? 0);
        $coin['default_balance'] = (float) ($coin['default_balance'] ?? 0);
        $coin['coin_key'] = (string) ($coin['coin_key'] ?? '');
        $coin['display_name'] = (string) ($coin['display_name'] ?? '');
        $coin['symbol'] = (string) ($coin['symbol'] ?? '');
        $coin['logo'] = $coin['logo'] !== null ? (string) $coin['logo'] : null;
    }
    unset($coin);

    send_json(['success' => true, 'coins' => $coins, 'for' => $forDepositable ? 'depositable' : 'all']);
} catch (Throwable $e) {
    error_log('[coins.php] ' . $e->getMessage());
    send_json([
        'success' => false,
        'message' => 'Unable to load cryptocurrencies right now.',
        'coins' => [],
    ], 500);
}
