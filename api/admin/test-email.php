<?php
// Suppress any output before JSON
if (ob_get_level()) {
    @ob_clean();
}
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

require_admin_auth();

$payload = get_json_input();
$testEmail = sanitize_text($payload['email'] ?? '');

if (empty($testEmail)) {
    send_json(['success' => false, 'message' => 'Email address is required'], 400);
}

if (!validate_email($testEmail)) {
    send_json(['success' => false, 'message' => 'Invalid email address'], 400);
}

try {
    // Check if PHPMailer exists in root directory
    // From api/admin/ we need to go up 2 levels: ../../
    $phpmailerPath = dirname(__DIR__, 2) . '/PHPMailer/PHPMailer.php'; // Root directory
    
    if (!file_exists($phpmailerPath)) {
        throw new Exception(
            'PHPMailer library not found at: ' . $phpmailerPath . '. ' .
            'Please ensure PHPMailer folder exists in your project root directory.'
        );
    }
    
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../email.php';
    
    // Get SMTP configuration
    $smtp = getSMTPConfig();
    
    // Check if SMTP is configured
    if (empty($smtp['username']) || empty($smtp['password'])) {
        send_json([
            'success' => false,
            'message' => 'SMTP configuration is incomplete. Please configure SMTP username and password in api/config.php',
            'config_status' => 'incomplete'
        ], 400);
    }
    
    // Create test email content
    $subject = 'WyomingTrust - SMTP Test Email';
    $name = 'Admin';
    
    $message = get_email_template(
        'SMTP Configuration Test',
        "Hello {$name}!",
        "This is a test email to verify your SMTP configuration is working correctly.<br><br>" .
        "If you received this email, your SMTP settings in <code>api/config.php</code> are configured properly.<br><br>" .
        "<strong>SMTP Details:</strong><br>" .
        "Host: {$smtp['host']}<br>" .
        "Port: {$smtp['port']}<br>" .
        "Encryption: {$smtp['encryption']}<br>" .
        "From: {$smtp['from_email']} ({$smtp['from_name']})<br><br>" .
        "Test sent at: " . date('Y-m-d H:i:s T'),
        null,
        null,
        "This is an automated test email. No action is required."
    );
    
    // Send test email (throwException=true + debug=true for detailed SMTP diagnostics)
    $result = send_email_phpmailer($smtp, $testEmail, $name, $subject, $message, true, true);
    
    // If we get here, email was sent successfully
    send_json([
        'success' => true,
        'message' => "Test email sent successfully to {$testEmail}. Please check your inbox.",
        'email' => $testEmail,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Throwable $e) {
    // Log detailed error information
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    $errorTrace = $e->getTraceAsString();
    
    error_log('Test email error: ' . $errorMessage);
    error_log('File: ' . $errorFile . ' Line: ' . $errorLine);
    error_log('Trace: ' . $errorTrace);
    
    // Check if PHPMailer files exist (from api/admin/ go up 2 levels to root)
    $phpmailerPath = dirname(__DIR__, 2) . '/PHPMailer/PHPMailer.php';
    $phpmailerExists = file_exists($phpmailerPath);
    
    $smtpDebug = $GLOBALS['WYOMINGTRUST_SMTP_DEBUG_LOG'] ?? null;

    $errorDetails = [
        'success' => false,
        'message' => 'Error sending test email: ' . $errorMessage,
        'error' => $errorMessage,
        'error_file' => basename($errorFile),
        'error_line' => $errorLine,
        'phpmailer_exists' => $phpmailerExists,
        'phpmailer_path' => $phpmailerPath,
        'smtp_debug' => $smtpDebug,
        'help' => $phpmailerExists ? 'PHPMailer found but error occurred. Check server logs for details.' : 'PHPMailer library not found at expected path. Please ensure PHPMailer folder exists in project root directory.'
    ];
    
    send_json($errorDetails, 500);
}
