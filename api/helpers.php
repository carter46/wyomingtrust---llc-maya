<?php

require_once __DIR__ . '/config.php';

// Start secure session if not already started
if (!function_exists('session_status') || session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    if ($isHttps) {
        @ini_set('session.cookie_secure', '1');
    }
    if (!isset($_SESSION) || !is_array($_SESSION)) {
        if (!@session_start()) {
            error_log('[helpers] Failed to start session');
        }
    }
}

/**
 * Send JSON response and exit
 */
function send_json($data, $statusCode = 200) {
    if (!is_array($data)) {
        $data = ['success' => false, 'message' => 'Invalid response payload'];
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get request method (supports method override)
 */
function get_request_method() {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST') {
        $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_GET['_method'] ?? null;
        if (is_string($override) && $override !== '') {
            return strtoupper($override);
        }
    }

    return $method;
}

/**
 * Get JSON input from request body
 */
function get_json_input() {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        send_json(['success' => false, 'message' => 'Invalid JSON payload'], 400);
    }

    return $data;
}

/**
 * Require admin authentication
 */
function require_admin_auth() {
    if (!isset($_SESSION['admin_id'])) {
        send_json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    return (int) $_SESSION['admin_id'];
}

/**
 * Clear current session (used when session becomes invalid)
 */
function logout_current_session() {
    if (!function_exists('session_status') || session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    // Clear session data
    $_SESSION = [];

    // Clear session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        @setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? false),
            (bool) ($params['httponly'] ?? true)
        );
    }

    @session_destroy();
}

/**
 * Validate that the currently logged-in user still exists in DB.
 * Returns user id if valid, otherwise false.
 */
function get_valid_session_user_id() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];
    if ($userId <= 0) {
        return false;
    }

    try {
        $db = getDatabase();
        $stmt = $db->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
    } catch (Exception $e) {
        // If DB is down, don't silently "log them out"—treat as unauthorized for APIs.
        error_log('[helpers] Failed to validate user session: ' . $e->getMessage());
        return false;
    }

    return $userId;
}

/**
 * Require user authentication
 */
function require_user_auth() {
    $userId = get_valid_session_user_id();
    if ($userId === false) {
        // Session says logged in, but user no longer exists (e.g. account deleted)
        logout_current_session();
        send_json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    return (int) $userId;
}

/**
 * Require user authentication for normal PHP pages (redirects instead of JSON).
 */
function require_user_page_auth($redirectTo = '/login.php') {
    $userId = get_valid_session_user_id();
    if ($userId === false) {
        logout_current_session();
        header('Location: ' . $redirectTo);
        exit;
    }
    return (int) $userId;
}

/**
 * Sanitize text input
 */
function sanitize_text($value) {
    $value = trim((string) $value);
    // Remove null bytes (security risk)
    $value = str_replace("\0", '', $value);
    // Normalize whitespace
    $value = preg_replace('/\s+/', ' ', $value);
    // Remove control characters except newlines and tabs
    $value = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $value);
    return $value;
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Encrypt data using AES-256-CBC
 */
function encrypt_data($data, $key = null) {
    if ($key === null) {
        $key = getEncryptionKey();
    }
    
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    if ($encrypted === false) {
        throw new RuntimeException('Encryption failed');
    }
    
    // Return base64 encoded IV + encrypted data
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt data using AES-256-CBC
 */
function decrypt_data($encryptedData, $key = null) {
    if ($key === null) {
        $key = getEncryptionKey();
    }
    
    $data = base64_decode($encryptedData, true);
    if ($data === false || strlen($data) < 16) {
        throw new RuntimeException('Invalid encrypted data');
    }
    
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    
    if ($decrypted === false) {
        throw new RuntimeException('Decryption failed');
    }
    
    return $decrypted;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require CSRF token validation for POST/PUT/DELETE
 * Supports both form submissions and JSON API requests
 */
function require_csrf_token() {
    $method = get_request_method();
    if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        // Try to get token from various sources
        $token = null;
        
        // Check POST data (form submissions)
        if (!empty($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }
        // Check JSON body (API requests)
        elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        // Try to get from request body for JSON requests
        else {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $data = json_decode($raw, true);
                if (isset($data['csrf_token'])) {
                    $token = $data['csrf_token'];
                }
            }
        }
        
        if (!$token || !validate_csrf_token($token)) {
            send_json(['success' => false, 'message' => 'Invalid or missing CSRF token'], 403);
        }
    }
}

/**
 * Sanitize HTML output
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Load public site branding settings (cached per request).
 * Falls back safely when the database is unavailable.
 *
 * @return array{site_name:string,tagline:string,logo:?string,favicon:?string}
 */
function get_site_settings() {
    static $settings = null;
    if (is_array($settings)) {
        return $settings;
    }

    $settings = [
        'site_name' => 'WyomingTrust',
        'tagline' => 'Secure Your Digital Legacy',
        'logo' => null,
        'favicon' => null,
    ];

    try {
        $db = getDatabase();
        $row = $db->query('SELECT * FROM site_settings WHERE id = 1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            if (!empty($row['site_name'])) {
                $settings['site_name'] = (string) $row['site_name'];
            }
            if (!empty($row['tagline'])) {
                $settings['tagline'] = (string) $row['tagline'];
            }
            if (!empty($row['logo'])) {
                $settings['logo'] = (string) $row['logo'];
            }
            if (!empty($row['favicon'])) {
                $settings['favicon'] = (string) $row['favicon'];
            }
        }
    } catch (Throwable $e) {
        error_log('[get_site_settings] ' . $e->getMessage());
    }

    return $settings;
}

/**
 * Resolve a project-relative asset path from the current script location.
 */
function asset_url($relativePath) {
    $relativePath = ltrim(str_replace('\\', '/', (string) $relativePath), '/');
    $projectRoot = realpath(dirname(__DIR__));
    $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $scriptDir = realpath(dirname($scriptFile) ?: $scriptFile);

    if (!$projectRoot || !$scriptDir) {
        return $relativePath;
    }

    $depth = 0;
    $current = $scriptDir;
    while ($current && $current !== $projectRoot && dirname($current) !== $current) {
        $depth++;
        $current = dirname($current);
    }

    return ($depth > 0 ? str_repeat('../', $depth) : '') . $relativePath;
}

/**
 * Validate password strength
 */
function validate_password($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    return ['valid' => true];
}

/**
 * Validate crypto address format
 */
function validate_crypto_address($address, $coinKey) {
    if (empty($address)) {
        return false;
    }
    
    $address = trim($address);
    
    // Basic validation patterns for common cryptocurrencies
    $patterns = [
        'bitcoin' => '/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^bc1[a-z0-9]{39,59}$/i',
        'ethereum' => '/^0x[a-fA-F0-9]{40}$/',
        'tether' => '/^0x[a-fA-F0-9]{40}$/',
        'usd-coin' => '/^0x[a-fA-F0-9]{40}$/',
        'binancecoin' => '/^[a-z0-9]{39,42}$/i',
        'solana' => '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/',
        'ripple' => '/^r[1-9A-HJ-NP-Za-km-z]{25,34}$/',
        'cardano' => '/^addr1[a-z0-9]{98}$|^DdzFF[a-z0-9]{98}$/i',
        'polkadot' => '/^1[a-km-zA-HJ-NP-Z1-9]{47,48}$/',
        'polygon' => '/^0x[a-fA-F0-9]{40}$/',
        'litecoin' => '/^[LM3][a-km-zA-HJ-NP-Z1-9]{26,33}$|^ltc1[a-z0-9]{39,59}$/i',
        'bitcoin-cash' => '/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^bitcoincash:[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{42}$/i',
        'default' => '/^[a-zA-Z0-9]{26,64}$/' // Generic alphanumeric for other coins
    ];
    
    $coinKeyLower = strtolower($coinKey);
    $pattern = $patterns[$coinKeyLower] ?? $patterns['default'];
    
    return preg_match($pattern, $address) === 1;
}

/**
 * Map CoinGecko coin_key to crypto symbol for FreeCryptoAPI
 * Returns symbol (e.g., "BTC", "ETH") or null if not found
 */
function coin_key_to_symbol($coinKey) {
    static $mapping = [
        'bitcoin' => 'BTC',
        'ethereum' => 'ETH',
        'litecoin' => 'LTC',
        'bitcoin-cash' => 'BCH',
        'polygon' => 'MATIC',
        'dogecoin' => 'DOGE',
        'tether' => 'USDT',
        'tron' => 'TRX',
        'ripple' => 'XRP',
        'cardano' => 'ADA',
        'solana' => 'SOL',
        'polkadot' => 'DOT',
        'binancecoin' => 'BNB',
        'usd-coin' => 'USDC',
        'avalanche-2' => 'AVAX',
        'shiba-inu' => 'SHIB',
        'chainlink' => 'LINK',
        'uniswap' => 'UNI',
        'stellar' => 'XLM',
        'algorand' => 'ALGO',
        'aptos' => 'APT',
        'arbitrum' => 'ARB',
        'fantom' => 'FTM',
        'filecoin' => 'FIL',
        'hedera-hashgraph' => 'HBAR',
        'internet-computer' => 'ICP',
        'monero' => 'XMR',
        'optimism' => 'OP',
        'cosmos' => 'ATOM',
        'vechain' => 'VET',
        'the-open-network' => 'TON',
    ];
    
    $coinKeyLower = strtolower(trim($coinKey));
    return $mapping[$coinKeyLower] ?? null;
}

/**
 * Map crypto symbol to CoinGecko coin_key (reverse lookup)
 */
function symbol_to_coin_key($symbol) {
    static $reverseMapping = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'LTC' => 'litecoin',
        'BCH' => 'bitcoin-cash',
        'MATIC' => 'polygon',
        'DOGE' => 'dogecoin',
        'USDT' => 'tether',
        'TRX' => 'tron',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'SOL' => 'solana',
        'DOT' => 'polkadot',
        'BNB' => 'binancecoin',
        'USDC' => 'usd-coin',
        'AVAX' => 'avalanche-2',
        'SHIB' => 'shiba-inu',
        'LINK' => 'chainlink',
        'UNI' => 'uniswap',
        'XLM' => 'stellar',
        'ALGO' => 'algorand',
        'APT' => 'aptos',
        'ARB' => 'arbitrum',
        'FTM' => 'fantom',
        'FIL' => 'filecoin',
        'HBAR' => 'hedera-hashgraph',
        'ICP' => 'internet-computer',
        'XMR' => 'monero',
        'OP' => 'optimism',
        'ATOM' => 'cosmos',
        'VET' => 'vechain',
        'TON' => 'the-open-network',
    ];
    
    $symbolUpper = strtoupper(trim($symbol));
    return $reverseMapping[$symbolUpper] ?? null;
}

/**
 * Get cached crypto prices from database
 * Returns array in CoinGecko format or empty array if cache expired/not found
 * Cache duration: 2 minutes (120 seconds)
 */
function get_cached_crypto_prices($coinKeys, $cacheDuration = 120) {
    if (empty($coinKeys) || !is_array($coinKeys)) {
        return [];
    }
    
    try {
        $db = getDatabase();
        $placeholders = str_repeat('?,', count($coinKeys) - 1) . '?';
        
        $stmt = $db->prepare("
            SELECT coin_key, price_usd, change_24h, last_updated
            FROM crypto_price_cache
            WHERE coin_key IN ($placeholders)
            AND TIMESTAMPDIFF(SECOND, last_updated, NOW()) <= ?
        ");
        
        $params = array_merge($coinKeys, [$cacheDuration]);
        $stmt->execute($params);
        $cached = $stmt->fetchAll();
        
        $result = [];
        foreach ($cached as $row) {
            $result[$row['coin_key']] = [
                'usd' => (float) $row['price_usd'],
                'usd_24h_change' => (float) $row['change_24h']
            ];
        }
        
        return $result;
    } catch (Exception $e) {
        error_log('Error getting cached crypto prices: ' . $e->getMessage());
        return [];
    }
}

/**
 * Save crypto prices to database cache
 */
function save_crypto_prices_to_cache($prices) {
    if (empty($prices) || !is_array($prices)) {
        return false;
    }
    
    try {
        $db = getDatabase();
        $db->beginTransaction();
        
        $stmt = $db->prepare("
            INSERT INTO crypto_price_cache (coin_key, price_usd, change_24h, last_updated)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                price_usd = VALUES(price_usd),
                change_24h = VALUES(change_24h),
                last_updated = NOW()
        ");
        
        foreach ($prices as $coinKey => $data) {
            if (isset($data['usd']) && is_numeric($data['usd'])) {
                $stmt->execute([
                    $coinKey,
                    $data['usd'],
                    $data['usd_24h_change'] ?? 0
                ]);
            }
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Error saving crypto prices to cache: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get last update time for cached prices
 */
function get_crypto_cache_last_updated($coinKeys) {
    if (empty($coinKeys) || !is_array($coinKeys)) {
        return null;
    }
    
    try {
        $db = getDatabase();
        $placeholders = str_repeat('?,', count($coinKeys) - 1) . '?';
        
        $stmt = $db->prepare("
            SELECT MAX(last_updated) as last_updated
            FROM crypto_price_cache
            WHERE coin_key IN ($placeholders)
        ");
        
        $stmt->execute($coinKeys);
        $result = $stmt->fetch();
        
        return $result ? $result['last_updated'] : null;
    } catch (Exception $e) {
        error_log('Error getting cache last updated: ' . $e->getMessage());
        return null;
    }
}

/**
 * Fetch prices from FreeCryptoAPI backup
 * Returns array in CoinGecko format: ['coin_key' => ['usd' => price, 'usd_24h_change' => change]]
 */
function fetch_freecryptoapi_prices($coinKeys) {
    if (empty($coinKeys) || !is_array($coinKeys)) {
        return [];
    }
    
    $apiKey = 'jviwgklny5hl3pcve6pn';
    $baseUrl = 'https://api.freecryptoapi.com/v1';
    
    // Convert coin_keys to symbols
    $symbols = [];
    foreach ($coinKeys as $coinKey) {
        $symbol = coin_key_to_symbol(trim($coinKey));
        if ($symbol) {
            $symbols[] = $symbol;
        }
    }
    
    if (empty($symbols)) {
        return [];
    }
    
    // Batch symbols (max 15 per batch)
    $maxBatchSize = 15;
    $symbolBatches = array_chunk(array_slice($symbols, 0, 50), $maxBatchSize);
    
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
                foreach ($priceData as $key => $value) {
                    $allPrices[$key] = $value;
                }
            } else {
                // Log if response is not valid JSON
                error_log('FreeCryptoAPI price response not valid JSON for batch: ' . $symbolsString . ' - Response: ' . substr($priceResponse, 0, 200));
            }
        } else {
            error_log('FreeCryptoAPI price request failed: Status=' . $priceStatus . ' for batch: ' . $symbolsString);
        }
        
        // Parse performance data
        if ($perfStatus === 200 && $perfResponse) {
            $perfData = json_decode($perfResponse, true);
            if (is_array($perfData)) {
                foreach ($perfData as $key => $value) {
                    $allPerformance[$key] = $value;
                }
            } else {
                // Log if response is not valid JSON (performance is optional, so just log)
                error_log('FreeCryptoAPI performance response not valid JSON for batch: ' . $symbolsString);
            }
        }
        // Performance data failure is not critical, so we don't log errors
        
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
            if (is_array($priceData)) {
                $price = $priceData['price'] ?? $priceData['last'] ?? $priceData['close'] ?? $priceData['USD'] ?? $priceData['usd'] ?? null;
            } elseif (is_numeric($priceData)) {
                $price = (float) $priceData;
            }
            
            if ($price !== null && (!is_numeric($price) || $price <= 0)) {
                $price = null;
            } elseif ($price !== null) {
                $price = (float) $price;
            }
        }
        
        // Get 24h change from performance data
        if (isset($allPerformance[$symbol])) {
            $perfData = $allPerformance[$symbol];
            if (is_array($perfData)) {
                $change24h = $perfData['change_24h'] ?? $perfData['change24h'] ?? $perfData['percent_change_24h'] ?? $perfData['percentChange24h'] ?? $perfData['changePercent'] ?? $perfData['change_percent'] ?? 0;
            } elseif (is_numeric($perfData)) {
                $change24h = (float) $perfData;
            }
            
            if (!is_numeric($change24h)) {
                $change24h = 0;
            } else {
                $change24h = (float) $change24h;
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
    
    return $coingeckoFormat;
}

/**
 * Rate limiting check
 */
function check_rate_limit($key, $max_requests = 5, $window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_key = md5($key . '_' . $ip);
    $cache_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ratelimit_' . $rate_limit_key . '.txt';
    
    $current_time = time();
    $data = [];
    
    if (file_exists($cache_file)) {
        $file_data = file_get_contents($cache_file);
        $data = json_decode($file_data, true) ?: [];
    }
    
    // Clean old entries
    $data = array_filter($data, function($timestamp) use ($current_time, $window) {
        return ($current_time - $timestamp) < $window;
    });
    
    // Check if limit exceeded
    if (count($data) >= $max_requests) {
        send_json([
            'success' => false, 
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $window - ($current_time - min($data))
        ], 429);
    }
    
    // Add current request
    $data[] = $current_time;
    
    // Save updated data
    @file_put_contents($cache_file, json_encode($data), LOCK_EX);
    
    // Cleanup old files (run 1% of the time)
    if (rand(1, 100) === 1) {
        $files = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ratelimit_*.txt');
        $expiry = $current_time - $window;
        foreach ($files as $file) {
            if (filemtime($file) < $expiry) {
                @unlink($file);
            }
        }
    }
}

/**
 * Predefined trust type catalog (service_key = trust type id).
 */
function get_trust_type_options(): array {
    return [
        'irrevocable_trust' => 'Irrevocable Trust Service',
        'revocable_living_trust' => 'Revocable Living Trust',
        'smart_contract_trust' => 'Smart Contract Trust Service',
    ];
}

function is_valid_trust_type_key(string $key): bool {
    return array_key_exists($key, get_trust_type_options()) || $key === 'crypto_asset_trust';
}

function is_crypto_trust_type(string $key): bool {
    return in_array($key, ['smart_contract_trust', 'crypto_asset_trust'], true);
}

require_once __DIR__ . '/../includes/trust-asset-catalog.php';

function trust_services_has_asset_types_column(PDO $db): bool {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $stmt = $db->query("SHOW COLUMNS FROM trust_services LIKE 'asset_types'");
        $cache = (bool) $stmt->fetch();
    } catch (Exception $e) {
        $cache = false;
    }
    return $cache;
}

function trust_services_has_asset_category_config_column(PDO $db): bool {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $stmt = $db->query("SHOW COLUMNS FROM trust_services LIKE 'asset_category_config'");
        $cache = (bool) $stmt->fetch();
    } catch (Exception $e) {
        $cache = false;
    }
    return $cache;
}

function trust_services_has_liquidation_fee_column(PDO $db): bool {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $stmt = $db->query("SHOW COLUMNS FROM trust_services LIKE 'liquidation_fee'");
        $cache = (bool) $stmt->fetch();
    } catch (Exception $e) {
        $cache = false;
    }
    return $cache;
}

function coins_has_liquidation_fee_column(PDO $db): bool {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $stmt = $db->query("SHOW COLUMNS FROM coins LIKE 'liquidation_fee'");
        $cache = (bool) $stmt->fetch();
    } catch (Exception $e) {
        $cache = false;
    }
    return $cache;
}

/**
 * Resolve USD liquidation fee for an asset (coin-level fee takes precedence over trust service fee).
 *
 * @return array{fee: float, has_fee: bool, fee_source: string}
 */
function resolve_liquidation_fee_usd(PDO $db, int $userId, string $coinKey, int $trustId = 0): array {
    $fee = 0.0;
    $feeSource = 'none';

    if (coins_has_liquidation_fee_column($db)) {
        $stmt = $db->prepare('SELECT liquidation_fee FROM coins WHERE coin_key = :coin_key LIMIT 1');
        $stmt->execute([':coin_key' => $coinKey]);
        $row = $stmt->fetch();
        if ($row && isset($row['liquidation_fee'])) {
            $coinFee = (float) $row['liquidation_fee'];
            if ($coinFee > 0) {
                $fee = $coinFee;
                $feeSource = 'coin';
            }
        }
    }

    if ($fee <= 0 && $trustId > 0 && trust_services_has_liquidation_fee_column($db)) {
        $stmt = $db->prepare(
            'SELECT ts.liquidation_fee, ts.service_key
             FROM user_trusts ut
             INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
             WHERE ut.id = :id AND ut.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
        $trust = $stmt->fetch();
        if ($trust && trust_allows_liquidation($trust['service_key'] ?? '')) {
            $trustFee = (float) ($trust['liquidation_fee'] ?? 0);
            if ($trustFee > 0) {
                $fee = $trustFee;
                $feeSource = 'trust';
            }
        }
    }

    $fee = round($fee, 2);

    return [
        'fee' => $fee,
        'has_fee' => $fee > 0,
        'fee_source' => $feeSource,
    ];
}

/**
 * Whether a liquidation fee row has already been used for an approved liquidation.
 */
function liquidation_fee_is_consumed(array $row): bool {
    $data = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];

    return !empty($data['consumed_by_liquidation_id']);
}

/**
 * Mark a completed liquidation fee as consumed by a liquidation transaction.
 */
function mark_liquidation_fee_consumed(PDO $db, int $feeTransactionId, int $liquidationId): void {
    $stmt = $db->prepare('SELECT transaction_data FROM transactions WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $feeTransactionId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return;
    }

    $data = !empty($row['transaction_data'])
        ? (json_decode($row['transaction_data'], true) ?? [])
        : [];
    $data['consumed_by_liquidation_id'] = $liquidationId;
    $data['consumed_at'] = date('c');

    $update = $db->prepare(
        'UPDATE transactions SET transaction_data = :data, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
    );
    $update->execute([
        ':id' => $feeTransactionId,
        ':data' => json_encode($data),
    ]);
}

/**
 * Find a liquidation fee payment for this asset.
 *
 * When $approvedOnly is false: pending or completed-but-unconsumed (blocks duplicate checkout).
 * When $approvedOnly is true: completed and unconsumed (authorizes liquidation).
 */
function user_has_liquidation_fee_payment(PDO $db, int $userId, int $coinId, int $trustId = 0, bool $approvedOnly = false): ?array {
    $sql = 'SELECT t.id, t.status, t.amount, t.transaction_data, t.created_at
            FROM transactions t
            WHERE t.user_id = :user_id
              AND t.type = "liquidation_fee"
              AND t.coin_id = :coin_id
              AND t.status IN ("pending", "completed")';
    $params = [':user_id' => $userId, ':coin_id' => $coinId];

    if ($trustId > 0) {
        $sql .= ' AND t.trust_id = :trust_id';
        $params[':trust_id'] = $trustId;
    }

    $sql .= ' ORDER BY t.created_at DESC LIMIT 10';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $consumed = liquidation_fee_is_consumed($row);
        if ($approvedOnly) {
            if ($row['status'] === 'completed' && !$consumed) {
                return $row;
            }
            continue;
        }
        if ($row['status'] === 'pending' || ($row['status'] === 'completed' && !$consumed)) {
            return $row;
        }
    }

    return null;
}

/**
 * Resolve trust-level liquidation fee from the trust service (all trust types).
 *
 * @return array{fee: float, has_fee: bool, allows_liquidation: bool}
 */
function resolve_trust_liquidation_fee_usd(PDO $db, int $userId, int $trustId): array {
    $extra = trust_services_has_liquidation_fee_column($db) ? ', ts.liquidation_fee' : '';
    $stmt = $db->prepare(
        "SELECT ts.service_key{$extra}
         FROM user_trusts ut
         INNER JOIN trust_services ts ON ts.id = ut.trust_service_id
         WHERE ut.id = :id AND ut.user_id = :user_id
         LIMIT 1"
    );
    $stmt->execute([':id' => $trustId, ':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return ['fee' => 0.0, 'has_fee' => false, 'allows_liquidation' => false];
    }

    $trustType = $row['service_key'] ?? '';
    $allows = trust_allows_liquidation($trustType);
    $fee = $allows && isset($row['liquidation_fee']) ? (float) $row['liquidation_fee'] : 0.0;
    $fee = round(max(0, $fee), 2);

    return [
        'fee' => $fee,
        'has_fee' => $fee > 0,
        'allows_liquidation' => $allows,
    ];
}

/**
 * Trust-level liquidation fee payment (not tied to a specific coin).
 */
function user_has_trust_liquidation_fee_payment(PDO $db, int $userId, int $trustId, bool $approvedOnly = false): ?array {
    $stmt = $db->prepare(
        'SELECT t.id, t.status, t.amount, t.transaction_data, t.created_at
         FROM transactions t
         WHERE t.user_id = :user_id
           AND t.trust_id = :trust_id
           AND t.type = "liquidation_fee"
           AND t.coin_id IS NULL
           AND t.status IN ("pending", "completed")
         ORDER BY t.created_at DESC
         LIMIT 10'
    );
    $stmt->execute([':user_id' => $userId, ':trust_id' => $trustId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = !empty($row['transaction_data'])
            ? (json_decode($row['transaction_data'], true) ?? [])
            : [];
        if (($data['purpose'] ?? '') !== 'trust_liquidation') {
            continue;
        }

        $consumed = liquidation_fee_is_consumed($row);
        if ($approvedOnly) {
            if ($row['status'] === 'completed' && !$consumed) {
                return $row;
            }
            continue;
        }
        if ($row['status'] === 'pending' || ($row['status'] === 'completed' && !$consumed)) {
            return $row;
        }
    }

    return null;
}
function get_suggested_asset_types(): array {
    return array_values(array_map(function ($cat) {
        return $cat['label'];
    }, get_trust_asset_category_catalog()));
}

/** @deprecated Use normalize_asset_category_config() */
function normalize_asset_types($input): array {
    return normalize_asset_category_config($input);
}

/** @deprecated */
function encode_asset_types_json(array $types): ?string {
    return encode_asset_category_config_json(normalize_asset_category_config($types));
}

/** @deprecated */
function decode_asset_types(?string $json): array {
    $config = decode_asset_category_config($json);
    return array_values(array_filter(array_map(function ($item) {
        return $item['key'] ?? '';
    }, $config)));
}

require_once __DIR__ . '/../includes/icons.php';
