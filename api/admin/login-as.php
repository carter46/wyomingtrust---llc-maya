<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$adminId = require_admin_auth();

if (!empty($_SESSION['admin_impersonating'])) {
    $redirect = '../../dashboard/user/dashboard.php';
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        send_json(['success' => false, 'message' => 'Already impersonating a user', 'redirect' => $redirect], 400);
    }
    header('Location: ' . $redirect);
    exit;
}

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($userId <= 0 && strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    if ($userId <= 0) {
        $payload = get_json_input();
        $userId = (int) ($payload['user_id'] ?? 0);
    }
}

if ($userId <= 0) {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        header('Location: ../../dashboard/admin/users.php?error=missing_user');
        exit;
    }
    send_json(['success' => false, 'message' => 'User ID is required'], 400);
}

$db = getDatabase();
$stmt = $db->prepare('SELECT id, full_name, email FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    send_json(['success' => false, 'message' => 'User not found'], 404);
}

$_SESSION['admin_impersonating'] = true;
$_SESSION['admin_original_id'] = $adminId;
$_SESSION['admin_original_email'] = $_SESSION['admin_email'] ?? '';
$_SESSION['admin_original_name'] = $_SESSION['admin_email'] ?? 'Admin';
// Keep admin_id so admin APIs/layout still recognize the admin session while impersonating

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['full_name'] ?? 'User';
$_SESSION['user_email'] = $user['email'] ?? '';

$redirect = '../../dashboard/user/dashboard.php';

// Browser navigation (Login As link) should always redirect; JSON only for explicit API clients
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$wantsJson = (strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false)
    || (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && empty($_GET['redirect']) && strpos($accept, 'text/html') === false);

if ($wantsJson) {
    send_json([
        'success' => true,
        'message' => 'Now logged in as ' . ($user['full_name'] ?? 'user'),
        'redirect' => $redirect,
    ]);
}

header('Location: ' . $redirect);
exit;
