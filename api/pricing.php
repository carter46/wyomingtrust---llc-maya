<?php

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$db = getDatabase();
$hasAssetTypes = trust_services_has_asset_types_column($db);
$assetCol = $hasAssetTypes ? ', asset_types' : '';

// Get all active trust services
$stmt = $db->query(
    "SELECT id, service_key, service_name, description{$assetCol}, price, is_free, is_active, created_at, updated_at
     FROM trust_services
     WHERE is_active = 1
     ORDER BY is_free DESC, price ASC"
);
$services = $stmt->fetchAll();

// Format as pricing plans for frontend compatibility
$plans = [];
foreach ($services as $service) {
    // Parse description for features (if description contains feature list)
    $features = [];
    $desc = $service['description'] ?? '';
    
    // Try to extract features from description (if formatted as a list)
    if (preg_match('/Features?:?\s*([^\.]+)/i', $desc, $matches)) {
        $features = array_map('trim', explode(',', $matches[1]));
    } else {
        // Default features based on service type
        $features = [
            'Secure asset protection',
            'Professional management',
            '24/7 account access',
            'Email support'
        ];
    }
    
    $price = $service['is_free'] ? 0 : (float)($service['price'] ?? 0);
    
    $plans[] = [
        'id' => $service['id'],
        'plan_name' => $service['service_name'],
        'service_key' => $service['service_key'],
        'trust_type' => $service['service_key'],
        'description' => $desc,
        'price' => $price,
        'is_free' => (bool)$service['is_free'],
        'features' => $features,
        'is_active' => (bool)$service['is_active'],
        'asset_types' => $hasAssetTypes ? decode_asset_types($service['asset_types'] ?? null) : [],
        'is_crypto' => is_crypto_trust_type($service['service_key'] ?? ''),
    ];
}

send_json(['success' => true, 'plans' => $plans]);
