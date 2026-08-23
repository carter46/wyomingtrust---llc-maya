<?php
/**
 * Scoped not-found for /dashboard/user/* invalid paths.
 * Guests → login; logged-in users → user dashboard.
 */
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

header('Location: dashboard.php');
exit;
