<?php

require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    // Check if user is logged in
    $response = [
        'success' => true,
        'authenticated' => false,
        'user' => null,
        'admin' => null,
        'csrf_token' => generate_csrf_token(), // Always provide CSRF token
    ];
    
    if (isset($_SESSION['user_id'])) {
        $response['authenticated'] = true;
        $response['user'] = [
            'id' => (int) $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
        ];
    }
    
    if (isset($_SESSION['admin_id'])) {
        $response['authenticated'] = true;
        $response['admin'] = [
            'id' => (int) $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'] ?? null,
        ];
    }
    
    send_json($response);
}

send_json(['success' => false, 'message' => 'Method not allowed'], 405);
