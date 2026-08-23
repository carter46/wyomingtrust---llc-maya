<?php
/**
 * Root 404 handler — special-case /dashboard/* only; all other URLs get a normal 404 page.
 */
require_once __DIR__ . '/api/helpers.php';

$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = parse_url($uri, PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$path = '/' . ltrim($path, '/');

$isDashboardUser = (bool) preg_match('#^/dashboard/user(/|$)#i', $path);
$isDashboardAdmin = (bool) preg_match('#^/dashboard/admin(/|$)#i', $path);
$isDashboardRoot = (bool) preg_match('#^/dashboard(/|$)#i', $path);

if ($isDashboardUser) {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard/user/dashboard.php');
        exit;
    }
    header('Location: /login.php');
    exit;
}

if ($isDashboardAdmin) {
    if (isset($_SESSION['admin_id'])) {
        header('Location: /dashboard/admin/index.php');
        exit;
    }
    header('Location: /dashboard/admin/login.php');
    exit;
}

if ($isDashboardRoot) {
    if (isset($_SESSION['admin_id'])) {
        header('Location: /dashboard/admin/index.php');
        exit;
    }
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard/user/dashboard.php');
        exit;
    }
    header('Location: /login.php');
    exit;
}

http_response_code(404);
$loggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
$homeHref = isset($_SESSION['admin_id'])
    ? '/dashboard/admin/index.php'
    : (isset($_SESSION['user_id']) ? '/dashboard/user/dashboard.php' : '/');
$loginHref = isset($_SESSION['admin_id']) ? '/dashboard/admin/login.php' : '/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Page not found | WyomingTrust</title>
<style>
body { font-family: system-ui, sans-serif; background: #f8f9fa; color: #191c1d; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
.card { max-width: 28rem; text-align: center; background: #fff; border: 1px solid #c4c6cd; border-radius: 12px; padding: 2rem; }
h1 { font-size: 1.5rem; margin: 0 0 0.5rem; }
p { color: #44474c; margin: 0 0 1.5rem; }
a { color: #115cb9; margin: 0 0.5rem; }
</style>
</head>
<body>
<div class="card">
<h1>Page not found</h1>
<p>The page you requested does not exist.</p>
<p>
<a href="<?php echo htmlspecialchars($homeHref, ENT_QUOTES, 'UTF-8'); ?>">Home</a>
<?php if (!$loggedIn): ?>
<a href="<?php echo htmlspecialchars($loginHref, ENT_QUOTES, 'UTF-8'); ?>">Log in</a>
<?php endif; ?>
</p>
</div>
</body>
</html>
