<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Reset Password | ' . $site_name;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (empty($token)) {
    header('Location: login.php?error=missing_token');
    exit;
}
include 'includes/header.php';
?>
<section class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-section-gap-md px-6 lg:px-12 bg-surface">
<div class="max-w-md w-full bg-surface-pure rounded-xl p-8 md:p-10 shadow-sm border border-outline-variant/30">
<div class="text-center mb-8">
<h1 class="font-headline-lg text-headline-lg text-primary mb-3">Reset Your Password</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Enter your new password below</p>
</div>
<form class="space-y-6" id="resetPasswordForm">
<input type="hidden" id="resetToken" value="<?php echo htmlspecialchars($token); ?>">
<div id="tokenError" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
<p class="flex items-center gap-2">
<?php echo wt_icon('error', 'text-sm'); ?>
<span id="tokenErrorText">Invalid or expired reset token. Please request a new password reset link.</span>
</p>
</div>
<div id="passwordForm" class="space-y-4">
<div class="space-y-2">
<label for="password" class="font-label-md text-label-md text-on-surface-variant">New Password</label>
<div class="relative">
<input id="password" name="password" type="password" required class="w-full bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg px-4 py-3 pr-12 transition-colors" placeholder="Enter your new password">
<button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'text-xl toggle-password-icon'); ?>
</button>
</div>
<p class="text-xs text-on-surface-variant">Password must be at least 8 characters with uppercase, lowercase, number, and special character</p>
</div>
<div class="space-y-2">
<label for="confirm_password" class="font-label-md text-label-md text-on-surface-variant">Confirm New Password</label>
<div class="relative">
<input id="confirm_password" name="confirm_password" type="password" required class="w-full bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg px-4 py-3 pr-12 transition-colors" placeholder="Confirm your new password">
<button type="button" onclick="togglePasswordVisibility('confirm_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'text-xl toggle-password-icon'); ?>
</button>
</div>
</div>
</div>
<div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
<div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
<p class="flex items-center gap-2">
<?php echo wt_icon('check-circle', 'text-sm'); ?>
<span>Password reset successfully! Redirecting to login...</span>
</p>
</div>
<div id="submitButtonContainer">
<button type="submit" class="w-full bg-secondary text-on-secondary flex items-center justify-center gap-2 py-4 rounded-lg font-bold hover:opacity-90 transition-all">
Reset Password
<?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
</button>
</div>
<div class="text-center space-y-2 pt-2 text-sm text-on-surface-variant">
<p><a href="login.php" class="text-secondary font-bold hover:underline">Back to Sign In</a></p>
<p>Don't have a valid reset link? <a href="forgot-password.php" class="text-secondary font-bold hover:underline">Request a new one</a></p>
</div>
</form>
</div>
</section>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = document.getElementById('resetToken').value;
    const tokenError = document.getElementById('tokenError');
    const tokenErrorText = document.getElementById('tokenErrorText');
    const passwordForm = document.getElementById('passwordForm');
    const submitButtonContainer = document.getElementById('submitButtonContainer');
    if (!token || token.length < 32) {
        tokenError.classList.remove('hidden');
        tokenErrorText.textContent = 'Invalid reset token format. Please use the link from your email.';
        passwordForm.style.display = 'none';
        submitButtonContainer.style.display = 'none';
        return;
    }
    try {
        const response = await fetch(`api/reset-password.php?token=${encodeURIComponent(token)}`);
        const data = await response.json();
        if (!data.success || !data.valid) {
            tokenError.classList.remove('hidden');
            tokenErrorText.textContent = data.message || 'Invalid or expired reset token. Please request a new password reset link.';
            passwordForm.style.display = 'none';
            submitButtonContainer.style.display = 'none';
        }
    } catch (error) {
        console.error('Token validation error:', error);
    }
});

document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const token = document.getElementById('resetToken').value;
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    errorMessage.classList.add('hidden');
    successMessage.classList.add('hidden');
    if (!passwordInput.value || !confirmPasswordInput.value) {
        errorMessage.textContent = 'Please enter both password fields';
        errorMessage.classList.remove('hidden');
        return;
    }
    if (passwordInput.value !== confirmPasswordInput.value) {
        errorMessage.textContent = 'Passwords do not match';
        errorMessage.classList.remove('hidden');
        return;
    }
    if (passwordInput.value.length < 8) {
        errorMessage.textContent = 'Password must be at least 8 characters long';
        errorMessage.classList.remove('hidden');
        return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Resetting...';
    try {
        const response = await fetch('api/reset-password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ token: token, password: passwordInput.value, confirm_password: confirmPasswordInput.value })
        });
        const data = await response.json();
        if (data.success) {
            successMessage.classList.remove('hidden');
            passwordInput.disabled = true;
            confirmPasswordInput.disabled = true;
            setTimeout(() => { window.location.href = 'login.php?reset=success'; }, 3000);
        } else {
            errorMessage.textContent = data.message || 'Failed to reset password. Please try again or request a new reset link.';
            errorMessage.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Reset Password <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>';
        }
    } catch (error) {
        errorMessage.textContent = 'An error occurred. Please try again later.';
        errorMessage.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Reset Password <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>';
    }
});

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    if (input && button) {
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = wtIcon('visibility', 'w-5 h-5');
        } else {
            input.type = 'password';
            button.innerHTML = wtIcon('visibility-off', 'w-5 h-5');
        }
    }
}
</script>
<?php include 'includes/footer.php'; ?>
