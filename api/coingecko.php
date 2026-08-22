<?php
// CoinGecko API proxy to avoid CORS on shared hosting with FreeCryptoAPI backup
// Usage: /api/coingecko.php?path=/simple/price&ids=bitcoin&vs_currencies=usd

require_once __DIR__ . '/helpers.php';

// Security: Only allow from same origin - NO wildcards for crypto security
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$protocol = $isHttps ? 'https' : 'http';
$allowedOrigin = ($origin === $protocol . '://' . $host) ? $origin : ($protocol . '://' . $host);

header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

$base = 'https://api.coingecko.com/api/v3';
$path = isset($_GET['path']) ? $_GET['path'] : '/simple/price';

// Whitelist allowed paths
$allowed = ['/simple/price','/coins/markets','/coins/bitcoin/market_chart','/coins/ethereum/market_chart'];
if (!in_array($path, $allowed) && !preg_match('#^/coins/[^/]+/market_chart$#', $path)) {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Path not allowed']);
  exit;
}

$query = $_GET;
unset($query['path']);
$url = $base . $path;
if (!empty($query)) {
  $url .= '?' . http_build_query($query);
}

// For /simple/price endpoint, check cache FIRST before calling CoinGecko
$cachedData = null;
$cacheLastUpdated = null;
$coinKeys = [];
if ($path === '/simple/price') {
    $ids = $_GET['ids'] ?? '';
    if (!empty($ids)) {
        $coinKeys = array_filter(array_map('trim', explode(',', $ids)));
        if (!empty($coinKeys)) {
            // Check cache first (2 minute cache duration)
            $cachedData = get_cached_crypto_prices($coinKeys, 120);
            $cacheLastUpdated = get_crypto_cache_last_updated($coinKeys);
            
            // If we have cached data for all requested coins, return it immediately
            $requestedCount = count($coinKeys);
            $cachedCount = count($cachedData);
            
            if ($cachedCount >= $requestedCount) {
                header('Content-Type: application/json');
                header('Cache-Control: public, max-age=30');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');
                header('X-Price-Source: cache');
                if ($cacheLastUpdated) {
                    header('X-Cache-Last-Updated: ' . $cacheLastUpdated);
                }
                http_response_code(200);
                echo json_encode($cachedData);
                exit;
            }
        }
    }
}

// Call CoinGecko API (only if cache miss or expired)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Accept: application/json',
  'User-Agent: wyomingtrust-proxy'
]);

// Call CoinGecko API (only if cache miss or expired)
$startTime = microtime(true);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
$timeElapsed = microtime(true) - $startTime;
curl_close($ch);

// Check if we should use backup
$shouldUseBackup = false;
$backupReason = '';

// Only use backup for /simple/price endpoint (charts need CoinGecko)
if ($path === '/simple/price') {
    if ($status === 429) {
        $shouldUseBackup = true;
        $backupReason = 'rate_limit';
    } elseif ($timeElapsed > 15 || !empty($err) || ($status >= 500) || ($status < 200 && $status > 0)) {
        $shouldUseBackup = true;
        $backupReason = 'timeout_or_error';
    } elseif (!($response && $status >= 200 && $status < 300)) {
        $shouldUseBackup = true;
        $backupReason = 'invalid_response';
    }
    
    // Also check if response contains error
    if (!$shouldUseBackup && $response) {
        $decoded = json_decode($response, true);
        if (is_array($decoded) && isset($decoded['error'])) {
            $shouldUseBackup = true;
            $backupReason = 'api_error';
        }
    }
}

// Try backup if CoinGecko failed
$finalData = null;
if ($shouldUseBackup && $path === '/simple/price' && !empty($coinKeys)) {
    try {
        $backupData = fetch_freecryptoapi_prices($coinKeys);
        if (!empty($backupData) && is_array($backupData)) {
            $finalData = $backupData;
            // Save backup data to cache
            save_crypto_prices_to_cache($backupData);
        }
    } catch (Exception $e) {
        error_log('FreeCryptoAPI backup exception: ' . $e->getMessage());
    }
} elseif ($status >= 200 && $status < 300 && $response) {
    // CoinGecko success - parse and use data
    $decoded = json_decode($response, true);
    if (is_array($decoded) && !isset($decoded['error'])) {
        $finalData = $decoded;
        // Save successful CoinGecko data to cache
        if ($path === '/simple/price') {
            save_crypto_prices_to_cache($finalData);
        }
    }
}

// If we have cached data and API failed, return cache as fallback
if (empty($finalData) && !empty($cachedData) && $path === '/simple/price') {
    $finalData = $cachedData;
    header('X-Price-Source: cache-fallback');
    if ($cacheLastUpdated) {
        header('X-Cache-Last-Updated: ' . $cacheLastUpdated);
    }
} elseif (!empty($finalData) && $path === '/simple/price') {
    header('X-Price-Source: ' . ($shouldUseBackup ? 'backup' : 'coingecko'));
}

// Return final data if available (for /simple/price endpoint)
if (!empty($finalData) && $path === '/simple/price') {
    header('Content-Type: application/json');
    header('Cache-Control: public, max-age=30');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');
    http_response_code(200);
    echo json_encode($finalData);
    exit;
}

// Set headers for CoinGecko response (only if backup didn't work)
header('Content-Type: application/json');

// Add cache headers for successful responses
if ($status >= 200 && $status < 300) {
  header('Cache-Control: public, max-age=30'); // Cache for 30 seconds
  header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');
} else if ($status === 429) {
  // Rate limited - shorter cache
  header('Cache-Control: public, max-age=10');
  header('Retry-After: 60'); // Suggest retry after 60 seconds
}

http_response_code($status > 0 ? $status : 502);
if ($response && $status >= 200 && $status < 500) {
  echo $response;
} else {
  echo json_encode(['error' => 'Upstream error', 'status' => $status, 'detail' => $err, 'backup_attempted' => $shouldUseBackup]);
}
