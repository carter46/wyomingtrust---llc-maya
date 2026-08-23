<?php
/**
 * Scoped not-found for /dashboard/admin/* invalid paths.
 * Guests → admin login; logged-in admins → admin dashboard.
 */
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

header('Location: index.php');
exit;
