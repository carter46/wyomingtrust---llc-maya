<?php

require_once __DIR__ . '/../helpers.php';

if (empty($_SESSION['admin_impersonating'])) {
    header('Location: ../../dashboard/admin/users.php');
    exit;
}

restore_admin_from_impersonation();

header('Location: ../../dashboard/admin/users.php');
exit;
