<?php

require_once __DIR__ . '/config.php';

// Note: PHPMailer classes will be loaded inside send_email_phpmailer() function
// This matches the working cosmopolitan bank implementation

/**
 * Generate professional email template with site branding
 *
 * @param string $title Email title/heading
 * @param string $greeting Greeting text (e.g., "Welcome, John!")
 * @param string $message Main message content
 * @param string|null $buttonText Button text (if null, no button)
 * @param string|null $buttonUrl Button URL (if null, no button)
 * @param string|null $footerText Custom footer text (if null, uses default)
 * @return string HTML email template
 */
function get_email_template($title, $greeting, $message, $buttonText = null, $buttonUrl = null, $footerText = null) {
    $siteName = 'WyomingTrust';
    $currentYear = date('Y');
    
    // Logo icon (Material Icons account_balance)
    $logoIcon = 'account_balance';
    
    // Default footer text
    if ($footerText === null) {
        $footerText = "&copy; {$currentYear} {$siteName}. All rights reserved.";
    }
    
    $buttonHtml = '';
    if ($buttonText && $buttonUrl) {
        $buttonHtml = "
            <div style='text-align: center; margin: 32px 0;'>
                <a href='{$buttonUrl}' style='display: inline-block; padding: 14px 28px; background-color: #F59E0B; color: #0F172A; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; font-family: Inter, sans-serif;'>{$buttonText}</a>
            </div>
        ";
    }
    
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <title>{$title}</title>
        <!--[if mso]>
        <style type='text/css'>
            body, table, td, a { font-family: Arial, sans-serif !important; }
        </style>
        <![endif]-->
    </head>
    <body style='margin: 0; padding: 0; background-color: #F8FAFC; font-family: Inter, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; line-height: 1.6; color: #0F172A;'>
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #F8FAFC; padding: 20px 0;'>
            <tr>
                <td align='center'>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='max-width: 600px; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                        <!-- Header -->
                        <tr>
                            <td style='background-color: #F59E0B; padding: 32px 40px; text-align: center;'>
                                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                    <tr>
                                        <td align='center'>
                                            <div style='display: inline-block; background-color: rgba(15, 23, 42, 0.1); padding: 12px; border-radius: 8px; margin-bottom: 16px;'>
                                                <span style='font-size: 32px; color: #0F172A; font-weight: bold;'>🏛️</span>
                                            </div>
                                            <h1 style='margin: 0; font-family: Lexend, sans-serif; font-size: 28px; font-weight: bold; color: #0F172A; letter-spacing: -0.5px;'>
                                                {$siteName}
                                            </h1>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style='padding: 40px; background-color: #FFFFFF;'>
                                <h2 style='margin: 0 0 16px 0; font-family: Lexend, sans-serif; font-size: 24px; font-weight: bold; color: #0F172A;'>
                                    {$title}
                                </h2>
                                
                                <p style='margin: 0 0 20px 0; font-size: 16px; color: #334155; font-family: Inter, sans-serif;'>
                                    {$greeting}
                                </p>
                                
                                <div style='font-size: 16px; color: #0F172A; font-family: Inter, sans-serif; line-height: 1.8;'>
                                    {$message}
                                </div>
                                
                                {$buttonHtml}
                                
                                <!-- Fallback link if button doesn't work -->
                                " . ($buttonText && $buttonUrl ? "
                                <p style='margin: 24px 0 0 0; font-size: 14px; color: #64748B; font-family: Inter, sans-serif;'>
                                    If the button doesn't work, copy and paste this link into your browser:
                                </p>
                                <p style='margin: 8px 0 0 0; word-break: break-all; font-size: 12px; color: #0F172A; font-family: monospace; background-color: #F1F5F9; padding: 12px; border-radius: 6px;'>
                                    {$buttonUrl}
                                </p>
                                " : "") . "
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background-color: #0F172A; padding: 32px 40px; text-align: center;'>
                                <p style='margin: 0 0 12px 0; font-size: 14px; color: #94A3B8; font-family: Inter, sans-serif;'>
                                    {$footerText}
                                </p>
                                <p style='margin: 0; font-size: 12px; color: #64748B; font-family: Inter, sans-serif;'>
                                    This email was sent by {$siteName}<br>
                                    Secure Your Digital Legacy
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Bottom spacing -->
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                        <tr>
                            <td style='padding: 20px; text-align: center;'>
                                <p style='margin: 0; font-size: 12px; color: #94A3B8; font-family: Inter, sans-serif;'>
                                    If you did not expect this email, please ignore it.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";
}

/**
 * Send verification email to user
 *
 * @param string $email
 * @param string $name
 * @param string $token
 * @return bool
 */
function send_verification_email($email, $name, $otpCode) {
    $smtp = getSMTPConfig();
    
    if (empty($smtp['username']) || empty($smtp['password'])) {
        error_log('SMTP configuration missing - cannot send email');
        return false;
    }
    
    $subject = 'Verify Your WyomingTrust Account';
    
    $greeting = "Welcome, {$name}!";
    $message = "
        <p>Thank you for registering with WyomingTrust. To complete your registration, please verify your email address using the code below:</p>
        <div style='text-align: center; margin: 32px 0; padding: 24px; background-color: #F8FAFC; border: 2px solid #F59E0B; border-radius: 12px;'>
            <p style='margin: 0 0 12px 0; font-size: 14px; color: #64748B; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;'>Your Verification Code</p>
            <p style='margin: 0; font-size: 36px; font-weight: 700; color: #0F172A; letter-spacing: 8px; font-family: monospace;'>{$otpCode}</p>
            <p style='margin: 12px 0 0 0; font-size: 12px; color: #94A3B8;'>This code will expire in 24 hours.</p>
        </div>
        <p>Enter this code in the onboarding process to continue creating your trust.</p>
    ";
    $footerText = "If you didn't create an account with WyomingTrust, please ignore this email.";
    
    $emailBody = get_email_template(
        'Verify Your Email Address',
        $greeting,
        $message,
        null, // No button, just OTP code
        null,
        $footerText
    );
    
    return send_email_phpmailer($smtp, $email, $name, $subject, $emailBody);
}

/**
 * Send email using PHPMailer
 *
 * @param array $smtp
 * @param string $email
 * @param string $name
 * @param string $subject
 * @param string $message
 * @param bool $throwException If true, throws exception on error instead of returning false
 * @return bool Returns true on success, false on error (when throwException is false)
 * @throws Exception If throwException is true and error occurs
 */
function send_email_phpmailer($smtp, $email, $name, $subject, $message, $throwException = false, $debug = false) {
    try {
        // Optional SMTP debug log capture (admin-only test can enable this)
        if ($debug) {
            $GLOBALS['WYOMINGTRUST_SMTP_DEBUG_LOG'] = [];
        }

        // Load PHPMailer classes (matches cosmopolitan bank implementation)
        // From api/ we go up 1 level: ../
        $phpmailerDir = __DIR__ . '/../PHPMailer';
        if (!file_exists($phpmailerDir . '/PHPMailer.php')) {
            // Try alternative path
            $phpmailerDir = dirname(__DIR__) . '/PHPMailer';
            if (!file_exists($phpmailerDir . '/PHPMailer.php')) {
                throw new \Exception('PHPMailer library not found. Expected at: ' . __DIR__ . '/../PHPMailer/PHPMailer.php');
            }
        }
        require_once $phpmailerDir . '/PHPMailer.php';
        require_once $phpmailerDir . '/SMTP.php';
        require_once $phpmailerDir . '/Exception.php';
        
        // Use fully qualified namespace (matches cosmopolitan bank)
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings - Use SMTP instead of mail()
        $mail->isSMTP();

        if ($debug) {
            $mail->SMTPDebug = 2; // client + server
            $mail->Debugoutput = function ($str, $level) {
                // Redact any long tokens / base64 blobs to avoid leaking credentials
                $redacted = preg_replace('/[A-Za-z0-9+\/=]{20,}/', '[REDACTED]', (string) $str);
                $GLOBALS['WYOMINGTRUST_SMTP_DEBUG_LOG'][] = $redacted;
            };
        }

        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        
        // Determine encryption based on port (EXACTLY like cosmopolitan bank)
        // PHPMailer 6.0.5 uses string values: 'ssl' for port 465, 'tls' for port 587
        // NOTE: Only set SMTPSecure for ports 465 and 587, do NOT set it for other ports
        $port = (int) $smtp['port'];
        if ($port == 465) {
            $mail->SMTPSecure = 'ssl'; // SSL encryption
        } elseif ($port == 587) {
            $mail->SMTPSecure = 'tls'; // TLS encryption
        }
        // Do NOT set SMTPSecure for other ports (let PHPMailer handle it)
        
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($email, $name);
        $mail->addReplyTo($smtp['from_email'], $smtp['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        // Plain text alternative (exactly like cosmopolitan bank)
        $plainText = trim(
            preg_replace(
                "/[ \t]+/",
                ' ',
                html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8')
            )
        );
        if ($plainText === '') {
            $plainText = 'This email contains HTML content. Please view it in an HTML-capable email client.';
        }
        $mail->AltBody = $plainText;
        
        // Additional headers for deliverability (like cosmopolitan bank)
        // IMPORTANT: Do NOT add a custom Message-ID header.
        // PHPMailer generates Message-ID automatically; adding another causes Gmail to reject
        // the email as "not RFC 5322 compliant" (multiple Message-ID headers).
        $siteUrl = getSiteUrl();
        $mail->addCustomHeader('List-Unsubscribe', '<' . $siteUrl . '/unsubscribe>, <mailto:' . $smtp['from_email'] . '?subject=Unsubscribe>');
        $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('Importance', 'Normal');
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
        
        // Send email using SMTP (bypasses mail() limits)
        $result = $mail->send();
        
        if (!$result) {
            error_log("Email error: Failed to send email to $email - " . $mail->ErrorInfo);
            if ($throwException) {
                throw new \Exception($mail->ErrorInfo);
            }
            return false;
        }
        
        return true;
        
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        // Catch PHPMailer-specific exceptions (exactly like cosmopolitan bank)
        error_log("Email error: " . $e->getMessage());
        if ($throwException) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        return false;
        
    } catch (\Exception $e) {
        // Catch generic PHP exceptions (exactly like cosmopolitan bank)
        error_log("Email error: " . $e->getMessage());
        if ($throwException) {
            throw $e;
        }
        return false;
    }
}

/**
 * Send password reset email
 *
 * @param string $email
 * @param string $name
 * @param string $token
 * @return bool
 */
function send_password_reset_email($email, $name, $token) {
    $smtp = getSMTPConfig();
    
    if (empty($smtp['username']) || empty($smtp['password'])) {
        error_log('SMTP configuration missing - cannot send email');
        return false;
    }
    
    // config.php is already required at top of this file, but require_once is safe
    // Ensure getSiteUrl() is available (from config.php)
    if (!function_exists('getSiteUrl')) {
        require_once __DIR__ . '/config.php';
    }
    $siteUrl = getSiteUrl();
    $resetUrl = $siteUrl . '/reset-password.php?token=' . urlencode($token);
    
    $subject = 'Reset Your WyomingTrust Password';
    
    $greeting = "Hello {$name},";
    $message = "
        <p>We received a request to reset your password for your WyomingTrust account.</p>
        <p>Click the button below to reset your password:</p>
    ";
    $footerText = "This link will expire in 1 hour. If you didn't request a password reset, please ignore this email and your password will remain unchanged.";
    
    $emailBody = get_email_template(
        'Password Reset Request',
        $greeting,
        $message,
        'Reset Password',
        $resetUrl,
        $footerText
    );
    
    return send_email_phpmailer($smtp, $email, $name, $subject, $emailBody);
}
