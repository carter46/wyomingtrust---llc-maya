<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

function user_trusts_has_payment_method_id_column($db) {
    static $cached = null;
    if ($cached !== null) return $cached;

    try {
        $stmt = $db->prepare(
            'SELECT COUNT(*) 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "user_trusts"
               AND COLUMN_NAME = "payment_method_id"'
        );
        $stmt->execute();
        $cached = ((int) $stmt->fetchColumn() > 0);
    } catch (Exception $e) {
        // If we can't introspect schema, assume "no" so API stays compatible.
        $cached = false;
    }

    return $cached;
}

switch ($method) {
    case 'GET':
        handleListPendingPayments();
        break;
    case 'PUT':
    case 'PATCH':
        $payload = get_json_input();
        if (!empty($payload['deposit_id'])) {
            handleApproveRejectDeposit($payload);
        } elseif (!empty($payload['liquidation_fee_id'])) {
            handleApproveRejectLiquidationFee($payload);
        } elseif (!empty($payload['asset_funding_id'])) {
            handleApproveRejectAssetFunding($payload);
        } elseif (!empty($payload['liquidation_id'])) {
            handleApproveRejectLiquidation($payload);
        } else {
            handleApproveRejectPayment($payload);
        }
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function decode_transaction_data_row(array &$row): void {
    $row['transaction_data'] = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
}

function credit_user_coin_balance(PDO $db, int $userId, int $coinId, float $amount): float {
    $assetStmt = $db->prepare('SELECT balance FROM user_assets WHERE user_id = :user AND coin_id = :coin LIMIT 1');
    $assetStmt->execute([':user' => $userId, ':coin' => $coinId]);
    $asset = $assetStmt->fetch(PDO::FETCH_ASSOC);

    $currentBalance = $asset ? (float) $asset['balance'] : 0.0;
    $newBalance = $currentBalance + $amount;

    if ($asset) {
        $update = $db->prepare(
            'UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin'
        );
        $update->execute([':balance' => $newBalance, ':user' => $userId, ':coin' => $coinId]);
    } else {
        $insert = $db->prepare('INSERT INTO user_assets (user_id, coin_id, balance) VALUES (:user, :coin, :balance)');
        $insert->execute([':user' => $userId, ':coin' => $coinId, ':balance' => $newBalance]);
    }

    return $newBalance;
}

function debit_user_coin_balance(PDO $db, int $userId, int $coinId, float $amount): float {
    $assetStmt = $db->prepare('SELECT balance FROM user_assets WHERE user_id = :user AND coin_id = :coin LIMIT 1');
    $assetStmt->execute([':user' => $userId, ':coin' => $coinId]);
    $asset = $assetStmt->fetch(PDO::FETCH_ASSOC);

    $currentBalance = $asset ? (float) $asset['balance'] : 0.0;
    if ($amount > $currentBalance) {
        throw new RuntimeException('Insufficient balance');
    }

    $newBalance = $currentBalance - $amount;
    $update = $db->prepare(
        'UPDATE user_assets SET balance = :balance, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user AND coin_id = :coin'
    );
    $update->execute([':balance' => $newBalance, ':user' => $userId, ':coin' => $coinId]);

    if ($update->rowCount() === 0) {
        throw new RuntimeException('User asset record not found');
    }

    return $newBalance;
}

function handleListPendingPayments() {
    require_admin_auth();
    $db = getDatabase();
    
    // Get all trusts with pending payments (paid services only)
    $hasPm = user_trusts_has_payment_method_id_column($db);
    $sql = $hasPm
        ? 'SELECT ut.id, ut.user_id, ut.trust_service_id, ut.payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free,
                  pm.method_type AS payment_method_type, pm.method_name AS payment_method_name,
                  u.full_name AS user_name, u.email AS user_email
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           INNER JOIN users u ON u.id = ut.user_id
           LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id
           WHERE ut.payment_status = "pending" AND ts.is_free = 0
           ORDER BY ut.created_at DESC'
        : 'SELECT ut.id, ut.user_id, ut.trust_service_id, NULL AS payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free,
                  NULL AS payment_method_type, NULL AS payment_method_name,
                  u.full_name AS user_name, u.email AS user_email
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           INNER JOIN users u ON u.id = ut.user_id
           WHERE ut.payment_status = "pending" AND ts.is_free = 0
           ORDER BY ut.created_at DESC';
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll();
    
    // Decode trust_data and format for frontend
    foreach ($payments as &$payment) {
        $payment['price'] = (float) $payment['price'];
        if (!empty($payment['trust_data'])) {
            $payment['trust_data'] = json_decode($payment['trust_data'], true) ?? [];
        } else {
            $payment['trust_data'] = [];
        }
    }

    $depositStmt = $db->prepare(
        'SELECT t.id, t.user_id, t.trust_id, t.coin_id, t.amount, t.status, t.asset_symbol,
                t.transaction_data, t.created_at, t.updated_at,
                c.coin_key, c.display_name AS coin_name, c.symbol AS coin_symbol,
                u.full_name AS user_name, u.email AS user_email,
                ut.id AS trust_ref_id, ts.service_name AS trust_service_name
         FROM transactions t
         INNER JOIN coins c ON c.id = t.coin_id
         INNER JOIN users u ON u.id = t.user_id
         LEFT JOIN user_trusts ut ON ut.id = t.trust_id
         LEFT JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE t.type = "deposit" AND t.status = "pending"
         ORDER BY t.created_at DESC'
    );
    $depositStmt->execute();
    $deposits = $depositStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($deposits as &$deposit) {
        $deposit['amount'] = (float) $deposit['amount'];
        decode_transaction_data_row($deposit);
    }

    $liquidationStmt = $db->prepare(
        'SELECT t.id, t.user_id, t.trust_id, t.coin_id, t.amount, t.fee, t.recipient, t.status, t.asset_symbol,
                t.transaction_data, t.created_at, t.updated_at,
                c.coin_key, c.display_name AS coin_name, c.symbol AS coin_symbol,
                u.full_name AS user_name, u.email AS user_email,
                ut.id AS trust_ref_id, ts.service_name AS trust_service_name
         FROM transactions t
         INNER JOIN coins c ON c.id = t.coin_id
         INNER JOIN users u ON u.id = t.user_id
         LEFT JOIN user_trusts ut ON ut.id = t.trust_id
         LEFT JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE t.type = "liquidation" AND t.status = "pending"
         ORDER BY t.created_at DESC'
    );
    $liquidationStmt->execute();
    $liquidations = $liquidationStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($liquidations as &$liq) {
        $liq['amount'] = (float) $liq['amount'];
        $liq['fee'] = (float) $liq['fee'];
        decode_transaction_data_row($liq);
    }

    $liquidationFeeStmt = $db->prepare(
        'SELECT t.id, t.user_id, t.trust_id, t.coin_id, t.amount, t.status, t.transaction_data, t.created_at, t.updated_at,
                c.coin_key, c.display_name AS coin_name, c.symbol AS coin_symbol,
                u.full_name AS user_name, u.email AS user_email,
                ut.id AS trust_ref_id, ts.service_name AS trust_service_name
         FROM transactions t
         LEFT JOIN coins c ON c.id = t.coin_id
         INNER JOIN users u ON u.id = t.user_id
         LEFT JOIN user_trusts ut ON ut.id = t.trust_id
         LEFT JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE t.type = "liquidation_fee" AND t.status = "pending"
         ORDER BY t.created_at DESC'
    );
    $liquidationFeeStmt->execute();
    $liquidationFees = $liquidationFeeStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($liquidationFees as &$feeRow) {
        $feeRow['amount'] = (float) $feeRow['amount'];
        decode_transaction_data_row($feeRow);
    }

    $assetFundingStmt = $db->prepare(
        'SELECT t.id, t.user_id, t.trust_id, t.amount, t.status, t.transaction_data, t.created_at, t.updated_at,
                u.full_name AS user_name, u.email AS user_email,
                ut.id AS trust_ref_id, ts.service_name AS trust_service_name
         FROM transactions t
         INNER JOIN users u ON u.id = t.user_id
         LEFT JOIN user_trusts ut ON ut.id = t.trust_id
         LEFT JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE t.type = "asset_funding" AND t.status = "pending"
         ORDER BY t.created_at DESC'
    );
    $assetFundingStmt->execute();
    $assetFundings = $assetFundingStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($assetFundings as &$fundingRow) {
        $fundingRow['amount'] = (float) $fundingRow['amount'];
        decode_transaction_data_row($fundingRow);
    }
    
    send_json([
        'success' => true,
        'payments' => $payments,
        'deposits' => $deposits,
        'liquidations' => $liquidations,
        'liquidation_fees' => $liquidationFees,
        'asset_fundings' => $assetFundings,
    ]);
}

function handleApproveRejectPayment($payload = null) {
    require_admin_auth();
    require_csrf_token();
    if ($payload === null) {
        $payload = get_json_input();
    }
    
    $trustId = isset($payload['trust_id']) ? (int) $payload['trust_id'] : 0;
    $action = sanitize_text($payload['action'] ?? ''); // 'approve' or 'reject'
    
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }
    
    if (!in_array($action, ['approve', 'reject'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be "approve" or "reject"'], 400);
    }
    
    $db = getDatabase();
    $db->beginTransaction();

    try {
        // Verify trust exists and has pending payment
        $stmt = $db->prepare(
            'SELECT ut.id, ut.payment_status, ut.status, ts.is_free
             FROM user_trusts ut
             INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
             WHERE ut.id = :id AND ut.payment_status = "pending" AND ts.is_free = 0
             LIMIT 1'
        );
        $stmt->execute([':id' => $trustId]);
        $trust = $stmt->fetch();
        
        if (!$trust) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Trust not found or payment already processed'], 404);
        }

        // Update based on action
        $newPaymentStatus = null;
        $newStatus = null;
        $message = null;

        if ($action === 'approve') {
            // Approve: payment_status = 'completed', status = 'active'
            $newPaymentStatus = 'completed';
            $newStatus = 'active';
            $message = 'Payment approved successfully. Trust is now active.';

            $update = $db->prepare(
                'UPDATE user_trusts
                 SET payment_status = "completed", status = "active", updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $update->execute([':id' => $trustId]);
        } else {
            // Reject: payment_status = 'rejected', status = 'inactive'
            // (keeps trust from sitting in "pending" forever)
            $newPaymentStatus = 'rejected';
            $newStatus = 'inactive';
            $message = 'Payment rejected. Trust is now inactive.';

            $update = $db->prepare(
                'UPDATE user_trusts
                 SET payment_status = "rejected", status = "inactive", updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $update->execute([':id' => $trustId]);
        }

        if (($update->rowCount() ?? 0) <= 0) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'No changes were applied'], 409);
        }

        $db->commit();

        send_json([
            'success' => true,
            'message' => $message,
            'payment_status' => $newPaymentStatus,
            'status' => $newStatus,
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Approve/reject payment failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to process payment: ' . $e->getMessage()], 500);
    }
}

function handleApproveRejectDeposit($payload = null) {
    require_admin_auth();
    require_csrf_token();
    if ($payload === null) {
        $payload = get_json_input();
    }

    $depositId = isset($payload['deposit_id']) ? (int) $payload['deposit_id'] : 0;
    $action = sanitize_text($payload['action'] ?? '');
    $adminNotes = sanitize_text($payload['admin_notes'] ?? '');

    if ($depositId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid deposit ID'], 400);
    }
    if (!in_array($action, ['approve', 'reject'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be "approve" or "reject"'], 400);
    }

    $db = getDatabase();
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'SELECT t.id, t.user_id, t.trust_id, t.coin_id, t.amount, t.status, t.transaction_data, c.symbol
             FROM transactions t
             INNER JOIN coins c ON c.id = t.coin_id
             WHERE t.id = :id AND t.type = "deposit" AND t.status = "pending"
             LIMIT 1'
        );
        $stmt->execute([':id' => $depositId]);
        $deposit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deposit) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Deposit not found or already processed'], 404);
        }

        $txData = !empty($deposit['transaction_data'])
            ? (json_decode($deposit['transaction_data'], true) ?? [])
            : [];

        if ($action === 'approve') {
            $newBalance = credit_user_coin_balance(
                $db,
                (int) $deposit['user_id'],
                (int) $deposit['coin_id'],
                (float) $deposit['amount']
            );
            $txData['approved_at'] = date('c');
            $txData['admin_notes'] = $adminNotes;
            $txData['balance_after'] = $newBalance;

            $update = $db->prepare(
                'UPDATE transactions SET status = "completed", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $update->execute([
                ':id' => $depositId,
                ':data' => json_encode($txData),
            ]);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Deposit approved. User balance has been credited.',
                'status' => 'completed',
            ]);
        }

        $txData['rejected_at'] = date('c');
        $txData['admin_notes'] = $adminNotes;

        $update = $db->prepare(
            'UPDATE transactions SET status = "rejected", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([
            ':id' => $depositId,
            ':data' => json_encode($txData),
        ]);

        $db->commit();
        send_json([
            'success' => true,
            'message' => 'Deposit rejected.',
            'status' => 'rejected',
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Approve/reject deposit failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to process deposit: ' . $e->getMessage()], 500);
    }
}

function handleApproveRejectLiquidation($payload = null) {
    require_admin_auth();
    require_csrf_token();
    if ($payload === null) {
        $payload = get_json_input();
    }

    $liquidationId = isset($payload['liquidation_id']) ? (int) $payload['liquidation_id'] : 0;
    $action = sanitize_text($payload['action'] ?? '');
    $adminNotes = sanitize_text($payload['admin_notes'] ?? '');

    if ($liquidationId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid liquidation ID'], 400);
    }
    if (!in_array($action, ['approve', 'reject'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be "approve" or "reject"'], 400);
    }

    $db = getDatabase();
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'SELECT t.id, t.user_id, t.trust_id, t.coin_id, t.amount, t.fee, t.recipient, t.status, t.transaction_data, c.symbol
             FROM transactions t
             INNER JOIN coins c ON c.id = t.coin_id
             WHERE t.id = :id AND t.type = "liquidation" AND t.status = "pending"
             LIMIT 1'
        );
        $stmt->execute([':id' => $liquidationId]);
        $liquidation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$liquidation) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Liquidation not found or already processed'], 404);
        }

        $txData = !empty($liquidation['transaction_data'])
            ? (json_decode($liquidation['transaction_data'], true) ?? [])
            : [];

        if ($action === 'approve') {
            $feePayment = user_has_liquidation_fee_payment(
                $db,
                (int) $liquidation['user_id'],
                (int) $liquidation['coin_id'],
                (int) ($liquidation['trust_id'] ?? 0),
                true
            );
            if (!$feePayment) {
                $db->rollBack();
                send_json([
                    'success' => false,
                    'message' => 'Cannot approve liquidation: no completed liquidation fee payment found for this asset.',
                ], 409);
            }

            $totalDebit = (float) $liquidation['amount'] + (float) $liquidation['fee'];
            $newBalance = debit_user_coin_balance(
                $db,
                (int) $liquidation['user_id'],
                (int) $liquidation['coin_id'],
                $totalDebit
            );
            $txData['approved_at'] = date('c');
            $txData['admin_notes'] = $adminNotes;
            $txData['balance_after'] = $newBalance;

            $update = $db->prepare(
                'UPDATE transactions SET status = "completed", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $update->execute([
                ':id' => $liquidationId,
                ':data' => json_encode($txData),
            ]);

            mark_liquidation_fee_consumed($db, (int) $feePayment['id'], $liquidationId);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Liquidation approved. User balance has been debited.',
                'status' => 'completed',
            ]);
        }

        $txData['rejected_at'] = date('c');
        $txData['admin_notes'] = $adminNotes;

        $update = $db->prepare(
            'UPDATE transactions SET status = "rejected", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([
            ':id' => $liquidationId,
            ':data' => json_encode($txData),
        ]);

        $db->commit();
        send_json([
            'success' => true,
            'message' => 'Liquidation rejected.',
            'status' => 'rejected',
        ]);
    } catch (RuntimeException $e) {
        $db->rollBack();
        send_json(['success' => false, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Approve/reject liquidation failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to process liquidation: ' . $e->getMessage()], 500);
    }
}

function handleApproveRejectLiquidationFee($payload = null) {
    require_admin_auth();
    require_csrf_token();
    if ($payload === null) {
        $payload = get_json_input();
    }

    $feeId = isset($payload['liquidation_fee_id']) ? (int) $payload['liquidation_fee_id'] : 0;
    $action = sanitize_text($payload['action'] ?? '');
    $adminNotes = sanitize_text($payload['admin_notes'] ?? '');

    if ($feeId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid liquidation fee ID'], 400);
    }
    if (!in_array($action, ['approve', 'reject'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be "approve" or "reject"'], 400);
    }

    $db = getDatabase();
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'SELECT t.id, t.user_id, t.amount, t.status, t.transaction_data
             FROM transactions t
             WHERE t.id = :id AND t.type = "liquidation_fee" AND t.status = "pending"
             LIMIT 1'
        );
        $stmt->execute([':id' => $feeId]);
        $feePayment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$feePayment) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Liquidation fee payment not found or already processed'], 404);
        }

        $txData = !empty($feePayment['transaction_data'])
            ? (json_decode($feePayment['transaction_data'], true) ?? [])
            : [];

        if ($action === 'approve') {
            $txData['approved_at'] = date('c');
            $txData['admin_notes'] = $adminNotes;

            $update = $db->prepare(
                'UPDATE transactions SET status = "completed", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $update->execute([
                ':id' => $feeId,
                ':data' => json_encode($txData),
            ]);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Liquidation fee payment approved.',
                'status' => 'completed',
            ]);
        }

        $txData['rejected_at'] = date('c');
        $txData['admin_notes'] = $adminNotes;

        $update = $db->prepare(
            'UPDATE transactions SET status = "rejected", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([
            ':id' => $feeId,
            ':data' => json_encode($txData),
        ]);

        $db->commit();
        send_json([
            'success' => true,
            'message' => 'Liquidation fee payment rejected.',
            'status' => 'rejected',
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Approve/reject liquidation fee failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to process liquidation fee payment: ' . $e->getMessage()], 500);
    }
}

function handleApproveRejectAssetFunding($payload = null) {
    require_admin_auth();
    require_csrf_token();
    if ($payload === null) {
        $payload = get_json_input();
    }

    $fundingId = isset($payload['asset_funding_id']) ? (int) $payload['asset_funding_id'] : 0;
    $action = sanitize_text($payload['action'] ?? '');
    $adminNotes = sanitize_text($payload['admin_notes'] ?? '');

    if ($fundingId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid asset funding ID'], 400);
    }
    if (!in_array($action, ['approve', 'reject'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be "approve" or "reject"'], 400);
    }

    $db = getDatabase();
    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'SELECT t.id, t.user_id, t.trust_id, t.amount, t.status, t.transaction_data
             FROM transactions t
             WHERE t.id = :id AND t.type = "asset_funding" AND t.status = "pending"
             LIMIT 1'
        );
        $stmt->execute([':id' => $fundingId]);
        $funding = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$funding) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Asset funding payment not found or already processed'], 404);
        }

        $trustId = (int) ($funding['trust_id'] ?? 0);
        if ($trustId <= 0) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Trust reference missing on funding payment'], 400);
        }

        $trustStmt = $db->prepare('SELECT id, trust_data FROM user_trusts WHERE id = :id LIMIT 1');
        $trustStmt->execute([':id' => $trustId]);
        $trustRow = $trustStmt->fetch(PDO::FETCH_ASSOC);
        if (!$trustRow) {
            $db->rollBack();
            send_json(['success' => false, 'message' => 'Trust not found'], 404);
        }

        $trustData = !empty($trustRow['trust_data'])
            ? (json_decode($trustRow['trust_data'], true) ?? [])
            : [];
        $txData = !empty($funding['transaction_data'])
            ? (json_decode($funding['transaction_data'], true) ?? [])
            : [];
        $purpose = sanitize_text($txData['purpose'] ?? '');
        $amountUsd = (float) $funding['amount'];

        if ($action === 'approve') {
            if ($purpose === 'catalog_asset') {
                $assetId = sanitize_text($txData['asset_id'] ?? '');
                $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
                $index = find_trust_asset_index($assets, $assetId);
                if ($index === null) {
                    $db->rollBack();
                    send_json(['success' => false, 'message' => 'Catalog asset not found on trust'], 404);
                }
                $assets[$index]['funding_status'] = 'funded';
                $assets[$index]['funded_amount_usd'] = $amountUsd;
                $assets[$index]['funding_transaction_id'] = $fundingId;
                $trustData['assets'] = $assets;
            } elseif ($purpose === 'trust_declared_value') {
                $trustData['declared_value_funding'] = [
                    'amount_usd' => $amountUsd,
                    'status' => 'funded',
                    'funded_amount_usd' => $amountUsd,
                    'transaction_id' => $fundingId,
                ];
            } else {
                $db->rollBack();
                send_json(['success' => false, 'message' => 'Unknown asset funding purpose'], 400);
            }

            $txData['approved_at'] = date('c');
            $txData['admin_notes'] = $adminNotes;

            $updateTrust = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id');
            $updateTrust->execute([
                ':trust_data' => json_encode($trustData, JSON_UNESCAPED_UNICODE),
                ':id' => $trustId,
            ]);

            $update = $db->prepare(
                'UPDATE transactions SET status = "completed", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $update->execute([
                ':id' => $fundingId,
                ':data' => json_encode($txData),
            ]);

            $db->commit();
            send_json([
                'success' => true,
                'message' => 'Asset funding approved. Trust value has been updated.',
                'status' => 'completed',
            ]);
        }

        if ($purpose === 'catalog_asset') {
            $assetId = sanitize_text($txData['asset_id'] ?? '');
            $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
            $index = find_trust_asset_index($assets, $assetId);
            if ($index !== null) {
                $assets[$index]['funding_status'] = 'rejected';
                $trustData['assets'] = $assets;
            }
        } elseif ($purpose === 'trust_declared_value') {
            $trustData['declared_value_funding'] = [
                'amount_usd' => $amountUsd,
                'status' => 'rejected',
                'funded_amount_usd' => 0.0,
                'transaction_id' => $fundingId,
            ];
        }

        $txData['rejected_at'] = date('c');
        $txData['admin_notes'] = $adminNotes;

        $updateTrust = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id');
        $updateTrust->execute([
            ':trust_data' => json_encode($trustData, JSON_UNESCAPED_UNICODE),
            ':id' => $trustId,
        ]);

        $update = $db->prepare(
            'UPDATE transactions SET status = "rejected", transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([
            ':id' => $fundingId,
            ':data' => json_encode($txData),
        ]);

        $db->commit();
        send_json([
            'success' => true,
            'message' => 'Asset funding payment rejected.',
            'status' => 'rejected',
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Approve/reject asset funding failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to process asset funding payment: ' . $e->getMessage()], 500);
    }
}
