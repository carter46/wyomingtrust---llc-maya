<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();
$userId = require_user_auth();
$db = getDatabase();

function load_user_trust_row(PDO $db, int $userId, int $trustId): ?array {
    $stmt = $db->prepare(
        'SELECT ut.id, ut.trust_data, ut.status, ts.service_key, ts.service_name
         FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.id = :id AND ut.user_id = :user_id
         LIMIT 1'
    );
    $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    $row['trust_data'] = !empty($row['trust_data'])
        ? (json_decode($row['trust_data'], true) ?? [])
        : [];
    return $row;
}

function load_user_catalog_trust(PDO $db, int $userId, int $trustId): ?array {
    $row = load_user_trust_row($db, $userId, $trustId);
    if (!$row || !trust_type_supports_asset_catalog($row['service_key'] ?? '')) {
        return null;
    }
    return $row;
}

function save_trust_data(PDO $db, int $trustId, int $userId, array $trustData): void {
    $stmt = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        ':trust_data' => json_encode($trustData, JSON_UNESCAPED_UNICODE),
        ':id' => $trustId,
        ':user_id' => $userId,
    ]);
}

function user_has_pending_asset_funding(PDO $db, int $userId, int $trustId, string $purpose, ?string $assetId = null): ?array {
    $stmt = $db->prepare(
        'SELECT id, status, amount, transaction_data
         FROM transactions
         WHERE user_id = :user_id
           AND trust_id = :trust_id
           AND type = "asset_funding"
           AND status IN ("pending", "completed")
         ORDER BY created_at DESC'
    );
    $stmt->execute([':user_id' => $userId, ':trust_id' => $trustId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $data = !empty($row['transaction_data'])
            ? (json_decode($row['transaction_data'], true) ?? [])
            : [];
        if (($data['purpose'] ?? '') !== $purpose) {
            continue;
        }
        if ($purpose === 'catalog_asset' && $assetId !== null && ($data['asset_id'] ?? '') !== $assetId) {
            continue;
        }
        return $row;
    }

    return null;
}

function submit_checkout_payment(
    PDO $db,
    int $userId,
    int $trustId,
    float $amountUsd,
    int $paymentMethodId,
    array $transactionData,
    ?int $coinId = null,
    string $assetSymbol = 'USD'
): int {
    $pmStmt = $db->prepare(
        'SELECT id, method_type, method_name FROM payment_methods WHERE id = :id AND is_active = 1 LIMIT 1'
    );
    $pmStmt->execute([':id' => $paymentMethodId]);
    $paymentMethod = $pmStmt->fetch(PDO::FETCH_ASSOC);
    if (!$paymentMethod) {
        throw new RuntimeException('Payment method not found');
    }

    $transactionData['payment_method_id'] = (int) $paymentMethod['id'];
    $transactionData['payment_method_type'] = $paymentMethod['method_type'];
    $transactionData['payment_method_name'] = $paymentMethod['method_name'];
    $transactionData['amount_usd'] = round($amountUsd, 2);
    $transactionData['user_confirmed_at'] = date('c');

    $insert = $db->prepare(
        'INSERT INTO transactions (user_id, trust_id, coin_id, asset_symbol, amount, fee, status, type, transaction_data)
         VALUES (:user, :trust_id, :coin, :symbol, :amount, 0, "pending", :type, :transaction_data)'
    );
    $insert->execute([
        ':user' => $userId,
        ':trust_id' => $trustId,
        ':coin' => $coinId,
        ':symbol' => $assetSymbol,
        ':amount' => round($amountUsd, 2),
        ':type' => $transactionData['checkout_type'] ?? 'asset_funding',
        ':transaction_data' => json_encode($transactionData),
    ]);

    return (int) $db->lastInsertId();
}

if ($method === 'GET') {
    $type = sanitize_text($_GET['type'] ?? '');
    $trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
    $coinKey = sanitize_text($_GET['coin_key'] ?? '');
    $assetId = sanitize_text($_GET['asset_id'] ?? '');

    if ($type === 'liquidation') {
        if ($coinKey === '') {
            send_json(['success' => false, 'message' => 'coin_key is required'], 400);
        }

        $coinStmt = $db->prepare('SELECT id, coin_key, display_name, symbol FROM coins WHERE coin_key = :coin_key LIMIT 1');
        $coinStmt->execute([':coin_key' => $coinKey]);
        $coin = $coinStmt->fetch(PDO::FETCH_ASSOC);
        if (!$coin) {
            send_json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        if ($trustId > 0) {
            $trustStmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
            $trustStmt->execute([':id' => $trustId, ':user_id' => $userId]);
            if (!$trustStmt->fetch()) {
                send_json(['success' => false, 'message' => 'Trust not found'], 404);
            }
        }

        $feeInfo = resolve_liquidation_fee_usd($db, $userId, $coinKey, $trustId);
        $coinId = (int) $coin['id'];
        $existingPayment = user_has_liquidation_fee_payment($db, $userId, $coinId, $trustId, false);
        $approvedPayment = user_has_liquidation_fee_payment($db, $userId, $coinId, $trustId, true);

        send_json([
            'success' => true,
            'type' => 'liquidation',
            'title' => 'Liquidation Fee Checkout',
            'description' => 'Pay the platform liquidation fee before submitting your crypto liquidation request.',
            'purpose_label' => 'Liquidation Fee',
            'item_label' => $coin['display_name'] ?? $coin['symbol'] ?? $coinKey,
            'coin_key' => $coinKey,
            'trust_id' => $trustId > 0 ? $trustId : null,
            'fee' => $feeInfo['fee'],
            'amount' => $feeInfo['fee'],
            'has_fee' => $feeInfo['has_fee'],
            'fee_paid' => $approvedPayment !== null,
            'payment_satisfied' => $approvedPayment !== null,
            'already_submitted' => $existingPayment !== null,
            'payment_status' => $existingPayment['status'] ?? null,
            'fee_payment_status' => $existingPayment['status'] ?? null,
            'continue_url' => $trustId > 0
                ? "send.php?mode=liquidate&coin_key={$coinKey}&trust_id={$trustId}"
                : "send.php?mode=liquidate&coin_key={$coinKey}",
        ]);
    }

    if ($type === 'trust_liquidation') {
        if ($trustId <= 0) {
            send_json(['success' => false, 'message' => 'trust_id is required'], 400);
        }

        $trust = load_user_trust_row($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }

        $feeInfo = resolve_trust_liquidation_fee_usd($db, $userId, $trustId);
        if (!$feeInfo['allows_liquidation']) {
            send_json(['success' => false, 'message' => 'This LLC cannot be liquidated'], 403);
        }

        $existingPayment = user_has_trust_liquidation_fee_payment($db, $userId, $trustId, false);
        $approvedPayment = user_has_trust_liquidation_fee_payment($db, $userId, $trustId, true);
        $trustName = $trust['trust_data']['trust_name'] ?? $trust['service_name'] ?? 'Trust';

        send_json([
            'success' => true,
            'type' => 'trust_liquidation',
            'title' => 'Trust Liquidation Fee Checkout',
            'description' => 'Pay the trust liquidation fee before your liquidation request can be processed.',
            'purpose_label' => 'Trust Liquidation Fee',
            'item_label' => $trustName,
            'trust_id' => $trustId,
            'amount' => $feeInfo['fee'],
            'has_fee' => $feeInfo['has_fee'],
            'fee_paid' => $approvedPayment !== null,
            'payment_satisfied' => $approvedPayment !== null,
            'already_submitted' => $existingPayment !== null,
            'payment_status' => $existingPayment['status'] ?? null,
            'continue_url' => "manage-trust.php?id={$trustId}",
        ]);
    }

    if ($type === 'asset_funding') {
        if ($trustId <= 0 || $assetId === '') {
            send_json(['success' => false, 'message' => 'trust_id and asset_id are required'], 400);
        }

        $trust = load_user_catalog_trust($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found or does not support catalog assets'], 404);
        }

        $assets = is_array($trust['trust_data']['assets'] ?? null) ? $trust['trust_data']['assets'] : [];
        $index = find_trust_asset_index($assets, $assetId);
        if ($index === null) {
            send_json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        $asset = $assets[$index];
        $amount = (float) ($asset['funding_amount_usd'] ?? get_trust_asset_usd_value($asset));
        if ($amount <= 0) {
            send_json(['success' => false, 'message' => 'This asset does not require a cash deposit'], 400);
        }

        $status = sanitize_text($asset['funding_status'] ?? 'unfunded');
        $existing = user_has_pending_asset_funding($db, $userId, $trustId, 'catalog_asset', $assetId);

        send_json([
            'success' => true,
            'type' => 'asset_funding',
            'title' => 'Asset Deposit Checkout',
            'description' => 'Deposit the cash value of this LLC asset. An administrator will verify your payment before the asset value is added to your LLC.',
            'purpose_label' => 'Asset Value Deposit',
            'item_label' => $asset['label'] ?? 'Trust Asset',
            'trust_id' => $trustId,
            'asset_id' => $assetId,
            'amount' => round($amount, 2),
            'funding_status' => $status,
            'payment_satisfied' => $status === 'funded',
            'already_submitted' => $existing !== null,
            'payment_status' => $existing['status'] ?? null,
            'continue_url' => "manage-trust.php?id={$trustId}",
        ]);
    }

    if ($type === 'trust_value') {
        if ($trustId <= 0) {
            send_json(['success' => false, 'message' => 'trust_id is required'], 400);
        }

        $trust = load_user_catalog_trust($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found or does not support catalog assets'], 404);
        }

        $trustData = $trust['trust_data'];
        if (!trust_declared_value_funding_applies($trustData)) {
            send_json([
                'success' => false,
                'message' => 'This LLC uses per-asset deposits. Fund each asset individually from LLC Management.',
            ], 400);
        }

        $funding = get_trust_declared_value_funding($trustData);
        $amount = (float) ($funding['amount_usd'] ?? 0);
        if ($amount <= 0) {
            send_json(['success' => false, 'message' => 'No declared trust value requires funding'], 400);
        }

        $existing = user_has_pending_asset_funding($db, $userId, $trustId, 'trust_declared_value');
        $isFunded = ($funding['status'] ?? '') === 'funded';

        send_json([
            'success' => true,
            'type' => 'trust_value',
            'title' => 'Trust Value Deposit Checkout',
            'description' => 'Deposit the declared total asset value for your LLC. An administrator will verify your payment before the value is credited.',
            'purpose_label' => 'Declared Trust Value Deposit',
            'item_label' => $trust['trust_data']['trust_name'] ?? $trust['service_name'] ?? 'Trust',
            'trust_id' => $trustId,
            'amount' => round($amount, 2),
            'funding_status' => $funding['status'] ?? 'unfunded',
            'payment_satisfied' => $isFunded,
            'already_submitted' => $existing !== null,
            'payment_status' => $existing['status'] ?? null,
            'continue_url' => "manage-trust.php?id={$trustId}",
        ]);
    }

    send_json(['success' => false, 'message' => 'Invalid checkout request'], 400);
}

if ($method === 'POST') {
    require_csrf_token();
    $payload = get_json_input();

    $type = sanitize_text($payload['type'] ?? '');
    $trustId = isset($payload['trust_id']) ? (int) $payload['trust_id'] : 0;
    $coinKey = sanitize_text($payload['coin_key'] ?? '');
    $assetId = sanitize_text($payload['asset_id'] ?? '');
    $paymentMethodId = isset($payload['payment_method_id']) ? (int) $payload['payment_method_id'] : 0;

    if ($paymentMethodId <= 0) {
        send_json(['success' => false, 'message' => 'Payment method is required'], 400);
    }

    if ($type === 'liquidation') {
        if ($coinKey === '') {
            send_json(['success' => false, 'message' => 'coin_key is required'], 400);
        }

        $coinStmt = $db->prepare('SELECT id, symbol FROM coins WHERE coin_key = :coin_key LIMIT 1');
        $coinStmt->execute([':coin_key' => $coinKey]);
        $coin = $coinStmt->fetch(PDO::FETCH_ASSOC);
        if (!$coin) {
            send_json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        if ($trustId > 0) {
            $trustStmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
            $trustStmt->execute([':id' => $trustId, ':user_id' => $userId]);
            if (!$trustStmt->fetch()) {
                send_json(['success' => false, 'message' => 'Trust not found'], 404);
            }
        }

        $feeInfo = resolve_liquidation_fee_usd($db, $userId, $coinKey, $trustId);
        if (!$feeInfo['has_fee']) {
            send_json(['success' => false, 'message' => 'No liquidation fee is required for this asset'], 400);
        }

        $coinId = (int) $coin['id'];
        if (user_has_liquidation_fee_payment($db, $userId, $coinId, $trustId, false)) {
            send_json(['success' => false, 'message' => 'Liquidation fee payment has already been submitted'], 409);
        }

        $db->beginTransaction();
        try {
            $transactionId = submit_checkout_payment(
                $db,
                $userId,
                $trustId,
                $feeInfo['fee'],
                $paymentMethodId,
                [
                    'checkout_type' => 'liquidation_fee',
                    'purpose' => 'liquidation_fee',
                    'coin_key' => $coinKey,
                    'fee_source' => $feeInfo['fee_source'],
                ],
                $coinId,
                $coin['symbol'] ?? strtoupper(substr($coinKey, 0, 3))
            );
            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Liquidation fee payment submitted for admin approval.',
                'transaction_id' => $transactionId,
                'amount' => $feeInfo['fee'],
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Liquidation checkout failed: ' . $e->getMessage());
            send_json(['success' => false, 'message' => 'Failed to submit payment'], 500);
        }
    }

    if ($type === 'trust_liquidation') {
        if ($trustId <= 0) {
            send_json(['success' => false, 'message' => 'trust_id is required'], 400);
        }

        $trust = load_user_trust_row($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }

        $feeInfo = resolve_trust_liquidation_fee_usd($db, $userId, $trustId);
        if (!$feeInfo['allows_liquidation']) {
            send_json(['success' => false, 'message' => 'This LLC cannot be liquidated'], 403);
        }
        if (!$feeInfo['has_fee']) {
            send_json(['success' => false, 'message' => 'No liquidation fee is required for this LLC'], 400);
        }
        if (user_has_trust_liquidation_fee_payment($db, $userId, $trustId, false)) {
            send_json(['success' => false, 'message' => 'Liquidation fee payment has already been submitted'], 409);
        }

        $db->beginTransaction();
        try {
            $transactionId = submit_checkout_payment(
                $db,
                $userId,
                $trustId,
                $feeInfo['fee'],
                $paymentMethodId,
                [
                    'checkout_type' => 'liquidation_fee',
                    'purpose' => 'trust_liquidation',
                    'trust_name' => $trust['trust_data']['trust_name'] ?? $trust['service_name'] ?? '',
                ],
                null,
                'USD'
            );
            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Trust liquidation fee submitted for admin approval.',
                'transaction_id' => $transactionId,
                'amount' => $feeInfo['fee'],
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Trust liquidation checkout failed: ' . $e->getMessage());
            send_json(['success' => false, 'message' => 'Failed to submit payment'], 500);
        }
    }

    if ($type === 'asset_funding') {
        if ($trustId <= 0 || $assetId === '') {
            send_json(['success' => false, 'message' => 'trust_id and asset_id are required'], 400);
        }

        $trust = load_user_catalog_trust($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }

        $trustData = $trust['trust_data'];
        $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
        $index = find_trust_asset_index($assets, $assetId);
        if ($index === null) {
            send_json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        $asset = $assets[$index];
        $amount = (float) ($asset['funding_amount_usd'] ?? get_trust_asset_usd_value($asset));
        if ($amount <= 0) {
            send_json(['success' => false, 'message' => 'This asset does not require a cash deposit'], 400);
        }

        if (($asset['funding_status'] ?? '') === 'funded') {
            send_json(['success' => false, 'message' => 'This asset has already been funded'], 409);
        }
        if (user_has_pending_asset_funding($db, $userId, $trustId, 'catalog_asset', $assetId)) {
            send_json(['success' => false, 'message' => 'A deposit for this asset is already pending'], 409);
        }

        $db->beginTransaction();
        try {
            $transactionId = submit_checkout_payment(
                $db,
                $userId,
                $trustId,
                $amount,
                $paymentMethodId,
                [
                    'checkout_type' => 'asset_funding',
                    'purpose' => 'catalog_asset',
                    'asset_id' => $assetId,
                    'asset_label' => $asset['label'] ?? '',
                    'category_key' => $asset['category_key'] ?? '',
                ],
                null,
                'USD'
            );

            $assets[$index]['funding_status'] = 'pending';
            $assets[$index]['funding_transaction_id'] = $transactionId;
            $trustData['assets'] = $assets;
            save_trust_data($db, $trustId, $userId, $trustData);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Asset deposit submitted for admin approval.',
                'transaction_id' => $transactionId,
                'amount' => round($amount, 2),
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Asset funding checkout failed: ' . $e->getMessage());
            send_json(['success' => false, 'message' => 'Failed to submit payment'], 500);
        }
    }

    if ($type === 'trust_value') {
        if ($trustId <= 0) {
            send_json(['success' => false, 'message' => 'trust_id is required'], 400);
        }

        $trust = load_user_catalog_trust($db, $userId, $trustId);
        if (!$trust) {
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }

        $trustData = $trust['trust_data'];
        if (!trust_declared_value_funding_applies($trustData)) {
            send_json([
                'success' => false,
                'message' => 'This LLC uses per-asset deposits. Fund each asset individually from LLC Management.',
            ], 400);
        }

        $funding = get_trust_declared_value_funding($trustData);
        $amount = (float) ($funding['amount_usd'] ?? 0);
        if ($amount <= 0) {
            send_json(['success' => false, 'message' => 'No declared trust value requires funding'], 400);
        }
        if (($funding['status'] ?? '') === 'funded') {
            send_json(['success' => false, 'message' => 'Declared trust value has already been funded'], 409);
        }
        if (user_has_pending_asset_funding($db, $userId, $trustId, 'trust_declared_value')) {
            send_json(['success' => false, 'message' => 'A deposit for this LLC value is already pending'], 409);
        }

        $db->beginTransaction();
        try {
            $transactionId = submit_checkout_payment(
                $db,
                $userId,
                $trustId,
                $amount,
                $paymentMethodId,
                [
                    'checkout_type' => 'asset_funding',
                    'purpose' => 'trust_declared_value',
                    'trust_name' => $trustData['trust_name'] ?? '',
                ],
                null,
                'USD'
            );

            $trustData['declared_value_funding'] = [
                'amount_usd' => round($amount, 2),
                'status' => 'pending',
                'funded_amount_usd' => 0.0,
                'transaction_id' => $transactionId,
            ];
            save_trust_data($db, $trustId, $userId, $trustData);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Trust value deposit submitted for admin approval.',
                'transaction_id' => $transactionId,
                'amount' => round($amount, 2),
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Trust value checkout failed: ' . $e->getMessage());
            send_json(['success' => false, 'message' => 'Failed to submit payment'], 500);
        }
    }

    send_json(['success' => false, 'message' => 'Invalid checkout payload'], 400);
}

send_json(['success' => false, 'message' => 'Method not allowed'], 405);
