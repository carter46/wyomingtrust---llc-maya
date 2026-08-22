<?php
// FreeCryptoAPI proxy as backup for CoinGecko
// Usage: /api/freecryptoapi.php?symbols=BTC,ETH,LTC or /api/freecryptoapi.php?ids=bitcoin,ethereum

require_once __DIR__ . '/helpers.php';

// Security: Only allow from same origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$protocol = $isHttps ? 'https' : 'http';
$allowedOrigin = ($origin === $protocol . '://' . $host) ? $origin : ($protocol . '://' . $host);

header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// FreeCryptoAPI configuration
$apiKey = 'jviwgklny5hl3pcve6pn';
$baseUrl = 'https://api.freecryptoapi.com/v1';

// Get symbols from query params
// Support both 'ids' (coin_key) and 'symbols' (direct symbols)
$idsParam = $_GET['ids'] ?? '';
$symbolsParam = $_GET['symbols'] ?? '';

if (empty($idsParam) && empty($symbolsParam)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Missing ids or symbols parameter']);
    exit;
}

// Convert coin_keys to symbols if ids provided
$symbols = [];
if (!empty($idsParam)) {
    $coinKeys = explode(',', $idsParam);
    foreach ($coinKeys as $coinKey) {
        $symbol = coin_key_to_symbol(trim($coinKey));
        if ($symbol) {
            $symbols[] = $symbol;
        }
    }
} else {
    $symbols = array_filter(array_map('trim', explode(',', $symbolsParam)));
}

if (empty($symbols)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'No valid symbols found']);
    exit;
}

// Limit batch size to avoid issues
$maxBatchSize = 15;
$symbolBatches = array_chunk(array_slice($symbols, 0, 50), $maxBatchSize); // Max 50 total symbols

$allPrices = [];
$allPerformance = [];

// Fetch prices and performance data
foreach ($symbolBatches as $batch) {
    $symbolsString = implode('+', $batch);
    
    // Fetch price data
    $priceUrl = $baseUrl . '/getData?symbol=' . urlencode($symbolsString);
    $ch = curl_init($priceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'apikey: ' . $apiKey,
        'User-Agent: wyomingtrust-proxy'
    ]);
    
    $priceResponse = curl_exec($ch);
    $priceStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Fetch performance data for 24h change
    $perfUrl = $baseUrl . '/getPerformance?symbol=' . urlencode($symbolsString);
    $ch = curl_init($perfUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'apikey: ' . $apiKey,
        'User-Agent: wyomingtrust-proxy'
    ]);
    
    $perfResponse = curl_exec($ch);
    $perfStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Parse price data
    if ($priceStatus === 200 && $priceResponse) {
        $priceData = json_decode($priceResponse, true);
        if (is_array($priceData)) {
            // Handle both object-style and array-style responses
            foreach ($priceData as $key => $value) {
                $allPrices[$key] = $value;
            }
        }
    }
    
    // Parse performance data
    if ($perfStatus === 200 && $perfResponse) {
        $perfData = json_decode($perfResponse, true);
        if (is_array($perfData)) {
            // Handle both object-style and array-style responses
            foreach ($perfData as $key => $value) {
                $allPerformance[$key] = $value;
            }
        }
    }
    
    // Small delay between batches
    if (count($symbolBatches) > 1) {
        usleep(200000); // 0.2 seconds
    }
}

// Convert to CoinGecko format
$coingeckoFormat = [];

foreach ($symbols as $symbol) {
    $coinKey = symbol_to_coin_key($symbol);
    if (!$coinKey) {
        continue;
    }
    
    $price = null;
    $change24h = 0;
    
    // Get price from price data
    if (isset($allPrices[$symbol])) {
        $priceData = $allPrices[$symbol];
        // Handle both array and scalar values
        if (is_array($priceData)) {
            // FreeCryptoAPI might return price in different field names
            $price = $priceData['price'] ?? $priceData['last'] ?? $priceData['close'] ?? $priceData['USD'] ?? $priceData['usd'] ?? null;
        } elseif (is_numeric($priceData)) {
            // Direct numeric value
            $price = (float) $priceData;
        } else {
            $price = null;
        }
        
        // Validate price
        if ($price !== null && (!is_numeric($price) || $price <= 0)) {
            $price = null;
        }
    }
    
    // Get 24h change from performance data
    if (isset($allPerformance[$symbol])) {
        $perfData = $allPerformance[$symbol];
        // Handle both array and scalar values
        if (is_array($perfData)) {
            // Try different field names for 24h change
            $change24h = $perfData['change_24h'] ?? $perfData['change24h'] ?? $perfData['percent_change_24h'] ?? $perfData['percentChange24h'] ?? $perfData['changePercent'] ?? $perfData['change_percent'] ?? 0;
        } elseif (is_numeric($perfData)) {
            // Direct numeric value
            $change24h = (float) $perfData;
        } else {
            $change24h = 0;
        }
        
        // Validate change value
        if (!is_numeric($change24h)) {
            $change24h = 0;
        }
    }
    
    // Only add if we have price data
    if ($price !== null) {
        $coingeckoFormat[$coinKey] = [
            'usd' => $price,
            'usd_24h_change' => $change24h
        ];
    }
}

// Add cache headers
if (!empty($coingeckoFormat)) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=30');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');
        http_response_code(200);
    }
    echo json_encode($coingeckoFormat);
} else {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(502);
    }
    echo json_encode(['error' => 'Failed to fetch prices from FreeCryptoAPI', 'detail' => 'No valid price data returned']);
}
