<?php

require_once __DIR__ . '/helpers.php';

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
