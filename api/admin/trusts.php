<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListTrusts();
        break;
    case 'POST':
        handleCreateTrust();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdateTrust();
        break;
    case 'DELETE':
        handleDeleteTrust();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function trust_service_extra_columns(PDO $db): array {
    return [
        'asset_types' => trust_services_has_asset_types_column($db),
        'asset_category_config' => trust_services_has_asset_category_config_column($db),
        'liquidation_fee' => trust_services_has_liquidation_fee_column($db),
    ];
}

function trust_service_select_sql(PDO $db): string {
    $cols = trust_service_extra_columns($db);
    $extra = '';
    if ($cols['asset_types']) {
        $extra .= ', asset_types';
    }
    if ($cols['asset_category_config']) {
        $extra .= ', asset_category_config';
    }
    if ($cols['liquidation_fee']) {
        $extra .= ', liquidation_fee';
    }
    return "SELECT id, service_key, service_name, description{$extra}, price, is_free, is_active, created_at, updated_at FROM trust_services";
}

function format_trust_row(array $trust, PDO $db): array {
    $trustType = $trust['service_key'] ?? '';
    $trust['id'] = (int) ($trust['id'] ?? 0);
    $trust['price'] = (float) ($trust['price'] ?? 0);
    $trust['is_free'] = (int) ($trust['is_free'] ?? 0);
    $trust['is_active'] = (int) ($trust['is_active'] ?? 0);
    $trust['trust_type'] = $trustType;
    $trust['trust_type_label'] = get_trust_type_options()[$trustType] ?? ($trust['service_name'] ?? '');
    $trust['is_crypto'] = is_crypto_trust_type($trustType);
    $trust['is_irrevocable'] = is_irrevocable_trust_type($trustType);
    $trust['supports_asset_catalog'] = trust_type_supports_asset_catalog($trustType);
    $trust['allows_liquidation'] = trust_allows_liquidation($trustType);

    $configJson = $trust['asset_category_config'] ?? null;
    if ($configJson === null && isset($trust['asset_types'])) {
        $configJson = $trust['asset_types'];
    }
    $trust['asset_category_config'] = decode_asset_category_config(is_string($configJson) ? $configJson : null, $trustType);
    $trust['asset_categories'] = get_enabled_asset_categories_for_service($trustType, is_string($configJson) ? $configJson : encode_asset_category_config_json($trust['asset_category_config']));

    $liquidationFee = isset($trust['liquidation_fee']) ? (float) $trust['liquidation_fee'] : 0.0;
    $trust['liquidation_fee'] = trust_allows_liquidation($trustType) ? $liquidationFee : 0.0;

    unset($trust['asset_types']);
    return $trust;
}

function handleListTrusts() {
    require_admin_auth();
    $db = getDatabase();
    $stmt = $db->query(trust_service_select_sql($db) . ' ORDER BY service_name');
    $trusts = array_map(function ($row) use ($db) {
        return format_trust_row($row, $db);
    }, $stmt->fetchAll());

    send_json([
        'success' => true,
        'trusts' => $trusts,
        'trust_type_options' => get_trust_type_options(),
        'asset_category_catalog' => get_trust_asset_category_catalog(),
    ]);
}

function handleCreateTrust() {
    require_admin_auth();
    $payload = get_json_input();

    $trustType = sanitize_text($payload['trust_type'] ?? $payload['service_key'] ?? '');
    $serviceName = sanitize_text($payload['service_name'] ?? '');
    $description = sanitize_text($payload['description'] ?? '');
    $price = isset($payload['price']) ? (float) $payload['price'] : 0.0;
    $isFree = isset($payload['is_free']) ? (int) $payload['is_free'] : 0;
    $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;
    $liquidationFee = isset($payload['liquidation_fee']) ? (float) $payload['liquidation_fee'] : 0.0;
    $categoryConfig = normalize_asset_category_config($payload['asset_category_config'] ?? [], $trustType);

    if (!is_valid_trust_type_key($trustType)) {
        send_json(['success' => false, 'message' => 'Please select a valid trust type'], 400);
    }

    $typeOptions = get_trust_type_options();
    if ($serviceName === '') {
        $serviceName = $typeOptions[$trustType];
    }

    if (!trust_type_supports_asset_catalog($trustType)) {
        $categoryConfig = [];
    } elseif (empty(array_filter($categoryConfig, fn($c) => !empty($c['enabled'])))) {
        $categoryConfig = get_default_asset_category_config($trustType);
    }

    if (!trust_allows_liquidation($trustType)) {
        $liquidationFee = 0.0;
    }

    $db = getDatabase();
    $cols = trust_service_extra_columns($db);

    $exists = $db->prepare('SELECT COUNT(*) FROM trust_services WHERE service_key = :key');
    $exists->execute([':key' => $trustType]);
    if ((int) $exists->fetchColumn() > 0) {
        send_json(['success' => false, 'message' => 'This trust type is already configured. Edit the existing service instead.'], 409);
    }

    try {
        $fields = ['service_key', 'service_name', 'description', 'price', 'is_free', 'is_active'];
        $values = [':service_key', ':service_name', ':description', ':price', ':is_free', ':is_active'];
        $params = [
            ':service_key' => $trustType,
            ':service_name' => $serviceName,
            ':description' => $description,
            ':price' => $price,
            ':is_free' => $isFree,
            ':is_active' => $isActive,
        ];

        if ($cols['asset_category_config']) {
            $fields[] = 'asset_category_config';
            $values[] = ':asset_category_config';
            $params[':asset_category_config'] = encode_asset_category_config_json($categoryConfig);
        } elseif ($cols['asset_types']) {
            $fields[] = 'asset_types';
            $values[] = ':asset_types';
            $params[':asset_types'] = encode_asset_category_config_json($categoryConfig);
        }

        if ($cols['liquidation_fee']) {
            $fields[] = 'liquidation_fee';
            $values[] = ':liquidation_fee';
            $params[':liquidation_fee'] = $liquidationFee;
        }

        $sql = 'INSERT INTO trust_services (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        send_json([
            'success' => true,
            'message' => 'Trust service created successfully',
            'trust' => [
                'id' => (int) $db->lastInsertId(),
                'service_key' => $trustType,
                'trust_type' => $trustType,
                'service_name' => $serviceName,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Create trust failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to create trust service'], 500);
    }
}

function handleUpdateTrust() {
    require_admin_auth();
    $payload = get_json_input();
    $trustId = isset($payload['id']) ? (int) $payload['id'] : 0;

    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $cols = trust_service_extra_columns($db);

    $existing = $db->prepare('SELECT service_key FROM trust_services WHERE id = :id LIMIT 1');
    $existing->execute([':id' => $trustId]);
    $row = $existing->fetch();
    if (!$row) {
        send_json(['success' => false, 'message' => 'Trust service not found'], 404);
    }

    $trustType = $row['service_key'] ?? '';
    $updates = [];
    $params = [':id' => $trustId];

    if (isset($payload['service_name'])) {
        $updates[] = 'service_name = :service_name';
        $params[':service_name'] = sanitize_text($payload['service_name']);
    }
    if (isset($payload['description'])) {
        $updates[] = 'description = :description';
        $params[':description'] = sanitize_text($payload['description']);
    }
    if (isset($payload['price'])) {
        $updates[] = 'price = :price';
        $params[':price'] = (float) $payload['price'];
    }
    if (isset($payload['is_free'])) {
        $updates[] = 'is_free = :is_free';
        $params[':is_free'] = (int) $payload['is_free'];
    }
    if (isset($payload['is_active'])) {
        $updates[] = 'is_active = :is_active';
        $params[':is_active'] = (int) $payload['is_active'];
    }

    if (isset($payload['liquidation_fee']) && $cols['liquidation_fee']) {
        $fee = trust_allows_liquidation($trustType) ? (float) $payload['liquidation_fee'] : 0.0;
        $updates[] = 'liquidation_fee = :liquidation_fee';
        $params[':liquidation_fee'] = $fee;
    }

    if (isset($payload['asset_category_config']) && trust_type_supports_asset_catalog($trustType)) {
        $config = normalize_asset_category_config($payload['asset_category_config'], $trustType);
        if ($cols['asset_category_config']) {
            $updates[] = 'asset_category_config = :asset_category_config';
            $params[':asset_category_config'] = encode_asset_category_config_json($config);
        } elseif ($cols['asset_types']) {
            $updates[] = 'asset_types = :asset_types';
            $params[':asset_types'] = encode_asset_category_config_json($config);
        }
    }

    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }

    try {
        $sql = 'UPDATE trust_services SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        send_json(['success' => true, 'message' => 'Trust service updated successfully']);
    } catch (Exception $e) {
        error_log('Update trust failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update trust service'], 500);
    }
}

function handleDeleteTrust() {
    require_admin_auth();
    $trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $inUse = $db->prepare('SELECT COUNT(*) FROM user_trusts WHERE trust_service_id = :id');
    $inUse->execute([':id' => $trustId]);
    if ((int) $inUse->fetchColumn() > 0) {
        send_json(['success' => false, 'message' => 'Cannot delete trust service that is in use'], 400);
    }

    try {
        $stmt = $db->prepare('DELETE FROM trust_services WHERE id = :id');
        $stmt->execute([':id' => $trustId]);
        if ($stmt->rowCount() === 0) {
            send_json(['success' => false, 'message' => 'Trust service not found'], 404);
        }
        send_json(['success' => true, 'message' => 'Trust service deleted successfully']);
    } catch (Exception $e) {
        error_log('Delete trust failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to delete trust service'], 500);
    }
}
