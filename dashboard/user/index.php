<?php
/**
 * /dashboard/user/ entry — require login then go to user dashboard.
 */
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

header('Location: dashboard.php');
exit;
