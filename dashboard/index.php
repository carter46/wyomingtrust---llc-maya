<?php
/**
 * /dashboard/ entry — route guests to login; admins/users to their dashboards.
 */
require_once __DIR__ . '/../api/helpers.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/index.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit;
}

header('Location: ../login.php');
exit;
