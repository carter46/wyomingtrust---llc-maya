<?php
/**
 * Admin Password Reset Script
 * 
 * This script allows direct password reset for admin accounts.
 * Access via URL: /dashboard/admin/reset-admin-password.php
 * 
 * WARNING: This is a one-time use script. After resetting the password, 
 * you should restrict access to this file or delete it for security.
 */

require_once __DIR__ . '/../../api/config.php';
require_once __DIR__ . '/../../api/helpers.php';

$message = '';
$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $adminEmail = sanitize_text($_POST['admin_email'] ?? '');
    
    if (empty($adminEmail)) {
        $error = 'Admin email is required';
    } elseif (!validate_email($adminEmail)) {
        $error = 'Invalid email address';
    } elseif (empty($newPassword)) {
        $error = 'Password is required';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = getDatabase();
            
            // Check if admin exists
            $stmt = $db->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $adminEmail]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                $error = 'Admin account not found';
            } else {
                // Update password
                $update = $db->prepare('UPDATE admins SET password = :password WHERE email = :email');
                $update->execute([
                    ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
                    ':email' => $adminEmail,
                ]);
                
                $success = true;
                $message = 'Admin password has been reset successfully. You can now log in with the new password.';
            }
        } catch (Exception $e) {
            error_log('Admin password reset failed: ' . $e->getMessage());
            $error = 'Failed to reset password. Please try again.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - WyomingTrust</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="bg-orange-500 p-3 rounded-lg">
                    <span class="material-icons-outlined text-white text-3xl">lock</span>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset Admin Password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter new password for admin account
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo escape_html($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo escape_html($message); ?></span>
            </div>
            <div class="text-center">
                <a href="login.php" class="font-medium text-orange-600 hover:text-orange-500">
                    Go to Login Page
                </a>
            </div>
        <?php else: ?>
            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="admin_email" class="sr-only">Admin Email</label>
                        <input id="admin_email" name="admin_email" type="email" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                               placeholder="Admin Email" value="<?php echo escape_html($_POST['admin_email'] ?? 'admin@wyomingtrust.com'); ?>">
                    </div>
                    <div>
                        <label for="password" class="sr-only">New Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                                   placeholder="New Password (min 8 characters)">
                            <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle password visibility">
                                <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="confirm_password" class="sr-only">Confirm Password</label>
                        <div class="relative">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                                   placeholder="Confirm Password">
                            <button type="button" onclick="togglePasswordVisibility('confirm_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle password visibility">
                                <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Reset Password
                    </button>
                </div>
                
                <div class="text-sm text-gray-600 text-center">
                    <p class="text-xs text-gray-500">
                        ⚠️ Security Note: This script should be restricted or deleted after use.
                    </p>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        // Password visibility toggle function
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('.toggle-password-icon');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility';
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility_off';
                }
            }
        }
    </script>
</body>
</html>
