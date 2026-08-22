<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = getDatabase();
    $stmt = $db->query(
        'SELECT wa.id, wa.address, wa.coin_id, c.coin_key, c.display_name
         FROM wallet_addresses wa
         INNER JOIN coins c ON c.id = wa.coin_id
         ORDER BY c.display_name'
    );

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format as map for easy lookup by coin_key (only non-empty addresses)
    $addressMap = [];
    foreach ($addresses as $addr) {
        $key = $addr['coin_key'] ?? '';
        $value = trim((string) ($addr['address'] ?? ''));
        if ($key !== '' && $value !== '') {
            $addressMap[$key] = $value;
        }
    }

    echo json_encode([
        'success' => true,
        'addresses' => $addresses,
        'addressMap' => $addressMap, // Convenience map: { "bitcoin": "1...", "ethereum": "0x..." }
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Exception $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load addresses'], JSON_UNESCAPED_UNICODE);
    exit;
}
