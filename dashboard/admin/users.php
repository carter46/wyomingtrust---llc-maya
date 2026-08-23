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
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
<div id="usersContainer" class="p-4 sm:p-6">
<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading users...</div>
</div>
</div>

<script src="includes/modal.js"></script>
<script src="includes/trust-detail-render.js"></script>
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
        <div class="hidden md:block overflow-x-auto overflow-y-visible">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">LLC Status</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">LLCs</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Created</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${users.map(user => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-medium text-navy-900 dark:text-white">${escapeHtml(user.full_name)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(user.email)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">${llcStatusBadge(user.llc_status)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${user.trusts_count || 0}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500">${new Date(user.created_at).toLocaleDateString()}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-right overflow-visible">${renderUserActionsMenu(user)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${users.map(user => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white truncate">${escapeHtml(user.full_name)}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1">${escapeHtml(user.email)}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${llcStatusBadge(user.llc_status)}
                            ${renderUserActionsMenu(user)}
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>LLCs: <strong>${user.trusts_count || 0}</strong></span>
                        <span>${new Date(user.created_at).toLocaleDateString()}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function llcStatusBadge(status) {
    const key = String(status || 'none').toLowerCase();
    const map = {
        pending: { label: 'Pending', cls: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' },
        approved: { label: 'Approved', cls: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
        rejected: { label: 'Rejected', cls: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
        none: { label: 'None', cls: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' },
    };
    const item = map[key] || map.none;
    return `<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold ${item.cls}">${item.label}</span>`;
}

function renderUserActionsMenu(user) {
    const id = Number(user.id);
    const hasTrusts = (user.trusts_count || 0) > 0;
    return `
        <div class="relative inline-block text-left user-actions-menu" data-user-id="${id}">
            <button type="button"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-navy-600 transition-colors"
                onclick="toggleUserActionsMenu(event, ${id})"
                aria-label="User actions"
                aria-haspopup="true">
                <span class="material-icons-outlined text-xl leading-none">more_vert</span>
            </button>
            <div class="user-actions-dropdown hidden absolute right-0 z-50 mt-1 w-44 origin-top-right rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-navy-800 shadow-lg py-1">
                ${hasTrusts ? `<button type="button" class="w-full text-left px-4 py-2.5 text-sm text-emerald-700 dark:text-emerald-400 hover:bg-slate-50 dark:hover:bg-navy-700" onclick="event.stopPropagation(); closeAllUserActionsMenus(); viewUserTrusts(${id})">View LLC</button>` : ''}
                <button type="button" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-navy-700" onclick="event.stopPropagation(); closeAllUserActionsMenus(); editUser(${id})">Edit</button>
                <button type="button" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-navy-700" onclick="event.stopPropagation(); closeAllUserActionsMenus(); resetPassword(${id})">Reset Password</button>
                <button type="button" class="w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20" onclick="event.stopPropagation(); closeAllUserActionsMenus(); deleteUser(${id})">Delete</button>
            </div>
        </div>
    `;
}

function closeAllUserActionsMenus() {
    document.querySelectorAll('.user-actions-dropdown').forEach((el) => el.classList.add('hidden'));
}

function toggleUserActionsMenu(event, userId) {
    event.stopPropagation();
    const menu = document.querySelector(`.user-actions-menu[data-user-id="${userId}"] .user-actions-dropdown`);
    if (!menu) return;
    const wasOpen = !menu.classList.contains('hidden');
    closeAllUserActionsMenus();
    if (!wasOpen) menu.classList.remove('hidden');
}

document.addEventListener('click', function () {
    closeAllUserActionsMenus();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllUserActionsMenus();
});


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

let currentTrustViewUserId = null;
let currentUserTrustsCache = [];

function setAdminModalWidth(wide) {
    const dialog = document.querySelector('#modalContainer .modal-dialog');
    if (!dialog) return;
    dialog.classList.toggle('max-w-md', !wide);
    dialog.classList.toggle('max-w-3xl', false);
    dialog.classList.toggle('max-w-4xl', !!wide);
}

async function viewUserTrusts(userId) {
    try {
        const response = await fetch(`../../api/admin/user-trusts.php?user_id=${userId}`);
        const data = await response.json();
        if (!data.success) {
            showToast(data.message || 'Failed to load LLCs', 'error');
            return;
        }
        const trusts = Array.isArray(data.trusts) ? data.trusts : [];
        if (!trusts.length) {
            showToast('This user has no LLCs', 'warning');
            return;
        }
        currentTrustViewUserId = userId;
        currentUserTrustsCache = trusts;
        if (trusts.length === 1) {
            await openAdminTrustDetailModal(trusts[0].id, userId);
            return;
        }
        showTrustPickerModal(data.user || { id: userId }, trusts);
    } catch (error) {
        console.error('Error loading user trusts:', error);
        showToast('Error loading user LLCs', 'error');
    }
}

function showTrustPickerModal(user, trusts) {
    const html = TrustDetailRender.renderTrustPickerHtml(user, trusts);
    setAdminModalWidth(true);
    showModal(`LLCs — ${user.full_name || user.email || 'User'}`, html, [
        { label: 'Close', onclick: () => closeModal(), class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white min-w-[6.5rem]' }
    ]);
}

async function openAdminTrustDetailModal(trustId, userId) {
    try {
        const response = await fetch(`../../api/admin/user-trusts.php?id=${trustId}`);
        const data = await response.json();
        if (!data.success || !data.trust) {
            showToast(data.message || 'Failed to load LLC details', 'error');
            return;
        }
        showAdminTrustDetailModal(data.trust, userId);
    } catch (error) {
        console.error('Error loading trust detail:', error);
        showToast('Error loading LLC details', 'error');
    }
}

function showAdminTrustDetailModal(trust, userId) {
    const html = TrustDetailRender.renderTrustDetailHtml(trust, { showUserInfo: true, showPaymentDetails: true, showValueSplit: true });
    const actions = [];

    if (currentUserTrustsCache.length > 1) {
        actions.push({
            label: '← Back to LLCs',
            onclick: () => backToTrustPicker(),
            class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white',
        });
    }

    if (trust.can_approve_registration) {
        actions.push({
            label: 'Approve LLC',
            onclick: () => approveTrustRegistration(trust.id),
            class: 'bg-green-600 text-white min-w-[8rem]',
            icon: 'check_circle',
        });
        actions.push({
            label: 'Disapprove LLC',
            onclick: () => disapproveTrustRegistration(trust.id),
            class: 'bg-red-600 text-white min-w-[8rem]',
            icon: 'cancel',
        });
    }

    actions.push({
        label: 'Close',
        onclick: () => closeModal(),
        class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white min-w-[6.5rem]',
    });

    setAdminModalWidth(true);
    showModal(`LLC Details — #${trust.id}`, html, actions);
}

function backToTrustPicker() {
    const user = allUsers.find(u => u.id == currentTrustViewUserId) || {
        id: currentTrustViewUserId,
        full_name: '',
        email: '',
    };
    showTrustPickerModal(user, currentUserTrustsCache);
}

async function getAdminCsrfToken() {
    const res = await fetch('../../api/admin/session.php');
    const data = await res.json();
    return data.csrf_token || null;
}

async function approveTrustRegistration(trustId) {
    showConfirmModal(
        'Approve LLC',
        'Approve this free LLC registration? The LLC will become active.',
        async function() {
            try {
                const csrfToken = await getAdminCsrfToken();
                const response = await fetch('../../api/admin/user-trusts.php', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        trust_id: trustId,
                        action: 'approve_registration',
                        csrf_token: csrfToken,
                    }),
                });
                const data = await response.json();
                if (data.success && data.trust) {
                    showToast(data.message || 'LLC approved', 'success');
                    const idx = currentUserTrustsCache.findIndex(t => t.id == trustId);
                    if (idx >= 0) currentUserTrustsCache[idx] = data.trust;
                    showAdminTrustDetailModal(data.trust, currentTrustViewUserId || data.trust.user_id);
                    loadUsers();
                } else {
                    showToast(data.message || 'Failed to approve LLC', 'error');
                }
            } catch (error) {
                console.error('Approve LLC error:', error);
                showToast('Error approving LLC', 'error');
            }
        }
    );
}

async function disapproveTrustRegistration(trustId) {
    showConfirmModal(
        'Disapprove LLC',
        'Disapprove this free LLC registration? The LLC will be set to inactive.',
        async function() {
            try {
                const csrfToken = await getAdminCsrfToken();
                const response = await fetch('../../api/admin/user-trusts.php', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        trust_id: trustId,
                        action: 'reject_registration',
                        csrf_token: csrfToken,
                    }),
                });
                const data = await response.json();
                if (data.success && data.trust) {
                    showToast(data.message || 'LLC disapproved', 'success');
                    const idx = currentUserTrustsCache.findIndex(t => t.id == trustId);
                    if (idx >= 0) currentUserTrustsCache[idx] = data.trust;
                    showAdminTrustDetailModal(data.trust, currentTrustViewUserId || data.trust.user_id);
                    loadUsers();
                } else {
                    showToast(data.message || 'Failed to disapprove LLC', 'error');
                }
            } catch (error) {
                console.error('Disapprove LLC error:', error);
                showToast('Error disapproving LLC', 'error');
            }
        }
    );
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
