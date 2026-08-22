<?php

require_once __DIR__ . '/../helpers.php';

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// If POST request (API call), return JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    send_json(['success' => true, 'message' => 'Logged out successfully']);
} else {
    // If GET request (link click), redirect to admin login page
    header('Location: ../../dashboard/admin/login.php');
    exit;
}
