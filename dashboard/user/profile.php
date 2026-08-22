<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'My Profile | WyomingTrust';
$active_nav = 'profile';

include __DIR__ . '/includes/layout.php';
?>

<section>
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Profile Settings</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">Update your account information and password.</p>
</section>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-20">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-8">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Account Information</h2>
<div class="space-y-4">
<div>
<label class="block font-label-md text-label-md font-semibold mb-2 text-on-surface">Full Name</label>
<input type="text" id="fullName" class="w-full px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface text-sm focus:ring-2 focus:ring-secondary/50 focus:border-transparent">
</div>
<div>
<label class="block font-label-md text-label-md font-semibold mb-2 text-on-surface">Email Address</label>
<input type="email" id="email" class="w-full px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface text-sm focus:ring-2 focus:ring-secondary/50 focus:border-transparent">
<p class="text-xs text-on-surface-variant mt-2">Email verification: <span id="emailStatus" class="font-semibold">--</span></p>
</div>
<button type="button" onclick="updateProfile()" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors">
Save Changes
</button>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-8">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Change Password</h2>
<div class="space-y-4">
<div>
<label class="block font-label-md text-label-md font-semibold mb-2 text-on-surface">Current Password</label>
<div class="relative">
<input type="password" id="currentPassword" class="w-full px-4 py-3 pr-12 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface text-sm focus:ring-2 focus:ring-secondary/50">
<button type="button" onclick="togglePasswordVisibility('currentPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'w-5 h-5'); ?>
</button>
</div>
</div>
<div>
<label class="block font-label-md text-label-md font-semibold mb-2 text-on-surface">New Password</label>
<div class="relative">
<input type="password" id="newPassword" class="w-full px-4 py-3 pr-12 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface text-sm focus:ring-2 focus:ring-secondary/50">
<button type="button" onclick="togglePasswordVisibility('newPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'w-5 h-5'); ?>
</button>
</div>
</div>
<div>
<label class="block font-label-md text-label-md font-semibold mb-2 text-on-surface">Confirm New Password</label>
<div class="relative">
<input type="password" id="confirmPassword" class="w-full px-4 py-3 pr-12 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface text-sm focus:ring-2 focus:ring-secondary/50">
<button type="button" onclick="togglePasswordVisibility('confirmPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'w-5 h-5'); ?>
</button>
</div>
</div>
<button type="button" onclick="changePassword()" class="w-full bg-surface-container text-on-surface py-3 rounded-lg font-bold hover:bg-surface-container-high transition-colors border border-outline-variant">
Change Password
</button>
</div>
</div>
</div>

<script>
let userProfile = null;

function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const svg = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        if (svg && typeof wtIcon === 'function') btn.innerHTML = wtIcon('visibility', 'w-5 h-5');
    } else {
        input.type = 'password';
        if (svg && typeof wtIcon === 'function') btn.innerHTML = wtIcon('visibility-off', 'w-5 h-5');
    }
}

async function loadProfile() {
    try {
        const response = await fetch('../../api/user/profile.php', { credentials: 'same-origin' });
        const data = await response.json();
        if (data.success && data.user) {
            userProfile = data.user;
            document.getElementById('fullName').value = userProfile.full_name || '';
            document.getElementById('email').value = userProfile.email || '';
            const statusEl = document.getElementById('emailStatus');
            statusEl.textContent = userProfile.email_verified ? 'Verified' : 'Not Verified';
            statusEl.className = userProfile.email_verified ? 'font-semibold text-deep-forest' : 'font-semibold text-secondary';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

async function updateProfile() {
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    if (!fullName || !email) {
        alert('Please fill in all fields');
        return;
    }
    try {
        const response = await fetch('../../api/user/profile.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ full_name: fullName, email: email })
        });
        const data = await response.json();
        if (data.success) {
            alert('Profile updated successfully!');
            if (email !== userProfile.email) {
                alert('Verification email sent. Please verify your new email address.');
            }
            loadProfile();
        } else {
            alert(data.message || 'Failed to update profile');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Failed to update profile');
    }
}

async function changePassword() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    if (!current || !newPass || !confirm) {
        alert('Please fill in all password fields');
        return;
    }
    if (newPass !== confirm) {
        alert('New passwords do not match');
        return;
    }
    if (newPass.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    try {
        const response = await fetch('../../api/user/profile.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ current_password: current, password: newPass })
        });
        const data = await response.json();
        if (data.success) {
            alert('Password changed successfully!');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            alert(data.message || 'Failed to change password');
        }
    } catch (error) {
        console.error('Error changing password:', error);
        alert('Failed to change password');
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
