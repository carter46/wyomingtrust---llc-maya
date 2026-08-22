<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            handleGetAdminUserTrust();
        } elseif (isset($_GET['user_id'])) {
            handleListAdminUserTrusts();
        } else {
            send_json(['success' => false, 'message' => 'user_id or id is required'], 400);
        }
        break;
    case 'PATCH':
        handleAdminTrustRegistrationAction();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function admin_user_trust_base_select(PDO $db): string {
    $hasPm = admin_user_trusts_has_payment_method_id_column($db);
    $svcExtra = '';
    if (trust_services_has_asset_category_config_column($db)) {
        $svcExtra .= ', ts.asset_category_config';
    } elseif (trust_services_has_asset_types_column($db)) {
        $svcExtra .= ', ts.asset_types';
    }
    if (trust_services_has_liquidation_fee_column($db)) {
        $svcExtra .= ', ts.liquidation_fee';
    }

    if ($hasPm) {
        return "SELECT ut.id, ut.user_id, ut.trust_service_id, ut.payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                       ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                       pm.method_type AS payment_method_type, pm.method_name AS payment_method_name,
                       u.full_name AS user_name, u.email AS user_email
                FROM user_trusts ut
                INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
                INNER JOIN users u ON u.id = ut.user_id
                LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id";
    }

    return "SELECT ut.id, ut.user_id, ut.trust_service_id, NULL AS payment_method_id, ut.status, ut.payment_status, ut.trust_data, ut.created_at, ut.updated_at,
                   ts.service_key, ts.service_name, ts.price, ts.is_free{$svcExtra},
                   NULL AS payment_method_type, NULL AS payment_method_name,
                   u.full_name AS user_name, u.email AS user_email
            FROM user_trusts ut
            INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
            INNER JOIN users u ON u.id = ut.user_id";
}

function admin_user_trusts_has_payment_method_id_column(PDO $db): bool {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
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
        $cached = false;
    }
    return $cached;
}

function format_admin_user_trust_row(array $trust): array {
    $trust = enrich_user_trust_row($trust);
    $trustData = is_array($trust['trust_data'] ?? null) ? $trust['trust_data'] : [];
    $trust['trust_name'] = $trust['trust_name'] ?? ($trustData['trust_name'] ?? 'Untitled Trust');
    $trust['is_free'] = (int) ($trust['is_free'] ?? 0);
    $trust['price'] = isset($trust['price']) ? (float) $trust['price'] : 0.0;
    $trust['can_approve_registration'] = can_approve_free_trust_registration($trust);
    return $trust;
}

function handleListAdminUserTrusts() {
    require_admin_auth();
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    if ($userId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid user ID'], 400);
    }

    $db = getDatabase();
    $userCheck = $db->prepare('SELECT id, full_name, email FROM users WHERE id = :id LIMIT 1');
    $userCheck->execute([':id' => $userId]);
    $user = $userCheck->fetch();
    if (!$user) {
        send_json(['success' => false, 'message' => 'User not found'], 404);
    }

    $sql = admin_user_trust_base_select($db) . ' WHERE ut.user_id = :user_id ORDER BY ut.created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $trusts = [];
    foreach ($rows as $row) {
        $trusts[] = format_admin_user_trust_row($row);
    }

    send_json([
        'success' => true,
        'user' => $user,
        'trusts' => $trusts,
    ]);
}

function handleGetAdminUserTrust() {
    require_admin_auth();
    $trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $sql = admin_user_trust_base_select($db) . ' WHERE ut.id = :id LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $trustId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        send_json(['success' => false, 'message' => 'Trust not found'], 404);
    }

    send_json([
        'success' => true,
        'trust' => format_admin_user_trust_row($row),
    ]);
}

function handleAdminTrustRegistrationAction() {
    require_admin_auth();
    require_csrf_token();
    $payload = get_json_input();

    $trustId = isset($payload['trust_id']) ? (int) $payload['trust_id'] : 0;
    $action = sanitize_text($payload['action'] ?? '');

    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }
    if (!in_array($action, ['approve_registration', 'reject_registration'], true)) {
        send_json(['success' => false, 'message' => 'Invalid action. Must be approve_registration or reject_registration'], 400);
    }

    $db = getDatabase();
    $stmt = $db->prepare(
        'SELECT ut.id, ut.status, ut.payment_status, ts.is_free
         FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $trustId]);
    $trust = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trust) {
        send_json(['success' => false, 'message' => 'Trust not found'], 404);
    }

    if ((int) ($trust['is_free'] ?? 0) !== 1) {
        send_json(['success' => false, 'message' => 'Paid trust registration is handled in Payment Approvals.'], 409);
    }

    if (!can_approve_free_trust_registration($trust)) {
        send_json(['success' => false, 'message' => 'Trust is not eligible for registration approval.'], 409);
    }

    $newStatus = $action === 'approve_registration' ? 'active' : 'inactive';
    $message = $action === 'approve_registration'
        ? 'Trust registration approved. Trust is now active.'
        : 'Trust registration disapproved. Trust is now inactive.';

    $update = $db->prepare(
        'UPDATE user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         SET ut.status = :status, ut.updated_at = CURRENT_TIMESTAMP
         WHERE ut.id = :id
           AND ts.is_free = 1
           AND ut.status = "pending"
           AND ut.payment_status = "completed"'
    );
    $update->execute([
        ':status' => $newStatus,
        ':id' => $trustId,
    ]);

    if (($update->rowCount() ?? 0) <= 0) {
        send_json(['success' => false, 'message' => 'No changes were applied'], 409);
    }

    $detailSql = admin_user_trust_base_select($db) . ' WHERE ut.id = :id LIMIT 1';
    $detailStmt = $db->prepare($detailSql);
    $detailStmt->execute([':id' => $trustId]);
    $row = $detailStmt->fetch(PDO::FETCH_ASSOC);

    send_json([
        'success' => true,
        'message' => $message,
        'trust' => format_admin_user_trust_row($row),
    ]);
}
