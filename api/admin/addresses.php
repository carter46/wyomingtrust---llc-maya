<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListAddresses();
        break;
    case 'POST':
        handleCreateAddress();
        break;
    case 'PUT':
        handleUpdateAddress();
        break;
    case 'DELETE':
        handleDeleteAddress();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListAddresses() {
    require_admin_auth();
    $db = getDatabase();
    $stmt = $db->query(
        'SELECT wa.id, wa.address, wa.coin_id, wa.created_at, c.coin_key, c.display_name, c.symbol
         FROM wallet_addresses wa
         INNER JOIN coins c ON c.id = wa.coin_id
         ORDER BY c.display_name'
    );

    send_json(['success' => true, 'addresses' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function handleCreateAddress() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();
    $coinId = isset($payload['coin_id']) ? (int) $payload['coin_id'] : 0;
    $address = sanitize_text($payload['address'] ?? '');

    if ($coinId <= 0 || $address === '') {
        send_json(['success' => false, 'message' => 'Coin and address are required'], 400);
    }

    $db = getDatabase();
    
    // Check if coin exists
    $checkCoin = $db->prepare('SELECT id FROM coins WHERE id = :coin_id LIMIT 1');
    $checkCoin->execute([':coin_id' => $coinId]);
    if (!$checkCoin->fetch()) {
        send_json(['success' => false, 'message' => 'Coin not found'], 404);
    }
    
    try {
        $stmt = $db->prepare('INSERT INTO wallet_addresses (coin_id, address) VALUES (:coin_id, :address)');
        $stmt->execute([
            ':coin_id' => $coinId,
            ':address' => $address,
        ]);
    } catch (PDOException $e) {
        // Check if it's a duplicate key error
        if ($e->getCode() == 23000) {
            send_json(['success' => false, 'message' => 'Address already exists for this coin'], 400);
        }
        send_json(['success' => false, 'message' => 'Unable to create address'], 400);
    }

    send_json(['success' => true, 'address' => ['id' => (int) $db->lastInsertId()]]);
}

function handleUpdateAddress() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();
    $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);

    if ($id <= 0) {
        send_json(['success' => false, 'message' => 'Address id is required'], 400);
    }

    $fields = [];
    $params = [':id' => $id];

    if (isset($payload['coin_id'])) {
        $fields[] = 'coin_id = :coin_id';
        $params[':coin_id'] = (int) $payload['coin_id'];
    }
    if (isset($payload['address'])) {
        $fields[] = 'address = :address';
        $params[':address'] = sanitize_text($payload['address']);
    }

    if (!$fields) {
        send_json(['success' => false, 'message' => 'No data to update'], 400);
    }

    $sql = 'UPDATE wallet_addresses SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            send_json(['success' => false, 'message' => 'Address already exists for this coin'], 400);
        }
        send_json(['success' => false, 'message' => 'Unable to update address'], 400);
    }

    // Sync linked crypto payment methods + regenerate QR
    $rowStmt = $db->prepare(
        'SELECT wa.id, wa.address, wa.coin_id, c.coin_key, c.display_name, c.symbol
         FROM wallet_addresses wa
         INNER JOIN coins c ON c.id = wa.coin_id
         WHERE wa.id = :id
         LIMIT 1'
    );
    $rowStmt->execute([':id' => $id]);
    $row = $rowStmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        sync_payment_methods_for_wallet_address($db, $id, (string) $row['address'], [
            'coin_id' => (int) $row['coin_id'],
            'coin_key' => $row['coin_key'],
            'display_name' => $row['display_name'],
            'symbol' => $row['symbol'],
        ]);
    }

    send_json(['success' => true, 'message' => 'Address updated']);
}

function handleDeleteAddress() {
    require_admin_auth();
    require_csrf_token();
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        send_json(['success' => false, 'message' => 'Address id is required'], 400);
    }

    $db = getDatabase();
    $linked = count_payment_methods_for_wallet_address($db, $id);
    if ($linked > 0) {
        send_json([
            'success' => false,
            'message' => 'Cannot delete: this wallet address is linked to ' . $linked . ' crypto payment method(s). Remove those payment methods first.',
        ], 400);
    }

    $stmt = $db->prepare('DELETE FROM wallet_addresses WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        send_json(['success' => false, 'message' => 'Address not found'], 404);
    }

    send_json(['success' => true, 'message' => 'Address deleted']);
}
