<?php
// Shared Admin Layout Component
// Usage: Include this file and call renderAdminLayout($page_title, $active_page, $content)
// $active_page should match the sidebar navigation key (e.g., 'dashboard', 'users', 'settings')

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

function renderAdminLayout($page_title, $active_page = 'dashboard', $content_callback = null) {
    $admin_email = $_SESSION['admin_email'] ?? 'Admin';
    
    // Navigation items (main menu — account settings pinned separately at bottom)
    $nav_items = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => 'index.php'],
        'users' => ['label' => 'User Management', 'icon' => 'people', 'href' => 'users.php'],
        'trusts' => ['label' => 'Trust Services', 'icon' => 'account_balance', 'href' => 'trusts.php'],
        'trust-payments' => ['label' => 'Payment Approvals', 'icon' => 'pending_actions', 'href' => 'trust-payments.php'],
        'coins' => ['label' => 'Coins Management', 'icon' => 'monetization_on', 'href' => 'coins.php'],
        'addresses' => ['label' => 'Wallet Addresses', 'icon' => 'account_balance_wallet', 'href' => 'addresses.php'],
        'user-assets' => ['label' => 'User Assets', 'icon' => 'savings', 'href' => 'user-assets.php'],
        'payments' => ['label' => 'Payment Methods', 'icon' => 'payment', 'href' => 'payments.php'],
        'email-settings' => ['label' => 'Email Settings', 'icon' => 'mail', 'href' => 'email-settings.php'],
        'settings' => ['label' => 'Site Settings', 'icon' => 'settings', 'href' => 'settings.php'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($page_title ?? 'Admin Dashboard'); ?> - WyomingTrust</title>
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
                    "navy-800": "#1E293B",
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
<style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Lexend', sans-serif; }
    .sidebar-transition {
        transition: transform 0.3s ease-in-out;
    }
</style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen">
    <!-- Mobile Sidebar Overlay -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-navy-900 border-r border-slate-200 dark:border-slate-800 z-50 sidebar-transition transform -translate-x-full lg:translate-x-0 flex flex-col">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="bg-primary p-1.5 rounded-lg">
                    <span class="material-icons-outlined text-navy-900 font-bold text-lg">admin_panel_settings</span>
                </div>
                <span class="text-lg font-bold tracking-tight text-navy-900 dark:text-white">Admin</span>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 min-h-0 overflow-y-auto py-4 px-3">
            <ul class="space-y-1">
                <?php foreach ($nav_items as $key => $item): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($item['href']); ?>" 
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors <?php echo $active_page === $key ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-navy-800 hover:text-slate-900 dark:hover:text-white'; ?>">
                        <span class="material-icons-outlined text-xl"><?php echo htmlspecialchars($item['icon']); ?></span>
                        <span><?php echo htmlspecialchars($item['label']); ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Account + Logout (always visible at bottom) -->
        <div class="shrink-0 px-3 pb-3 border-t border-slate-200 dark:border-slate-800 pt-4 space-y-1">
            <a href="profile.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors <?php echo $active_page === 'profile' ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-navy-800 hover:text-slate-900 dark:hover:text-white'; ?>">
                <span class="material-icons-outlined text-xl">manage_accounts</span>
                <span>Account Settings</span>
            </a>
            <p class="px-4 text-[10px] text-slate-500 dark:text-slate-500 leading-snug">Change admin email &amp; password</p>
            <a href="../../api/admin/logout.php" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <span class="material-icons-outlined text-xl">logout</span>
                <span>Logout</span>
            </a>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 px-2 py-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="material-icons-outlined text-sm">email</span>
                <span class="truncate"><?php echo htmlspecialchars($admin_email); ?></span>
            </div>
        </div>
    </aside>
    
    <!-- Main Content Area -->
    <div class="lg:pl-64">
        <!-- Top Header -->
        <header class="bg-white dark:bg-navy-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <!-- Mobile Menu Button -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-navy-800">
                    <span class="material-icons-outlined">menu</span>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-navy-900 dark:text-white"><?php echo htmlspecialchars($page_title ?? 'Admin Dashboard'); ?></h1>
                </div>
                
                <!-- Header Actions -->
                <div class="flex items-center gap-4">
                    <a href="../../index.php" target="_blank" rel="noopener noreferrer" class="hidden sm:flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                        <span class="material-icons-outlined text-lg">open_in_new</span>
                        <span>View Site</span>
                    </a>
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-navy-800 transition-colors">
                        <span id="darkModeIcon" class="material-icons-outlined">dark_mode</span>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <?php 
            if (is_callable($content_callback)) {
                call_user_func($content_callback);
            } elseif (is_string($content_callback) && function_exists($content_callback)) {
                call_user_func($content_callback);
            } else {
                // Content will be output by including page
            }
            ?>
        </main>
    </div>
    
    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileSidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        
        function toggleDarkMode() {
            const html = document.documentElement;
            const icon = document.getElementById('darkModeIcon');
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
                icon.textContent = 'dark_mode';
            } else {
                html.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
                icon.textContent = 'light_mode';
            }
        }
        
        // Initialize dark mode from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            const html = document.documentElement;
            const icon = document.getElementById('darkModeIcon');
            
            if (darkMode === 'true') {
                html.classList.add('dark');
                if (icon) icon.textContent = 'light_mode';
            } else {
                html.classList.remove('dark');
                if (icon) icon.textContent = 'dark_mode';
            }
        });
    </script>
<?php
}
?>
