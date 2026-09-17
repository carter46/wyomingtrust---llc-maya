<?php

require_once __DIR__ . '/helpers.php';

// If admin is currently viewing as a user, restore admin instead of destroying the session
if (!empty($_SESSION['admin_impersonating'])) {
    restore_admin_from_impersonation();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        send_json([
            'success' => true,
            'message' => 'Returned to admin session',
            'redirect' => '../dashboard/admin/users.php',
        ]);
    }
    header('Location: ../dashboard/admin/users.php');
    exit;
}

// Clear user session
unset($_SESSION['user_id']);
unset($_SESSION['user_email']);
unset($_SESSION['user_name']);

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// If POST request (API call), return JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    send_json(['success' => true, 'message' => 'Logged out successfully']);
} else {
    // If GET request (link click), redirect to login page
    header('Location: ../login.php');
    exit;
}
