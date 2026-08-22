<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Forgot Password | ' . $site_name;
include 'includes/header.php';
?>
<section class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-section-gap-md px-6 lg:px-12 bg-surface">
<div class="max-w-md w-full bg-surface-pure rounded-xl p-8 md:p-10 shadow-sm border border-outline-variant/30">
<div class="text-center mb-8">
<h1 class="font-headline-lg text-headline-lg text-primary mb-3">Forgot Password?</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Enter your email and we'll send reset instructions.</p>
</div>
<form class="space-y-6" id="forgotPasswordForm">
<div class="space-y-2">
<label for="email" class="font-label-md text-label-md text-on-surface-variant">Email Address</label>
<input id="email" name="email" type="email" required class="w-full bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg px-4 py-3 transition-colors" placeholder="Enter your registered email">
</div>
<div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
<div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
<p class="flex items-center gap-2">
<?php echo wt_icon('check-circle', 'text-sm'); ?>
<span id="successText">Password reset link has been sent to your email address.</span>
</p>
</div>
<button type="submit" class="w-full bg-secondary text-on-secondary flex items-center justify-center gap-2 py-4 rounded-lg font-bold hover:opacity-90 transition-all">
Send Reset Instructions
<?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
</button>
<div class="text-center space-y-2 pt-2">
<p class="text-sm text-on-surface-variant">
Remember your password? <a href="login.php" class="text-secondary font-bold hover:underline">Sign In</a>
</p>
</div>
</form>
</div>
</section>
<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const emailInput = document.getElementById('email');
    const email = emailInput.value.trim();
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    errorMessage.classList.add('hidden');
    successMessage.classList.add('hidden');
    if (!email) {
        errorMessage.textContent = 'Please enter your email address';
        errorMessage.classList.remove('hidden');
        return;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Sending...';
    try {
        const response = await fetch('api/forgot-password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ email: email })
        });
        const data = await response.json();
        if (data.success) {
            successMessage.classList.remove('hidden');
            emailInput.disabled = true;
            submitBtn.innerHTML = '<?php echo wt_icon('check-circle', 'w-5 h-5'); ?> Email Sent!';
            submitBtn.classList.remove('bg-secondary');
            submitBtn.classList.add('bg-green-600');
        } else {
            errorMessage.textContent = data.message || 'Failed to send reset email. Please try again.';
            errorMessage.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Send Reset Instructions <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>';
        }
    } catch (error) {
        errorMessage.textContent = 'An error occurred. Please try again later.';
        errorMessage.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Send Reset Instructions <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>';
    }
});
</script>
<?php include 'includes/footer.php'; ?>
