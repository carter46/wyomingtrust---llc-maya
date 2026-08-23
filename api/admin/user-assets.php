<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListUserAssets();
        break;
    case 'POST':
        handleAdjustAsset();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListUserAssets() {
    require_admin_auth();
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    if ($userId <= 0) {
        send_json(['success' => false, 'message' => 'User id is required'], 400);
    }

    $trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
    $db = getDatabase();

    $trustsStmt = $db->prepare(
        'SELECT ut.id, ut.trust_data, ts.service_key, ts.service_name
         FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.user_id = :user
         ORDER BY ut.created_at DESC'
    );
    $trustsStmt->execute([':user' => $userId]);
    $trustRows = $trustsStmt->fetchAll(PDO::FETCH_ASSOC);

    $cryptoTrusts = [];
    foreach ($trustRows as $row) {
        if (!is_crypto_trust_type($row['service_key'] ?? '')) {
            continue;
        }
        $trustData = !empty($row['trust_data']) ? (json_decode($row['trust_data'], true) ?? []) : [];
        $cryptoTrusts[] = [
            'id' => (int) $row['id'],
            'trust_name' => $trustData['trust_name'] ?? ($row['service_name'] ?? 'Trust'),
            'entrusted_coins' => $trustData['entrusted_coins'] ?? [],
        ];
    }

    $entrustedKeys = null;
    if ($trustId > 0) {
        $found = false;
        foreach ($cryptoTrusts as $t) {
            if ((int) $t['id'] === $trustId) {
                $entrustedKeys = normalize_entrusted_coin_keys($t['entrusted_coins'] ?? []);
                $found = true;
                break;
            }
        }
        if (!$found) {
            send_json(['success' => false, 'message' => 'Trust not found for this user'], 404);
        }
    } else {
        $entrustedKeys = [];
        foreach ($cryptoTrusts as $t) {
            $entrustedKeys = array_merge($entrustedKeys, normalize_entrusted_coin_keys($t['entrusted_coins'] ?? []));
        }
        $entrustedKeys = array_values(array_unique($entrustedKeys));
    }

    if (count($entrustedKeys) === 0) {
        send_json([
            'success' => true,
            'assets' => [],
            'trusts' => $cryptoTrusts,
            'entrusted_coins' => [],
            'trust_id' => $trustId > 0 ? $trustId : null,
        ]);
    }

    $placeholders = implode(',', array_fill(0, count($entrustedKeys), '?'));
    $sql = "SELECT c.id AS coin_id, c.display_name, c.symbol, c.coin_key,
                   COALESCE(ua.id, 0) AS id, COALESCE(ua.balance, 0) AS balance
            FROM coins c
            LEFT JOIN user_assets ua ON ua.coin_id = c.id AND ua.user_id = ?
            WHERE LOWER(c.coin_key) IN ($placeholders)
            ORDER BY c.display_name";
    $stmt = $db->prepare($sql);
    $params = array_merge([$userId], $entrustedKeys);
    $stmt->execute($params);

    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($assets as &$asset) {
        $asset['balance'] = (float) $asset['balance'];
        $asset['coin_id'] = (int) $asset['coin_id'];
    }
    
    send_json([
        'success' => true,
        'assets' => $assets,
        'trusts' => $cryptoTrusts,
        'entrusted_coins' => $entrustedKeys ?? [],
        'trust_id' => $trustId > 0 ? $trustId : null,
    ]);
}

function handleAdjustAsset() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();
    $userId = isset($payload['user_id']) ? (int) $payload['user_id'] : 0;
    $coinId = isset($payload['coin_id']) ? (int) $payload['coin_id'] : 0;
    $type = sanitize_text($payload['type'] ?? '');
    $amount = isset($payload['amount']) ? (float) $payload['amount'] : 0.0;

    if ($userId <= 0 || $coinId <= 0 || $amount <= 0) {
        send_json(['success' => false, 'message' => 'Invalid data provided'], 400);
    }

    if (!in_array($type, ['credit', 'debit'], true)) {
        send_json(['success' => false, 'message' => 'Invalid transaction type'], 400);
    }

    $db = getDatabase();

    if (!admin_user_has_entrusted_coin($db, $userId, $coinId)) {
        send_json(['success' => false, 'message' => 'This coin is not in the user\'s selected portfolio'], 403);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT balance FROM user_assets WHERE user_id = :user AND coin_id = :coin LIMIT 1');
        $stmt->execute([
            ':user' => $userId,
            ':coin' => $coinId,
        ]);
        $asset = $stmt->fetch();

        if (!$asset) {
            // Create asset record if it doesn't exist
            $insert = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, 0)');
            $insert->execute([':user' => $userId, ':coin' => $coinId]);
            $currentBalance = 0.0;
        } else {
            $currentBalance = (float) $asset['balance'];
        }

        $newBalance = $type === 'credit' ? $currentBalance + $amount : $currentBalance - $amount;
        if ($newBalance < 0) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Insufficient balance for debit'], 400);
        }

        $update = $db->prepare('UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin');
        $update->execute([
            ':balance' => $newBalance,
            ':user' => $userId,
            ':coin' => $coinId,
        ]);
        
        // Create transaction record for admin adjustment
        $coinStmt = $db->prepare('SELECT symbol FROM coins WHERE id = :coin_id LIMIT 1');
        $coinStmt->execute([':coin_id' => $coinId]);
        $coin = $coinStmt->fetch();
        
        $insertTx = $db->prepare(
            'INSERT INTO transactions (user_id, coin_id, asset_symbol, amount, fee, recipient, status, type, metadata)
             VALUES (:user, :coin, :symbol, :amount, 0, NULL, :status, :type, :metadata)'
        );
        $insertTx->execute([
            ':user' => $userId,
            ':coin' => $coinId,
            ':symbol' => $coin['symbol'] ?? 'CRYPTO',
            ':amount' => $amount,
            ':status' => 'completed',
            ':type' => $type === 'credit' ? 'admin_credit' : 'admin_debit',
            ':metadata' => json_encode(['admin_adjusted' => true, 'previous_balance' => $currentBalance, 'new_balance' => $newBalance]),
        ]);

        $db->commit();
    } catch (Exception $exception) {
        $db->rollBack();
        error_log('Admin asset adjustment failed: ' . $exception->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update balance'], 500);
    }

    send_json(['success' => true, 'message' => 'Balance updated', 'balance' => $newBalance]);
}

function normalize_entrusted_coin_keys(array $keys): array {
    $normalized = [];
    foreach ($keys as $key) {
        $k = strtolower(trim((string) $key));
        if ($k !== '') {
            $normalized[] = $k;
        }
    }
    return array_values(array_unique($normalized));
}

function get_user_entrusted_coin_keys(PDO $db, int $userId): array {
    $trustsStmt = $db->prepare(
        'SELECT ut.trust_data, ts.service_key
         FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.user_id = :user'
    );
    $trustsStmt->execute([':user' => $userId]);
    $rows = $trustsStmt->fetchAll(PDO::FETCH_ASSOC);

    $keys = [];
    foreach ($rows as $row) {
        if (!is_crypto_trust_type($row['service_key'] ?? '')) {
            continue;
        }
        $trustData = !empty($row['trust_data']) ? (json_decode($row['trust_data'], true) ?? []) : [];
        $keys = array_merge($keys, normalize_entrusted_coin_keys($trustData['entrusted_coins'] ?? []));
    }

    return array_values(array_unique($keys));
}

function admin_user_has_entrusted_coin(PDO $db, int $userId, int $coinId): bool {
    $coinStmt = $db->prepare('SELECT LOWER(coin_key) AS coin_key FROM coins WHERE id = :id LIMIT 1');
    $coinStmt->execute([':id' => $coinId]);
    $coinKey = (string) ($coinStmt->fetchColumn() ?: '');
    if ($coinKey === '') {
        return false;
    }

    return in_array($coinKey, get_user_entrusted_coin_keys($db, $userId), true);
}
