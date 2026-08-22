<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListCoins();
        break;
    case 'POST':
        handleCreateCoin();
        break;
    case 'PUT':
        handleUpdateCoin();
        break;
    case 'DELETE':
        handleDeleteCoin();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListCoins() {
    require_admin_auth();
    $db = getDatabase();
    $coins = $db->query('SELECT id, coin_key, display_name, symbol, default_balance, logo, is_default, created_at FROM coins ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert DECIMAL to float for JSON
    foreach ($coins as &$coin) {
        $coin['default_balance'] = (float) $coin['default_balance'];
        $coin['is_default'] = (bool) $coin['is_default'];
    }
    
    send_json(['success' => true, 'coins' => $coins]);
}

function handleCreateCoin() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();

    $coinKey = sanitize_text($payload['coin_key'] ?? '');
    $displayName = sanitize_text($payload['display_name'] ?? '');
    $symbol = sanitize_text($payload['symbol'] ?? '');
    $defaultBalance = isset($payload['default_balance']) ? (float) $payload['default_balance'] : 0.0;
    $logo = sanitize_text($payload['logo'] ?? '');

    if ($coinKey === '' || $displayName === '' || $symbol === '') {
        send_json(['success' => false, 'message' => 'Coin key, name, and symbol are required'], 400);
    }

    $db = getDatabase();

    $stmt = $db->prepare('INSERT INTO coins (coin_key, display_name, symbol, default_balance, logo, is_default) VALUES (:coin_key, :display_name, :symbol, :balance, :logo, 0)');

    try {
        $stmt->execute([
            ':coin_key' => $coinKey,
            ':display_name' => $displayName,
            ':symbol' => $symbol,
            ':balance' => $defaultBalance,
            ':logo' => $logo,
        ]);
    } catch (Exception $exception) {
        send_json(['success' => false, 'message' => 'Unable to create coin (possibly duplicate key).'], 400);
    }

    $coinId = (int) $db->lastInsertId();
    send_json(['success' => true, 'coin' => ['id' => $coinId]]);
}

function handleUpdateCoin() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();
    $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);

    if ($id <= 0) {
        send_json(['success' => false, 'message' => 'Coin id is required'], 400);
    }

    $fields = [];
    $params = [':id' => $id];

    foreach ([
        'coin_key' => 'coin_key',
        'display_name' => 'display_name',
        'symbol' => 'symbol',
        'default_balance' => 'default_balance',
        'logo' => 'logo',
    ] as $payloadKey => $column) {
        if (array_key_exists($payloadKey, $payload)) {
            if ($payloadKey === 'default_balance') {
                $fields[] = "$column = :$column";
                $params[":$column"] = (float) $payload[$payloadKey];
            } else {
                $fields[] = "$column = :$column";
                $params[":$column"] = sanitize_text($payload[$payloadKey]);
            }
        }
    }

    if (!$fields) {
        send_json(['success' => false, 'message' => 'No data to update'], 400);
    }

    $sql = 'UPDATE coins SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $db = getDatabase();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    send_json(['success' => true, 'message' => 'Coin updated']);
}

function handleDeleteCoin() {
    require_admin_auth();
    require_csrf_token();
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        send_json(['success' => false, 'message' => 'Coin id is required'], 400);
    }

    $db = getDatabase();
    
    // Check if coin is default
    $check = $db->prepare('SELECT is_default FROM coins WHERE id = :id LIMIT 1');
    $check->execute([':id' => $id]);
    $coin = $check->fetch();
    
    if (!$coin) {
        send_json(['success' => false, 'message' => 'Coin not found'], 404);
    }
    
    if ($coin['is_default'] == 1) {
        send_json(['success' => false, 'message' => 'Cannot delete default coins'], 400);
    }
    
    $stmt = $db->prepare('DELETE FROM coins WHERE id = :id AND is_default = 0');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        send_json(['success' => false, 'message' => 'Coin not found or cannot be deleted'], 400);
    }

    send_json(['success' => true, 'message' => 'Coin deleted']);
}
