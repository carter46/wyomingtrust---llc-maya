<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'User Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderUsersContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">User Management</h1>
    <button onclick="showCreateUserModal()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto">Create New User</button>
</div>
<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
<div id="usersContainer" class="p-4 sm:p-6">
<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading users...</div>
</div>
</div>

<script src="includes/modal.js"></script>
<script>
async function loadUsers() {
    try {
        const response = await fetch('../../api/admin/users.php');
        const data = await response.json();
        
        if (data.success && data.users) {
            allUsers = data.users;
            renderUsers(data.users);
        } else {
            document.getElementById('usersContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load users</div>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('usersContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading users</div>';
    }
}

function renderUsers(users) {
    const container = document.getElementById('usersContainer');
    if (!users || users.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No users found</div>';
        return;
    }
    
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Verified</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Trusts</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Created</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${users.map(user => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${escapeHtml(user.full_name)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${escapeHtml(user.email)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 rounded text-xs ${user.email_verified ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'}">
                                    ${user.email_verified ? 'Verified' : 'Unverified'}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${user.trusts_count || 0}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500">${new Date(user.created_at).toLocaleDateString()}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="editUser(${user.id})" class="text-primary hover:underline text-xs sm:text-sm">Edit</button>
                                    <button onclick="resetPassword(${user.id})" class="text-blue-600 hover:underline text-xs sm:text-sm">Reset</button>
                                    <button onclick="deleteUser(${user.id})" class="text-red-600 hover:underline text-xs sm:text-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${users.map(user => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white truncate">${escapeHtml(user.full_name)}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1">${escapeHtml(user.email)}</p>
                        </div>
                        <span class="px-2 py-1 rounded text-xs flex-shrink-0 ml-2 ${user.email_verified ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'}">
                            ${user.email_verified ? 'Verified' : 'Unverified'}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-3">
                        <span>Trusts: <strong>${user.trusts_count || 0}</strong></span>
                        <span>${new Date(user.created_at).toLocaleDateString()}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-200 dark:border-slate-600">
                        <button onclick="editUser(${user.id})" class="text-primary hover:underline text-xs">Edit</button>
                        <button onclick="resetPassword(${user.id})" class="text-blue-600 hover:underline text-xs">Reset Password</button>
                        <button onclick="deleteUser(${user.id})" class="text-red-600 hover:underline text-xs">Delete</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function showCreateUserModal() {
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Full Name *</label>
                <input type="text" name="full_name" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Email *</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Password *</label>
                <div class="relative">
                    <input type="password" name="password" id="createUserPassword" required minlength="8"
                           class="w-full px-4 py-2 pr-12 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <button type="button" onclick="togglePasswordVisibility('createUserPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                        <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Minimum 8 characters</p>
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="email_verified" 
                           class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <span class="text-sm font-semibold text-navy-900 dark:text-white">Email Verified</span>
                </label>
            </div>
        </div>
    `;
    
    showFormModal('Create New User', formHtml, function(data) {
        const name = (data.full_name || '').trim();
        const email = (data.email || '').trim();
        const password = data.password || '';
        const emailVerified = data.email_verified === true || data.email_verified === 'on' ? 1 : 0;
        
        if (!name || !email || !password) {
            showToast('All fields are required', 'warning');
            return;
        }
        
        if (password.length < 8) {
            showToast('Password must be at least 8 characters', 'warning');
            return;
        }
        
        createUser(name, email, password, emailVerified);
    });
}

async function createUser(name, email, password, emailVerified) {
    
    createUser(name, email, password);
}

async function createUser(name, email, password) {
    try {
        const response = await fetch('../../api/admin/users.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ full_name: name, email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('User created successfully', 'success');
            loadUsers();
        } else {
            showToast(data.message || 'Failed to create user', 'error');
        }
    } catch (error) {
        console.error('Error creating user:', error);
        showToast('Error creating user', 'error');
    }
}

function editUser(userId) {
    const user = allUsers.find(u => u.id == userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Full Name *</label>
                <input type="text" name="full_name" value="${escapeHtml(user.full_name)}" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Email *</label>
                <input type="email" name="email" value="${escapeHtml(user.email)}" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="email_verified" ${user.email_verified ? 'checked' : ''} 
                           class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <span class="text-sm font-semibold text-navy-900 dark:text-white">Email Verified</span>
                </label>
            </div>
        </div>
    `;
    
    showFormModal('Edit User', formHtml, function(data) {
        const name = (data.full_name || '').trim();
        const email = (data.email || '').trim();
        const emailVerified = data.email_verified === true || data.email_verified === 'on' ? 1 : 0;
        
        if (!name || !email) {
            showToast('Name and email are required', 'warning');
            return;
        }
        
        updateUser(userId, name, email, emailVerified);
    });
}

async function updateUser(userId, name, email, emailVerified) {
    const updates = {
        id: userId,
        full_name: name,
        email: email,
        email_verified: emailVerified
    };
    
    try {
        const response = await fetch('../../api/admin/users.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(updates)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('User updated successfully', 'success');
            loadUsers();
        } else {
            showToast(data.message || 'Failed to update user', 'error');
        }
    } catch (error) {
        console.error('Error updating user:', error);
        showToast('Error updating user', 'error');
    }
}

let allUsers = [];

function resetPassword(userId) {
    const user = allUsers.find(u => u.id == userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">New Password *</label>
                <div class="relative">
                    <input type="password" name="password" id="resetUserPassword" required minlength="8"
                           class="w-full px-4 py-2 pr-12 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <button type="button" onclick="togglePasswordVisibility('resetUserPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                        <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Minimum 8 characters</p>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Resetting password for: <strong>${escapeHtml(user.email)}</strong>
            </p>
        </div>
    `;
    
    showFormModal('Reset Password', formHtml, function(data) {
        const newPassword = data.password || '';
        if (!newPassword || newPassword.length < 8) {
            showToast('Password must be at least 8 characters', 'warning');
            return;
        }
        doResetPassword(userId, newPassword);
    });
}

async function doResetPassword(userId, newPassword) {
    
    try {
        const response = await fetch('../../api/admin/users.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: userId, password: newPassword })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Password reset successfully', 'success');
        } else {
            showToast(data.message || 'Failed to reset password', 'error');
        }
    } catch (error) {
        console.error('Error resetting password:', error);
        showToast('Error resetting password', 'error');
    }
}

function deleteUser(userId) {
    const user = allUsers.find(u => u.id == userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    showConfirmModal(
        'Delete User',
        `Are you sure you want to delete user "${escapeHtml(user.full_name)}" (${escapeHtml(user.email)})? This action cannot be undone.`,
        async function() {
    
    try {
        const response = await fetch(`../../api/admin/users.php?id=${userId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
            if (data.success) {
                showToast('User deleted successfully', 'success');
                loadUsers();
            } else {
                showToast(data.message || 'Failed to delete user', 'error');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            showToast('Error deleting user', 'error');
        }
        }
    );
}

function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load users on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadUsers);
} else {
    loadUsers();
}
</script>

<?php
}

// Render the layout with users content
renderAdminLayout($page_title, 'users', 'renderUsersContent');
?>
