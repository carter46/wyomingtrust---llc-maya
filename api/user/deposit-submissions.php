<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListDepositSubmissions();
        break;
    case 'POST':
        handleCreateDepositSubmission();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListDepositSubmissions() {
    $userId = require_user_auth();
    $db = getDatabase();

    $trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
    $coinKey = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';

    $sql = 'SELECT t.id, t.amount, t.status, t.trust_id, t.created_at, t.updated_at, t.transaction_data,
                   c.coin_key, c.display_name, c.symbol
            FROM transactions t
            INNER JOIN coins c ON c.id = t.coin_id
            WHERE t.user_id = :user_id AND t.type = "deposit"';
    $params = [':user_id' => $userId];

    if ($trustId > 0) {
        $sql .= ' AND t.trust_id = :trust_id';
        $params[':trust_id'] = $trustId;
    }
    if ($coinKey !== '') {
        $sql .= ' AND c.coin_key = :coin_key';
        $params[':coin_key'] = $coinKey;
    }

    $sql .= ' ORDER BY t.created_at DESC LIMIT 20';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['amount'] = (float) $row['amount'];
        $row['transaction_data'] = !empty($row['transaction_data'])
            ? (json_decode($row['transaction_data'], true) ?? [])
            : [];
    }

    send_json(['success' => true, 'submissions' => $rows]);
}

function handleCreateDepositSubmission() {
    $userId = require_user_auth();
    require_csrf_token();

    $trustId = isset($_POST['trust_id']) ? (int) $_POST['trust_id'] : 0;
    $coinKey = sanitize_text($_POST['coin_key'] ?? '');
    $txHash = sanitize_text($_POST['tx_hash'] ?? '');
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $amountUsd = isset($_POST['amount_usd']) ? (float) $_POST['amount_usd'] : 0;
    $depositAddress = sanitize_text($_POST['deposit_address'] ?? '');

    if ($coinKey === '') {
        send_json(['success' => false, 'message' => 'Coin is required'], 400);
    }
    if ($txHash === '') {
        send_json(['success' => false, 'message' => 'Transaction hash is required'], 400);
    }
    if ($amount <= 0) {
        send_json(['success' => false, 'message' => 'Amount must be greater than zero'], 400);
    }

    $db = getDatabase();

    if ($trustId > 0) {
        $trustStmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
        $trustStmt->execute([':id' => $trustId, ':user_id' => $userId]);
        if (!$trustStmt->fetch()) {
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }
    }

    $coinStmt = $db->prepare('SELECT id, symbol FROM coins WHERE coin_key = :coin_key LIMIT 1');
    $coinStmt->execute([':coin_key' => $coinKey]);
    $coin = $coinStmt->fetch(PDO::FETCH_ASSOC);
    if (!$coin) {
        send_json(['success' => false, 'message' => 'Coin not found'], 404);
    }
    $coinId = (int) $coin['id'];

    $addrStmt = $db->prepare(
        'SELECT wa.address FROM wallet_addresses wa WHERE wa.coin_id = :coin_id LIMIT 1'
    );
    $addrStmt->execute([':coin_id' => $coinId]);
    $addrRow = $addrStmt->fetch(PDO::FETCH_ASSOC);
    $configuredAddress = trim((string) ($addrRow['address'] ?? ''));
    if ($configuredAddress === '') {
        send_json(['success' => false, 'message' => 'Deposits are not available for this coin at this moment'], 400);
    }

    $pendingStmt = $db->prepare(
        'SELECT t.id, t.transaction_data FROM transactions t
         WHERE t.user_id = :user_id AND t.type = "deposit" AND t.status = "pending"'
    );
    $pendingStmt->execute([':user_id' => $userId]);
    foreach ($pendingStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existing = !empty($row['transaction_data'])
            ? (json_decode($row['transaction_data'], true) ?? [])
            : [];
        if (isset($existing['tx_hash']) && strcasecmp($existing['tx_hash'], $txHash) === 0) {
            send_json(['success' => false, 'message' => 'A pending deposit with this transaction hash already exists'], 400);
        }
    }

    $proofPath = null;
    $originalFilename = null;
    if (isset($_FILES['proof']) && is_uploaded_file($_FILES['proof']['tmp_name'])) {
        $file = $_FILES['proof'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            send_json(['success' => false, 'message' => 'Proof upload failed'], 400);
        }
        $maxSize = 10 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxSize) {
            send_json(['success' => false, 'message' => 'Proof file must be 10MB or smaller'], 400);
        }
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            send_json(['success' => false, 'message' => 'Allowed proof types: PDF, JPG, PNG, WEBP'], 400);
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/deposits/' . $userId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $safeName . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            send_json(['success' => false, 'message' => 'Failed to save proof file'], 500);
        }
        $proofPath = 'uploads/deposits/' . $userId . '/' . $filename;
        $originalFilename = $file['name'];
    }

    $transactionData = [
        'tx_hash' => $txHash,
        'deposit_address' => $depositAddress !== '' ? $depositAddress : $configuredAddress,
        'proof_path' => $proofPath,
        'proof_filename' => $originalFilename,
        'submitted_at' => date('c'),
        'amount_usd' => $amountUsd > 0 ? $amountUsd : null,
    ];

    $insert = $db->prepare(
        'INSERT INTO transactions (user_id, trust_id, coin_id, asset_symbol, amount, fee, recipient, status, type, transaction_data)
         VALUES (:user_id, :trust_id, :coin_id, :symbol, :amount, 0, NULL, "pending", "deposit", :transaction_data)'
    );
    $insert->execute([
        ':user_id' => $userId,
        ':trust_id' => $trustId > 0 ? $trustId : null,
        ':coin_id' => $coinId,
        ':symbol' => $coin['symbol'] ?? strtoupper(substr($coinKey, 0, 4)),
        ':amount' => $amount,
        ':transaction_data' => json_encode($transactionData),
    ]);

    send_json([
        'success' => true,
        'message' => 'Deposit submitted for review. An administrator will verify your payment shortly.',
        'submission' => [
            'id' => (int) $db->lastInsertId(),
            'status' => 'pending',
        ],
    ]);
}
