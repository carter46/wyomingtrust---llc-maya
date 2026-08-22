<?php
/**
 * Admin Password Hash Generator
 * 
 * Run this script to generate a proper password hash for the admin account.
 * Usage: php generate-admin-password.php
 * 
 * Then update the database:
 * UPDATE admins SET password = '<generated_hash>' WHERE email = 'admin@wyomingtrust.com';
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: {$password}\n";
echo "Hash: {$hash}\n\n";
echo "SQL UPDATE statement:\n";
echo "UPDATE admins SET password = '{$hash}' WHERE email = 'admin@wyomingtrust.com';\n\n";
echo "Or use the password reset script: dashboard/admin/reset-admin-password.php\n";
