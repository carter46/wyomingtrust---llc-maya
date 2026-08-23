<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            handleGetUserTrust();
        } else {
            handleListUserTrusts();
        }
        break;
    case 'POST':
        handleCreateUserTrust();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdateUserTrust();
        break;
    case 'DELETE':
        handleDeleteUserTrust();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function user_trust_service_extra_select(PDO $db): string {
    $extra = '';
    if (trust_services_has_asset_category_config_column($db)) {
        $extra .= ', ts.asset_category_config';
    } elseif (trust_services_has_asset_types_column($db)) {
        $extra .= ', ts.asset_types';
    }
    if (trust_services_has_liquidation_fee_column($db)) {
        $extra .= ', ts.liquidation_fee';
    }
    return $extra;
}

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

function normalize_beneficiaries($beneficiariesRaw) {
    if (!is_array($beneficiariesRaw)) {
        return [false, 'Beneficiaries must be an array', null];
    }

    if (count($beneficiariesRaw) === 0) {
        return [false, 'At least one beneficiary is required', null];
    }

    $beneficiaries = [];
    $total = 0.0;

    foreach ($beneficiariesRaw as $idx => $b) {
        if (!is_array($b)) {
            return [false, 'Invalid beneficiary at index ' . $idx, null];
        }

        $name = sanitize_text($b['name'] ?? '');
        $relationship = sanitize_text($b['relationship'] ?? '');
        $email = sanitize_text($b['email'] ?? '');
        $wallet = sanitize_text($b['wallet_address'] ?? '');
        $allocation = isset($b['allocation']) ? (float) $b['allocation'] : 0.0;
        $isMyself = !empty($b['is_myself']) ? 1 : 0;

        if ($name === '') {
            return [false, 'Beneficiary name is required (index ' . $idx . ')', null];
        }
        if ($relationship === '') {
            return [false, 'Beneficiary relationship is required (index ' . $idx . ')', null];
        }
        if ($email !== '' && !validate_email($email)) {
            return [false, 'Invalid beneficiary email (index ' . $idx . ')', null];
        }
        if ($allocation < 0 || $allocation > 100) {
            return [false, 'Allocation must be between 0 and 100 (index ' . $idx . ')', null];
        }

        $total += $allocation;

        $beneficiaries[] = [
            'name' => $name,
            'relationship' => $relationship,
            'email' => $email,
            'allocation' => $allocation,
            'wallet_address' => $wallet,
            'is_myself' => $isMyself === 1,
        ];
    }

    if (abs($total - 100.0) > 0.01) {
        return [false, 'Total allocation must equal 100%. Current total: ' . number_format($total, 2) . '%', null];
    }

    return [true, null, $beneficiaries];
}

function handleUpdateUserTrust() {
    $userId = require_user_auth();
    require_csrf_token();
    $payload = get_json_input();

    $trustId = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $stmt = $db->prepare('SELECT id, status, trust_data FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
    $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
    $row = $stmt->fetch();
    if (!$row) {
        send_json(['success' => false, 'message' => 'LLC not found'], 404);
    }

    $trustData = [];
    if (!empty($row['trust_data'])) {
        $trustData = json_decode($row['trust_data'], true) ?? [];
    }
    $currentStatus = strtolower((string) ($row['status'] ?? ''));

    $updatesMade = false;
    $statusUpdate = null;
    $liquidationFeePaymentId = null;

    if (isset($payload['trust_name'])) {
        $trustName = sanitize_text($payload['trust_name']);
        if ($trustName === '') {
            send_json(['success' => false, 'message' => 'LLC name cannot be empty'], 400);
        }
        $trustData['trust_name'] = $trustName;
        $updatesMade = true;
    }

    if (isset($payload['beneficiaries'])) {
        [$ok, $err, $normalized] = normalize_beneficiaries($payload['beneficiaries']);
        if (!$ok) {
            send_json(['success' => false, 'message' => $err], 400);
        }
        $trustData['beneficiaries'] = $normalized;
        $updatesMade = true;
    }

    if (isset($payload['add_entrusted_coins'])) {
        $toAdd = $payload['add_entrusted_coins'];
        if (!is_array($toAdd) || count($toAdd) === 0) {
            send_json(['success' => false, 'message' => 'Select at least one coin to add'], 400);
        }

        $svcStmt = $db->prepare(
            'SELECT ts.service_key FROM user_trusts ut
             INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
             WHERE ut.id = :id AND ut.user_id = :user_id LIMIT 1'
        );
        $svcStmt->execute([':id' => $trustId, ':user_id' => $userId]);
        $svc = $svcStmt->fetch();
        if (!$svc || !is_crypto_trust_type($svc['service_key'] ?? '')) {
            send_json(['success' => false, 'message' => 'Coins can only be added to crypto trusts'], 400);
        }

        $existing = isset($trustData['entrusted_coins']) && is_array($trustData['entrusted_coins'])
            ? $trustData['entrusted_coins']
            : [];
        $existingLower = array_map('strtolower', array_map('strval', $existing));

        $validated = [];
        $coinStmt = $db->prepare('SELECT coin_key FROM coins WHERE coin_key = :key LIMIT 1');
        foreach ($toAdd as $rawKey) {
            $key = sanitize_text((string) $rawKey);
            if ($key === '') {
                continue;
            }
            if (in_array(strtolower($key), $existingLower, true)) {
                continue;
            }
            $coinStmt->execute([':key' => $key]);
            if ($coinStmt->fetch()) {
                $validated[] = $key;
                $existingLower[] = strtolower($key);
            }
        }

        if (count($validated) === 0) {
            send_json(['success' => false, 'message' => 'No new valid coins to add'], 400);
        }

        $trustData['entrusted_coins'] = array_values(array_merge($existing, $validated));
        $updatesMade = true;
    }

    if (isset($payload['status'])) {
        if ($currentStatus === 'pending') {
            send_json(['success' => false, 'message' => 'LLC registration is pending approval. Status changes are not available yet.'], 403);
        }

        $status = sanitize_text($payload['status']);
        $allowedStatuses = ['active', 'inactive'];
        if (!in_array(strtolower($status), $allowedStatuses, true)) {
            send_json(['success' => false, 'message' => 'Invalid status. Allowed: ' . implode(', ', $allowedStatuses)], 400);
        }
        $requestedStatus = strtolower($status);

        if ($requestedStatus === 'active' && in_array($currentStatus, ['pending', 'inactive'], true)) {
            send_json(['success' => false, 'message' => 'LLC activation requires approval.'], 403);
        }
        if ($requestedStatus === 'inactive' && $currentStatus !== 'active') {
            send_json(['success' => false, 'message' => 'Only active LLCs can be set to inactive.'], 403);
        }

        $statusUpdate = $requestedStatus;
        $updatesMade = true;
    }

    if (!empty($payload['liquidate'])) {
        if ($currentStatus === 'pending') {
            send_json(['success' => false, 'message' => 'LLC registration is pending approval. Liquidation is not available yet.'], 403);
        }
        if ($currentStatus !== 'active') {
            send_json(['success' => false, 'message' => 'Only active LLCs can be liquidated.'], 403);
        }

        $svcStmt = $db->prepare(
            'SELECT ts.service_key' . (trust_services_has_liquidation_fee_column($db) ? ', ts.liquidation_fee' : '') . '
             FROM user_trusts ut INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
             WHERE ut.id = :id AND ut.user_id = :user_id LIMIT 1'
        );
        $svcStmt->execute([':id' => $trustId, ':user_id' => $userId]);
        $svc = $svcStmt->fetch();
        if (!$svc) {
            send_json(['success' => false, 'message' => 'LLC not found'], 404);
        }
        $trustType = $svc['service_key'] ?? '';
        if (!trust_allows_liquidation($trustType)) {
            send_json(['success' => false, 'message' => 'Irrevocable LLCs cannot be liquidated'], 403);
        }

        if ($currentStatus === 'liquidated') {
            send_json(['success' => false, 'message' => 'This LLC has already been liquidated'], 409);
        }

        $fee = isset($svc['liquidation_fee']) ? (float) $svc['liquidation_fee'] : 0.0;
        $feePayment = null;
        if ($fee > 0) {
            $feePayment = user_has_trust_liquidation_fee_payment($db, $userId, $trustId, true);
            if (!$feePayment) {
                $submitted = user_has_trust_liquidation_fee_payment($db, $userId, $trustId, false);
                send_json([
                    'success' => false,
                    'message' => $submitted
                        ? 'Liquidation fee payment is pending approval.'
                        : 'Liquidation fee payment is required. Please complete checkout first.',
                    'redirect_checkout' => !$submitted,
                    'payment_pending' => (bool) $submitted,
                ], $submitted ? 409 : 402);
            }
        }
        $trustData['liquidation'] = [
            'requested_at' => date('c'),
            'fee' => $fee,
            'status' => 'pending',
        ];
        if ($feePayment) {
            $trustData['liquidation']['fee_transaction_id'] = (int) $feePayment['id'];
            $liquidationFeePaymentId = (int) $feePayment['id'];
        }
        $statusUpdate = 'liquidated';
        $updatesMade = true;
    }

    if (!$updatesMade) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }

    try {
        if ($statusUpdate !== null) {
            // Update status directly in database
            $up = $db->prepare('UPDATE user_trusts SET status = :status, trust_data = :trust_data WHERE id = :id AND user_id = :user_id');
            $up->execute([
                ':status' => $statusUpdate,
                ':trust_data' => json_encode($trustData),
                ':id' => $trustId,
                ':user_id' => $userId,
            ]);

            if ($liquidationFeePaymentId) {
                mark_liquidation_fee_consumed($db, $liquidationFeePaymentId, $trustId);
            }
        } else {
            // Only update trust_data
            $up = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id AND user_id = :user_id');
            $up->execute([
                ':trust_data' => json_encode($trustData),
                ':id' => $trustId,
                ':user_id' => $userId,
            ]);
        }

        // Return updated trust
        $_GET['id'] = (string) $trustId;
        handleGetUserTrust();
    } catch (Exception $e) {
        error_log('Update user trust failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update trust'], 500);
    }
}

function handleDeleteUserTrust() {
    $userId = require_user_auth();
    $trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }
    
    $db = getDatabase();
    
    $svcStmt = $db->prepare(
        'SELECT ts.service_key FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.id = :id AND ut.user_id = :user_id LIMIT 1'
    );
    $svcStmt->execute([':id' => $trustId, ':user_id' => $userId]);
    $svc = $svcStmt->fetch();
    
    if (!$svc) {
        send_json(['success' => false, 'message' => 'LLC not found'], 404);
    }

    if (is_irrevocable_trust_type($svc['service_key'] ?? '')) {
        send_json(['success' => false, 'message' => 'Irrevocable LLCs cannot be deleted or liquidated'], 403);
    }
    
    try {
        // Delete trust (CASCADE will handle related data if foreign keys are set up)
        $del = $db->prepare('DELETE FROM user_trusts WHERE id = :id AND user_id = :user_id');
        $del->execute([':id' => $trustId, ':user_id' => $userId]);
        
        send_json(['success' => true, 'message' => 'LLC deleted successfully']);
    } catch (Exception $e) {
        error_log('Delete user trust failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to delete trust'], 500);
    }
}

function handleGetUserTrust() {
    $userId = require_user_auth();
    $trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $hasPm = user_trusts_has_payment_method_id_column($db);
    $svcExtra = user_trust_service_extra_select($db);
    $sql = $hasPm
        ? "SELECT ut.id, ut.user_id, ut.trust_service_id, ut.payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                  pm.method_type AS payment_method_type, pm.method_name AS payment_method_name
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id
           WHERE ut.user_id = :user_id AND ut.id = :id
           LIMIT 1"
        : "SELECT ut.id, ut.user_id, ut.trust_service_id, NULL AS payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                  NULL AS payment_method_type, NULL AS payment_method_name
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           WHERE ut.user_id = :user_id AND ut.id = :id
           LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId, ':id' => $trustId]);
    $trust = $stmt->fetch();

    if (!$trust) {
        send_json(['success' => false, 'message' => 'LLC not found'], 404);
    }

    $trust = enrich_user_trust_row($trust);

    send_json(['success' => true, 'trust' => $trust]);
}

function handleListUserTrusts() {
    $userId = require_user_auth();
    $db = getDatabase();
    
    $hasPm = user_trusts_has_payment_method_id_column($db);
    $svcExtra = user_trust_service_extra_select($db);
    $sql = $hasPm
        ? "SELECT ut.id, ut.user_id, ut.trust_service_id, ut.payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                  pm.method_type AS payment_method_type, pm.method_name AS payment_method_name
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id
           WHERE ut.user_id = :user_id
           ORDER BY ut.created_at DESC"
        : "SELECT ut.id, ut.user_id, ut.trust_service_id, NULL AS payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                  ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                  NULL AS payment_method_type, NULL AS payment_method_name
           FROM user_trusts ut
           INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
           WHERE ut.user_id = :user_id
           ORDER BY ut.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $trusts = $stmt->fetchAll();
    
    foreach ($trusts as &$trust) {
        $trust = enrich_user_trust_row($trust);
    }
    unset($trust);
    
    send_json(['success' => true, 'trusts' => $trusts]);
}

function handleCreateUserTrust() {
    $userId = require_user_auth();
    $payload = get_json_input();
    
    $trustServiceId = isset($payload['trust_service_id']) ? (int) $payload['trust_service_id'] : 0;
    $paymentMethodId = isset($payload['payment_method_id']) ? (int) $payload['payment_method_id'] : 0;
    $trustData = isset($payload['trust_data']) ? $payload['trust_data'] : [];
    
    if ($trustServiceId <= 0) {
        send_json(['success' => false, 'message' => 'Valid trust service ID is required'], 400);
    }
    
    $db = getDatabase();
    
    // Verify trust service exists and is active
    $stmt = $db->prepare('SELECT id, price, is_free, service_key FROM trust_services WHERE id = :id AND is_active = 1 LIMIT 1');
    $stmt->execute([':id' => $trustServiceId]);
    $trustService = $stmt->fetch();
    
    if (!$trustService) {
        send_json(['success' => false, 'message' => 'LLC service not found or inactive'], 404);
    }

    // Validate beneficiaries data (must exist and total 100%)
    $beneficiariesRaw = is_array($trustData) ? ($trustData['beneficiaries'] ?? null) : null;
    [$okBen, $errBen, $normalizedBeneficiaries] = normalize_beneficiaries($beneficiariesRaw);
    if (!$okBen) {
        send_json(['success' => false, 'message' => $errBen], 400);
    }
    // Ensure stored data is normalized
    $trustData['beneficiaries'] = $normalizedBeneficiaries;

    if (trust_type_supports_asset_catalog($trustService['service_key'] ?? '')) {
        $declared = isset($trustData['total_estimated_value']) ? (float) $trustData['total_estimated_value'] : 0.0;
        if ($declared > 0) {
            $trustData['declared_value_funding'] = [
                'amount_usd' => round($declared, 2),
                'status' => 'unfunded',
                'funded_amount_usd' => 0.0,
                'transaction_id' => null,
            ];
        }
    }
    
    try {
        $status = 'pending';
        $paymentStatus = 'pending';
        $resolvedPaymentMethodId = null;
        
        if ($trustService['is_free']) {
            $paymentStatus = 'completed';
            $status = 'pending';
            $resolvedPaymentMethodId = null;
        } else {
            if ($paymentMethodId > 0) {
                // Validate payment method exists and is active
                $pm = $db->prepare('SELECT id FROM payment_methods WHERE id = :id AND is_active = 1 LIMIT 1');
                $pm->execute([':id' => $paymentMethodId]);
                if (!$pm->fetch()) {
                    send_json(['success' => false, 'message' => 'Invalid payment method'], 400);
                }
                $resolvedPaymentMethodId = $paymentMethodId;
            }
        }
        
        // Encode trust_data to JSON with error checking
        $trustDataJson = json_encode($trustData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($trustDataJson === false) {
            $jsonError = json_last_error_msg();
            error_log('JSON encode failed: ' . $jsonError);
            send_json(['success' => false, 'message' => 'Failed to encode trust data: ' . $jsonError], 500);
        }
        
        $hasPm = user_trusts_has_payment_method_id_column($db);
        if ($hasPm) {
            $stmt = $db->prepare(
                'INSERT INTO user_trusts (user_id, trust_service_id, payment_method_id, status, payment_status, trust_data)
                 VALUES (:user_id, :trust_service_id, :payment_method_id, :status, :payment_status, :trust_data)'
            );
            
            $stmt->execute([
                ':user_id' => $userId,
                ':trust_service_id' => $trustServiceId,
                ':payment_method_id' => $resolvedPaymentMethodId, // Can be null for free services
                ':status' => $status,
                ':payment_status' => $paymentStatus,
                ':trust_data' => $trustDataJson,
            ]);
        } else {
            // Backward-compatible insert for older DB schemas
            $stmt = $db->prepare(
                'INSERT INTO user_trusts (user_id, trust_service_id, status, payment_status, trust_data)
                 VALUES (:user_id, :trust_service_id, :status, :payment_status, :trust_data)'
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':trust_service_id' => $trustServiceId,
                ':status' => $status,
                ':payment_status' => $paymentStatus,
                ':trust_data' => $trustDataJson,
            ]);
        }
        
        $trustId = (int) $db->lastInsertId();
        
        send_json([
            'success' => true,
            'message' => 'LLC created successfully',
            'trust' => [
                'id' => $trustId,
                'payment_method_id' => $resolvedPaymentMethodId,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],
        ]);
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        $errorTrace = $e->getTraceAsString();
        error_log('Create user trust failed: ' . $errorMsg);
        error_log('Stack trace: ' . $errorTrace);
        
        // Return detailed error for debugging (in production, you might want to hide this)
        send_json([
            'success' => false,
            'message' => 'Failed to create LLC. Please try again or contact support.',
        ], 500);
    }
}
