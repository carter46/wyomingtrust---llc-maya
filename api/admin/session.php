<?php

require_once __DIR__ . '/../helpers.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    if (!isset($_SESSION['admin_id'])) {
        send_json(['success' => false, 'authenticated' => false], 401);
    }
    
    send_json([
        'success' => true,
        'authenticated' => true,
        'csrf_token' => generate_csrf_token(),
        'admin' => [
            'id' => (int) $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'] ?? null,
        ],
    ]);
}

send_json(['success' => false, 'message' => 'Method not allowed'], 405);
