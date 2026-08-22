<?php
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Account Settings';
require_once __DIR__ . '/includes/layout.php';

function renderProfileContent() {
?>

<div class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mb-2">Account Settings</h1>
    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">Update your admin login email and/or password</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <form id="accountForm" class="space-y-6">
            <div>
                <h2 class="text-lg font-bold text-navy-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-icons-outlined text-primary">email</span>
                    Email
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Current Email</label>
                        <input type="email" id="currentEmail" readonly
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">New Email Address</label>
                        <input type="email" name="new_email" id="new_email" placeholder="Leave blank to keep current email"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <h2 class="text-lg font-bold text-navy-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-icons-outlined text-primary">lock</span>
                    Password
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">New Password</label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new_password" minlength="8" placeholder="Leave blank to keep current password"
                                   class="w-full px-4 py-2 pr-12 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                            <button type="button" onclick="togglePasswordVisibility('new_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                                <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" minlength="8"
                                   class="w-full px-4 py-2 pr-12 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                            <button type="button" onclick="togglePasswordVisibility('confirm_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                                <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Current Password *</label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-4 py-2 pr-12 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none" aria-label="Toggle password visibility">
                        <span class="material-icons-outlined text-lg toggle-password-icon">visibility_off</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Required to confirm any account changes</p>
            </div>

            <div id="accountMessage"></div>

            <button type="submit" id="accountSaveBtn" class="w-full sm:w-auto bg-primary text-navy-900 px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition-opacity">
                Save Account Changes
            </button>
        </form>
    </div>
</div>

<script src="includes/modal.js"></script>
<script>
async function loadAdminData() {
    try {
        const response = await fetch('../../api/admin/profile.php');
        const data = await response.json();
        if (data.success && data.admin) {
            document.getElementById('currentEmail').value = data.admin.email || '';
        }
    } catch (error) {
        console.error('Error loading admin data:', error);
    }
}

document.getElementById('accountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const messageDiv = document.getElementById('accountMessage');
    const saveBtn = document.getElementById('accountSaveBtn');
    messageDiv.innerHTML = '';

    const currentPassword = document.getElementById('current_password').value;
    const newEmail = document.getElementById('new_email').value.trim();
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    const wantsEmailChange = newEmail !== '';
    const wantsPasswordChange = newPassword !== '' || confirmPassword !== '';

    if (!wantsEmailChange && !wantsPasswordChange) {
        messageDiv.innerHTML = '<div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-400 rounded-lg p-3 text-amber-800 dark:text-amber-300 text-sm">Enter a new email and/or new password to save changes.</div>';
        return;
    }

    if (wantsEmailChange && !validateEmail(newEmail)) {
        messageDiv.innerHTML = '<div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-3 text-red-700 dark:text-red-400 text-sm">Invalid email address</div>';
        return;
    }

    if (wantsPasswordChange) {
        if (newPassword.length < 8) {
            messageDiv.innerHTML = '<div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-3 text-red-700 dark:text-red-400 text-sm">New password must be at least 8 characters</div>';
            return;
        }
        if (newPassword !== confirmPassword) {
            messageDiv.innerHTML = '<div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-3 text-red-700 dark:text-red-400 text-sm">New passwords do not match</div>';
            return;
        }
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const errors = [];
    let emailUpdated = false;
    let passwordUpdated = false;

    try {
        if (wantsPasswordChange) {
            const res = await fetch('../../api/admin/profile.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'change_password',
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            const data = await res.json();
            if (data.success) {
                passwordUpdated = true;
            } else {
                errors.push(data.message || 'Failed to update password');
            }
        }

        if (wantsEmailChange && errors.length === 0) {
            const res = await fetch('../../api/admin/profile.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'change_email',
                    new_email: newEmail,
                    password: currentPassword
                })
            });
            const data = await res.json();
            if (data.success) {
                emailUpdated = true;
                document.getElementById('currentEmail').value = newEmail;
            } else {
                errors.push(data.message || 'Failed to update email');
            }
        }

        if (errors.length) {
            messageDiv.innerHTML = `<div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-3 text-red-700 dark:text-red-400 text-sm">${escapeHtml(errors.join('. '))}</div>`;
        } else {
            const parts = [];
            if (passwordUpdated) parts.push('password');
            if (emailUpdated) parts.push('email');
            messageDiv.innerHTML = `<div class="bg-green-50 dark:bg-green-900/20 border border-green-400 rounded-lg p-3 text-green-700 dark:text-green-400 text-sm">Account ${parts.join(' and ')} updated successfully.</div>`;
            showToast('Account updated successfully', 'success');
            document.getElementById('new_email').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('current_password').value = '';
            if (emailUpdated) {
                setTimeout(() => window.location.reload(), 1200);
            }
        }
    } catch (error) {
        console.error('Error saving account:', error);
        messageDiv.innerHTML = '<div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-3 text-red-700 dark:text-red-400 text-sm">Error saving account changes. Please try again.</div>';
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Account Changes';
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('.toggle-password-icon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility_off';
    }
}

loadAdminData();
</script>

<?php
}

renderAdminLayout($page_title, 'profile', 'renderProfileContent');
