<?php
require_once __DIR__ . '/../../api/helpers.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Admin Login - WyomingTrust';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($page_title); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#F59E0B",
                    "navy-900": "#0F172A",
                    "background-light": "#F8FAFC",
                    "background-dark": "#0F172A",
                },
                fontFamily: {
                    display: ["Lexend", "sans-serif"],
                    sans: ["Inter", "sans-serif"],
                },
            },
        },
    };
</script>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white dark:bg-navy-800 rounded-xl shadow-lg p-8 border border-slate-200 dark:border-slate-700">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <div class="bg-primary p-1.5 rounded-lg">
                        <span class="material-icons-outlined text-navy-900 font-bold">admin_panel_settings</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-navy-900 dark:text-white">Admin Login</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">WyomingTrust Administration Panel</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-400 text-red-700 dark:text-red-400 px-4 py-3 rounded mb-4"></div>

            <!-- Login Form -->
            <form id="adminLoginForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Email Address</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required 
                        class="appearance-none relative block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 placeholder-slate-500 text-slate-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-navy-900"
                        placeholder="admin@wyomingtrust.com"
                    >
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Password</label>
                    <div class="relative">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="appearance-none relative block w-full px-3 py-2 pr-12 border border-slate-300 dark:border-slate-600 placeholder-slate-500 text-slate-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-navy-900"
                            placeholder="Enter your password"
                        >
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                            <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-lg text-navy-900 bg-primary hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Sign In to Admin Panel
                    </button>
                </div>
            </form>

            <!-- Back to Site -->
            <div class="mt-6 text-center">
                <a href="../../index.php" class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary">
                    ← Back to Website
                </a>
            </div>
        </div>

        <!-- Reset Password Link -->
        <div class="text-center">
            <a href="reset-admin-password.php" class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary">
                Reset Admin Password
            </a>
        </div>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.classList.add('hidden');
            
            try {
                const response = await fetch('../../api/admin/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = data.message || 'Login failed. Please check your credentials.';
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.classList.remove('hidden');
            }
        });
        
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
