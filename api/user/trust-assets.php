<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleGetTrustAssetsContext();
        break;
    case 'POST':
        handleSaveTrustAsset();
        break;
    case 'DELETE':
        handleDeleteTrustAsset();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function load_user_trust_with_service(PDO $db, int $userId, int $trustId): ?array {
    $cols = [
        'asset_category_config' => trust_services_has_asset_category_config_column($db),
        'asset_types' => trust_services_has_asset_types_column($db),
        'liquidation_fee' => trust_services_has_liquidation_fee_column($db),
    ];
    $extra = '';
    if ($cols['asset_category_config']) {
        $extra .= ', ts.asset_category_config';
    } elseif ($cols['asset_types']) {
        $extra .= ', ts.asset_types';
    }
    if ($cols['liquidation_fee']) {
        $extra .= ', ts.liquidation_fee';
    }

    $sql = "SELECT ut.id, ut.user_id, ut.status, ut.trust_data, ts.service_key, ts.service_name{$extra}
            FROM user_trusts ut
            INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
            WHERE ut.id = :id AND ut.user_id = :user_id LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $trustData = [];
    if (!empty($row['trust_data'])) {
        $trustData = json_decode($row['trust_data'], true) ?? [];
    }
    $row['trust_data'] = $trustData;
    $row['service_meta'] = build_trust_service_meta($row);
    return $row;
}

function handleGetTrustAssetsContext() {
    $userId = require_user_auth();
    $trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $trust = load_user_trust_with_service($db, $userId, $trustId);
    if (!$trust) {
        send_json(['success' => false, 'message' => 'Trust not found'], 404);
    }

    $assets = is_array($trust['trust_data']['assets'] ?? null) ? $trust['trust_data']['assets'] : [];
    send_json([
        'success' => true,
        'assets' => $assets,
        'summary' => compute_trust_assets_summary($assets),
        'service_meta' => $trust['service_meta'],
    ]);
}

function handleSaveTrustAsset() {
    $userId = require_user_auth();
    $payload = get_json_input();
    $trustId = isset($payload['trust_id']) ? (int) $payload['trust_id'] : 0;

    if ($trustId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
    }

    $db = getDatabase();
    $trust = load_user_trust_with_service($db, $userId, $trustId);
    if (!$trust) {
        send_json(['success' => false, 'message' => 'Trust not found'], 404);
    }

    if (empty($trust['service_meta']['supports_assets'])) {
        send_json(['success' => false, 'message' => 'This trust type does not support catalog assets'], 400);
    }

    $enabled = $trust['service_meta']['asset_categories'] ?? [];
    $normalized = normalize_trust_asset_entry(is_array($payload['asset'] ?? null) ? $payload['asset'] : [], $enabled);
    if ($normalized === null) {
        send_json(['success' => false, 'message' => 'Invalid asset data. Check required fields and category.'], 400);
    }

    $requiresDoc = false;
    foreach ($enabled as $cat) {
        if (($cat['key'] ?? '') === $normalized['category_key']) {
            $requiresDoc = !empty($cat['requires_document']);
            break;
        }
    }
    if ($requiresDoc && empty($normalized['document']['path'])) {
        send_json(['success' => false, 'message' => 'A supporting document is required for this asset category'], 400);
    }

    $trustData = $trust['trust_data'];
    $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
    $assetId = $normalized['id'];
    $found = false;
    $newUsd = get_trust_asset_usd_value($normalized);
    foreach ($assets as $i => $asset) {
        if (($asset['id'] ?? '') === $assetId) {
            $normalized['created_at'] = $asset['created_at'] ?? $normalized['created_at'];
            $oldUsd = get_trust_asset_usd_value($asset);
            $found = true;
            if (abs($oldUsd - $newUsd) > 0.001) {
                $normalized['funding_amount_usd'] = $newUsd;
                $normalized['funding_status'] = $newUsd > 0 ? 'unfunded' : 'funded';
                $normalized['funded_amount_usd'] = $newUsd > 0 ? 0.0 : 0.0;
                $normalized['funding_transaction_id'] = null;
            } else {
                $normalized['funding_amount_usd'] = (float) ($asset['funding_amount_usd'] ?? $newUsd);
                $normalized['funding_status'] = sanitize_text($asset['funding_status'] ?? ($newUsd > 0 ? 'unfunded' : 'funded'));
                $normalized['funded_amount_usd'] = (float) ($asset['funded_amount_usd'] ?? 0);
                $normalized['funding_transaction_id'] = !empty($asset['funding_transaction_id'])
                    ? (int) $asset['funding_transaction_id']
                    : null;
            }
            $assets[$i] = $normalized;
            break;
        }
    }
    if (!$found) {
        $normalized['funding_amount_usd'] = $newUsd;
        $normalized['funding_status'] = $newUsd > 0 ? 'unfunded' : 'funded';
        $normalized['funded_amount_usd'] = 0.0;
        $normalized['funding_transaction_id'] = null;
        $assets[] = $normalized;
    }
    $trustData['assets'] = array_values($assets);

    $up = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id AND user_id = :user_id');
    $up->execute([
        ':trust_data' => json_encode($trustData, JSON_UNESCAPED_UNICODE),
        ':id' => $trustId,
        ':user_id' => $userId,
    ]);

    send_json([
        'success' => true,
        'message' => $found ? 'Asset updated' : 'Asset added',
        'asset' => $normalized,
        'summary' => compute_trust_assets_summary($trustData['assets']),
        'requires_funding' => ($normalized['funding_status'] ?? '') === 'unfunded' && $newUsd > 0,
        'funding_amount' => $newUsd,
    ]);
}

function handleDeleteTrustAsset() {
    $userId = require_user_auth();
    $trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
    $assetId = sanitize_text($_GET['asset_id'] ?? '');

    if ($trustId <= 0 || $assetId === '') {
        send_json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    $db = getDatabase();
    $trust = load_user_trust_with_service($db, $userId, $trustId);
    if (!$trust) {
        send_json(['success' => false, 'message' => 'Trust not found'], 404);
    }

    $trustData = $trust['trust_data'];
    $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
    $assets = array_values(array_filter($assets, fn($a) => ($a['id'] ?? '') !== $assetId));
    $trustData['assets'] = $assets;

    $up = $db->prepare('UPDATE user_trusts SET trust_data = :trust_data WHERE id = :id AND user_id = :user_id');
    $up->execute([
        ':trust_data' => json_encode($trustData, JSON_UNESCAPED_UNICODE),
        ':id' => $trustId,
        ':user_id' => $userId,
    ]);

    send_json(['success' => true, 'message' => 'Asset removed', 'summary' => compute_trust_assets_summary($assets)]);
}
