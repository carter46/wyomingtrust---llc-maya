<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$db = getDatabase();
$cols = [
    'asset_types' => trust_services_has_asset_types_column($db),
    'asset_category_config' => trust_services_has_asset_category_config_column($db),
    'liquidation_fee' => trust_services_has_liquidation_fee_column($db),
];
$extra = '';
if ($cols['asset_category_config']) {
    $extra .= ', asset_category_config';
} elseif ($cols['asset_types']) {
    $extra .= ', asset_types';
}
if ($cols['liquidation_fee']) {
    $extra .= ', liquidation_fee';
}

$sql = "SELECT id, service_key, service_name, description{$extra}, price, is_free, is_active, created_at, updated_at
        FROM trust_services WHERE is_active = 1 ORDER BY service_name";

$stmt = $db->query($sql);
$services = $stmt->fetchAll();

foreach ($services as &$s) {
    $s['id'] = (int) ($s['id'] ?? 0);
    $s['price'] = (float) ($s['price'] ?? 0);
    $s['is_free'] = (int) ($s['is_free'] ?? 0);
    $s['is_active'] = (int) ($s['is_active'] ?? 0);
    $meta = build_trust_service_meta($s);
    $s = array_merge($s, $meta);
}
unset($s);

send_json(['success' => true, 'services' => $services]);
