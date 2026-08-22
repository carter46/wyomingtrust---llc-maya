<?php
/**
 * Wyoming Trust Platform - Global configuration and database bootstrap.
 */

/**
 * Legacy function for backwards compatibility.
 * All configuration is now done directly in this config.php file.
 * This function simply returns the default value provided.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed|null
 * @deprecated Configuration should be set directly in config.php
 */
function envValue($key, $default = null)
{
    // All configuration is now done directly in config.php
    // This function is kept for backwards compatibility only
    return $default;
}

/**
 * Get the PDO connection to the MySQL database.
 *
 * @return PDO
 */
function getDatabase()
{
    static $db = null;

    if ($db instanceof PDO) {
        return $db;
    }

    if (!class_exists('PDO')) {
        throw new RuntimeException('PDO extension is not available on this server.');
    }

    $requiredDrivers = ['mysql'];
    $drivers = PDO::getAvailableDrivers();
    foreach ($requiredDrivers as $driver) {
        if (!is_array($drivers) || !in_array($driver, $drivers, true)) {
            throw new RuntimeException(sprintf('PDO %s driver is not enabled on this server.', $driver));
        }
    }

    // ====================================================================
    // DATABASE CONFIGURATION
    // ====================================================================
    // Edit these values directly to match your database settings
    $host = 'localhost';
    $port = '3306';
    $database = 'u502532383_wyoming';  // Change this to your database name
    $username = 'u502532383_wyoming';  // Change this to your MySQL username
    $password = 'Secretpass0721//';    // Change this to your database password
    $charset = 'utf8mb4';

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port,
        $database,
        $charset
    );

    try {
        $db = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        return $db;
    } catch (PDOException $e) {
        error_log('[getDatabase] PDO Error: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
    }
}

/**
 * Get encryption key for wallet encryption.
 *
 * @return string
 */
function getEncryptionKey()
{
    // ====================================================================
    // ENCRYPTION KEY CONFIGURATION
    // ====================================================================
    // Change this to a secure random string (minimum 32 characters)
    // Generate a secure key: openssl rand -hex 32
    $key = 'default_encryption_key_change_in_production_min_32_chars';
    
    if (strlen($key) < 32) {
        error_log('[SECURITY WARNING] Encryption key is too short! Must be at least 32 characters.');
        $key = 'default_encryption_key_change_in_production_min_32_chars';
    }
    
    // Return raw binary hash (32 bytes) for AES-256-CBC
    return hash('sha256', $key, true);
}

/**
 * Get SMTP configuration for email sending.
 *
 * @return array
 */
function getSMTPConfig()
{
    // ====================================================================
    // SITE URL CONFIGURATION
    // ====================================================================
    // Base URL of your website (used for email links)
    $site_url = 'http://localhost';  // Change this to your actual site URL (e.g., 'https://wyomingtrust.com')
    
    // ====================================================================
    // SMTP EMAIL CONFIGURATION
    // ====================================================================
    // Edit these values directly with your SMTP server credentials
    
    // SMTP Server Settings
    $smtp_host = 'smtp.hostinger.com';        // SMTP server hostname (e.g., smtp.gmail.com, smtp.mail.yahoo.com)
    $smtp_port = 465;                      // SMTP port (usually 587 for TLS, 465 for SSL)
    $smtp_encryption = 'ssl';              // Encryption type: 'tls' or 'ssl'
    
    // SMTP Authentication
    $smtp_username = 'support@zentropay-global.pro';  // Your SMTP username (usually your email address)
    $smtp_password = 'Secretpass0721//';     // Your SMTP password (for Gmail, use an App Password)
    
    // Email From Settings
    $smtp_from_email = 'support@zentropay-global.pro';  // Email address to send from
    $smtp_from_name = 'WyomingTrust';               // Display name for sent emails
    
    // Note: For Gmail, you need to:
    // 1. Enable 2-Factor Authentication on your Google account
    // 2. Generate an App Password at: https://myaccount.google.com/apppasswords
    // 3. Use that App Password (not your regular password) in $smtp_password above
    
    return [
        'host' => $smtp_host,
        'port' => $smtp_port,
        'username' => $smtp_username,
        'password' => $smtp_password,
        'encryption' => $smtp_encryption,
        'from_email' => $smtp_from_email,
        'from_name' => $smtp_from_name,
    ];
}

/**
 * Get site URL for use in emails and redirects
 * Auto-detects from current request (supports multiple domains)
 *
 * @return string
 */
function getSiteUrl() {
    // Auto-detect SITE_URL from current request
    // Falls back to hardcoded value if detection fails
    if (!empty($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                    ? 'https' : 'http';
        
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // Remove port if it's the default port (80 for http, 443 for https)
        $port = $_SERVER['SERVER_PORT'] ?? null;
        if ($port && (($protocol === 'http' && $port != 80) || ($protocol === 'https' && $port != 443))) {
            $host .= ':' . $port;
        }
        
        return $protocol . '://' . $host;
    }
    
    // Fallback to localhost if auto-detection fails
    return 'http://localhost';
}
