<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListAssets();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListAssets() {
    $userId = require_user_auth();
    $db = getDatabase();
    
    $stmt = $db->prepare(
        'SELECT ua.id, ua.balance, ua.coin_id, c.coin_key, c.display_name, c.symbol, c.logo
         FROM user_assets ua
         INNER JOIN coins c ON c.id = ua.coin_id
         WHERE ua.user_id = :user_id
         ORDER BY c.display_name'
    );
    $stmt->execute([':user_id' => $userId]);
    $assets = $stmt->fetchAll();
    
    // Fetch real-time prices from CoinGecko
    if (!empty($assets)) {
        $coinKeys = array_column($assets, 'coin_key');
        $prices = fetchCoinGeckoPrices($coinKeys);
        
        // Add price data to each asset
        foreach ($assets as &$asset) {
            $coinKey = $asset['coin_key'];
            if (isset($prices[$coinKey])) {
                $asset['price_usd'] = $prices[$coinKey]['usd'] ?? 0;
                $asset['price_change_24h'] = $prices[$coinKey]['usd_24h_change'] ?? 0;
                $asset['value_usd'] = (float)($asset['balance'] ?? 0) * (float)($asset['price_usd'] ?? 0);
            } else {
                $asset['price_usd'] = 0;
                $asset['price_change_24h'] = 0;
                $asset['value_usd'] = 0;
            }
        }
    }
    
    send_json(['success' => true, 'assets' => $assets]);
}

function fetchCoinGeckoPrices($coinKeys) {
    if (empty($coinKeys)) {
        return [];
    }
    
    // Map coin_key to CoinGecko IDs (coin_key should match CoinGecko ID)
    $coinIds = array_filter($coinKeys, function($key) {
        return !empty($key);
    });
    
    if (empty($coinIds)) {
        return [];
    }
    
    // Limit to 30 coins per request to avoid rate limits
    $coinIds = array_slice($coinIds, 0, 30);
    $idsParam = implode(',', array_map('urlencode', $coinIds));
    
    // Use our proxy to fetch prices
    $proxyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
        . '://' . $_SERVER['HTTP_HOST'] 
        . '/api/coingecko.php?path=/simple/price&ids=' . $idsParam 
        . '&vs_currencies=usd&include_24hr_change=true';
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data && is_array($data) && !isset($data['error'])) {
            // CoinGecko returns data in format: { "bitcoin": { "usd": 50000, "usd_24h_change": 2.5 } }
            return $data;
        }
    }
    
    // Return empty array on error - frontend will handle fallback
    error_log('CoinGecko API error: HTTP ' . $httpCode . ' - Response: ' . substr($response, 0, 200));
    return [];
}
