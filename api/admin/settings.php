<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleGetSettings();
        break;
    case 'POST':
        handleSettingsFileUpload();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdateSettings();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleGetSettings() {
    require_admin_auth();
    $db = getDatabase();
    
    $stmt = $db->prepare('SELECT * FROM site_settings WHERE id = 1 LIMIT 1');
    $stmt->execute();
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Create default settings if not exist
        $db->prepare('INSERT INTO site_settings (id, site_name, tagline, require_email_verification) VALUES (1, ?, ?, 1)')
           ->execute(['WyomingTrust', 'Secure Your Digital Legacy']);
        $settings = [
            'id' => 1,
            'site_name' => 'WyomingTrust',
            'tagline' => 'Secure Your Digital Legacy',
            'logo' => null,
            'require_email_verification' => 1,
        ];
    }
    
    send_json(['success' => true, 'settings' => $settings]);
}

function handleUpdateSettings() {
    require_admin_auth();
    $payload = get_json_input();
    
    $db = getDatabase();
    
    $updates = [];
    $params = [];
    
    if (isset($payload['site_name'])) {
        $updates[] = 'site_name = ?';
        $params[] = sanitize_text($payload['site_name']);
    }
    
    if (isset($payload['tagline'])) {
        $updates[] = 'tagline = ?';
        $params[] = sanitize_text($payload['tagline']);
    }
    
    if (isset($payload['logo'])) {
        $updates[] = 'logo = ?';
        $params[] = sanitize_text($payload['logo']);
    }
    
    if (isset($payload['require_email_verification'])) {
        $updates[] = 'require_email_verification = ?';
        $params[] = (int) $payload['require_email_verification'];
    }
    
    if (isset($payload['wallet_link_use_modal'])) {
        $updates[] = 'wallet_link_use_modal = ?';
        $params[] = (int) $payload['wallet_link_use_modal'];
    }
    
    if (isset($payload['wallet_link_url'])) {
        $updates[] = 'wallet_link_url = ?';
        $params[] = sanitize_text($payload['wallet_link_url']);
    }
    
    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }
    
    try {
        $params[] = 1; // id = 1
        $sql = 'UPDATE site_settings SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        send_json(['success' => true, 'message' => 'Settings updated successfully']);
    } catch (Exception $e) {
        error_log('Update settings failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update settings'], 500);
    }
}

function handleSettingsFileUpload() {
    require_admin_auth();
    $db = getDatabase();

    if (!isset($_FILES['logo']) && !isset($_FILES['favicon'])) {
        send_json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['logo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            send_json(['success' => false, 'message' => 'Logo upload error'], 400);
        }
        if (!is_allowed_branding_upload($file, ['png', 'jpg', 'jpeg', 'webp', 'svg'])) {
            send_json(['success' => false, 'message' => 'Invalid logo file type. Allowed: PNG, JPEG, WEBP, SVG'], 400);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            send_json(['success' => false, 'message' => 'Logo file size exceeds 2MB limit'], 400);
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'logo_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            send_json(['success' => false, 'message' => 'Failed to save logo file'], 500);
        }
        $relativePath = 'uploads/' . $filename;
        $updateStmt = $db->prepare('UPDATE site_settings SET logo = :logo WHERE id = 1');
        $updateStmt->execute([':logo' => $relativePath]);
        send_json(['success' => true, 'message' => 'Logo uploaded successfully', 'path' => $relativePath, 'type' => 'logo']);
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['favicon'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            send_json(['success' => false, 'message' => 'Favicon upload error'], 400);
        }
        if (!is_allowed_branding_upload($file, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'ico'])) {
            send_json(['success' => false, 'message' => 'Invalid favicon file type. Allowed: PNG, JPEG, WEBP, ICO, SVG'], 400);
        }
        if ($file['size'] > 500 * 1024) {
            send_json(['success' => false, 'message' => 'Favicon file size exceeds 500KB limit'], 400);
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'favicon_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            send_json(['success' => false, 'message' => 'Failed to save favicon file'], 500);
        }
        $relativePath = 'uploads/' . $filename;
        try {
            $checkColumn = $db->query("SHOW COLUMNS FROM site_settings LIKE 'favicon'")->fetch();
            if (!$checkColumn) {
                $db->exec('ALTER TABLE site_settings ADD COLUMN favicon VARCHAR(255) DEFAULT NULL');
            }
            $updateStmt = $db->prepare('UPDATE site_settings SET favicon = :favicon WHERE id = 1');
            $updateStmt->execute([':favicon' => $relativePath]);
        } catch (Exception $e) {
            error_log('Error updating favicon: ' . $e->getMessage());
            send_json(['success' => false, 'message' => 'Failed to update favicon'], 500);
        }
        send_json(['success' => true, 'message' => 'Favicon uploaded successfully', 'path' => $relativePath, 'type' => 'favicon']);
    }

    send_json(['success' => false, 'message' => 'No file uploaded'], 400);
}

function is_allowed_branding_upload(array $file, array $allowedExtensions): bool {
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return false;
    }

    $mimeMap = [
        'png' => ['image/png'],
        'jpg' => ['image/jpeg', 'image/jpg'],
        'jpeg' => ['image/jpeg', 'image/jpg'],
        'webp' => ['image/webp'],
        'svg' => ['image/svg+xml'],
        'ico' => ['image/x-icon', 'image/vnd.microsoft.icon'],
    ];

    if (!isset($mimeMap[$extension])) {
        return false;
    }

    if (!function_exists('finfo_open')) {
        return true;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return true;
    }

    $detectedMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($detectedMime === false) {
        return false;
    }

    return in_array($detectedMime, $mimeMap[$extension], true);
}
