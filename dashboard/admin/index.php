<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Dashboard Overview';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

// Start output buffering for content  
function renderDashboardContent() {
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <!-- Total Users -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total Users</p>
                <p class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mt-2" id="statsTotalUsers">-</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1"><span id="statsVerifiedUsers">-</span> verified</p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 p-3 sm:p-4 rounded-xl">
                <span class="material-icons-outlined text-blue-600 dark:text-blue-400 text-2xl sm:text-3xl">people</span>
            </div>
        </div>
    </div>
    
    <!-- Total Trusts -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total Trusts</p>
                <p class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mt-2" id="statsTotalTrusts">-</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Created</p>
            </div>
            <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 sm:p-4 rounded-xl">
                <span class="material-icons-outlined text-emerald-600 dark:text-emerald-400 text-2xl sm:text-3xl">account_balance</span>
            </div>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wide">Total Revenue</p>
                <p class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mt-2" id="statsTotalRevenue">$0.00</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Completed transactions</p>
            </div>
            <div class="bg-amber-100 dark:bg-amber-900/30 p-3 sm:p-4 rounded-xl">
                <span class="material-icons-outlined text-amber-600 dark:text-amber-400 text-2xl sm:text-3xl">attach_money</span>
            </div>
        </div>
    </div>
    
    <!-- Active Services -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wide">Active Services</p>
                <p class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mt-2" id="statsActiveServices">-</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Trust services</p>
            </div>
            <div class="bg-primary/10 dark:bg-primary/20 p-3 sm:p-4 rounded-xl">
                <span class="material-icons-outlined text-primary text-2xl sm:text-3xl">settings</span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Cards -->
<div class="mb-6 sm:mb-8">
    <h2 class="text-xl sm:text-2xl font-bold text-navy-900 dark:text-white mb-4 sm:mb-6">Quick Actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <a href="users.php" class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 hover:shadow-md transition-shadow group">
            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 sm:p-3 rounded-lg w-fit mb-3 sm:mb-4 group-hover:scale-110 transition-transform">
                <span class="material-icons-outlined text-blue-600 dark:text-blue-400 text-xl sm:text-2xl">people</span>
            </div>
            <h3 class="font-bold text-base sm:text-lg text-navy-900 dark:text-white mb-1 sm:mb-2">User Management</h3>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Manage user accounts, edit profiles, and reset passwords</p>
        </a>

        <a href="trusts.php" class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 hover:shadow-md transition-shadow group">
            <div class="bg-emerald-100 dark:bg-emerald-900/30 p-2 sm:p-3 rounded-lg w-fit mb-3 sm:mb-4 group-hover:scale-110 transition-transform">
                <span class="material-icons-outlined text-emerald-600 dark:text-emerald-400 text-xl sm:text-2xl">account_balance</span>
            </div>
            <h3 class="font-bold text-base sm:text-lg text-navy-900 dark:text-white mb-1 sm:mb-2">Trust Services</h3>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Add, edit, or remove trust service offerings</p>
        </a>

        <a href="payments.php" class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 hover:shadow-md transition-shadow group">
            <div class="bg-primary/10 dark:bg-primary/20 p-2 sm:p-3 rounded-lg w-fit mb-3 sm:mb-4 group-hover:scale-110 transition-transform">
                <span class="material-icons-outlined text-primary text-xl sm:text-2xl">payment</span>
            </div>
            <h3 class="font-bold text-base sm:text-lg text-navy-900 dark:text-white mb-1 sm:mb-2">Payment Methods</h3>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Configure payment gateways and methods</p>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Recent Users -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-bold text-navy-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-icons-outlined text-primary">person_add</span>
            <span>Recent Registrations</span>
        </h2>
        <div id="recentUsers" class="space-y-3">
            <div class="text-center py-8 text-slate-500 text-sm">Loading...</div>
        </div>
    </div>
    
    <!-- Recent Trusts -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-bold text-navy-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-icons-outlined text-primary">account_balance</span>
            <span>Recent Trusts</span>
        </h2>
        <div id="recentTrusts" class="space-y-3">
            <div class="text-center py-8 text-slate-500 text-sm">Loading...</div>
        </div>
    </div>
</div>

<script src="includes/modal.js"></script>
<script>
    // Load dashboard statistics
    async function loadStats() {
        try {
            const response = await fetch('../../api/admin/stats.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });

            const data = await response.json();
            
            if (data.success && data.stats) {
                const stats = data.stats;
                
                // Update statistics cards
                document.getElementById('statsTotalUsers').textContent = stats.total_users || 0;
                document.getElementById('statsVerifiedUsers').textContent = `${stats.verified_users || 0} verified`;
                document.getElementById('statsTotalTrusts').textContent = stats.total_trusts || 0;
                document.getElementById('statsTotalRevenue').textContent = '$' + (stats.total_revenue || 0).toFixed(2);
                document.getElementById('statsActiveServices').textContent = stats.active_services || 0;
                
                // Render recent users
                renderRecentUsers(stats.recent_users || []);
                
                // Render recent trusts
                renderRecentTrusts(stats.recent_trusts || []);
            }
        } catch (error) {
            console.error('Failed to load statistics:', error);
            showToast('Failed to load dashboard statistics', 'error');
        }
    }
    
    function renderRecentUsers(users) {
        const container = document.getElementById('recentUsers');
        
        if (!users || users.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-slate-500 text-sm">No recent registrations</div>';
            return;
        }
        
        container.innerHTML = users.map(user => `
            <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-navy-900 dark:text-white truncate">${escapeHtml(user.full_name)}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(user.email)}</p>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 ml-4">
                    ${formatDate(user.created_at)}
                </div>
            </div>
        `).join('');
    }
    
    function renderRecentTrusts(trusts) {
        const container = document.getElementById('recentTrusts');
        
        if (!trusts || trusts.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-slate-500 text-sm">No recent trusts</div>';
            return;
        }
        
        container.innerHTML = trusts.map(trust => `
            <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-navy-900 dark:text-white truncate">${escapeHtml(trust.service_name)}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(trust.user_name)}</p>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 ml-4">
                    ${formatDate(trust.created_at)}
                </div>
            </div>
        `).join('');
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return 'Today';
        } else if (days === 1) {
            return 'Yesterday';
        } else if (days < 7) {
            return `${days} days ago`;
        } else {
            return date.toLocaleDateString();
        }
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Load statistics on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadStats);
    } else {
        loadStats();
    }
</script>
<?php
}

// Render the layout with dashboard content
renderAdminLayout($page_title, 'dashboard', 'renderDashboardContent');
?>
