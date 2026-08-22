<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Verify Your Email | ' . $site_name;
include 'includes/header.php';
$email = $_GET['email'] ?? $_SESSION['user_email'] ?? '';
?>
<section class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-section-gap-md px-6 lg:px-12 bg-surface">
<div class="max-w-md w-full bg-surface-pure rounded-xl p-8 md:p-10 shadow-sm border border-outline-variant/30">
<div class="flex justify-center mb-6">
<div class="bg-secondary-fixed p-4 rounded-full">
<?php echo wt_icon('mail', 'text-secondary text-5xl'); ?>
</div>
</div>
<h2 class="text-center font-headline-lg text-headline-lg text-primary mb-2">Check Your Email</h2>
<p class="text-center font-body-md text-body-md text-on-surface-variant mb-6">
We've sent a verification email to
<strong class="text-primary"><?php echo htmlspecialchars($email); ?></strong>
</p>
<p class="text-center text-sm text-on-surface-variant mb-8">
Please click the verification link in the email to activate your account. The link will expire in 24 hours.
</p>
<div class="space-y-4">
<div id="resendContainer">
<p class="text-center text-sm text-on-surface-variant mb-4">Didn't receive the email?</p>
<button id="resendButton" type="button" class="w-full bg-secondary text-on-secondary px-6 py-3 rounded-lg font-bold hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed" onclick="resendVerificationEmail()">
Resend Verification Email
</button>
</div>
<div id="countdownContainer" class="hidden text-center">
<p class="text-sm text-on-surface-variant mb-2">
Please wait <span id="countdown" class="font-bold text-secondary">60</span> seconds before requesting another email.
</p>
<div class="w-full bg-surface-container-high rounded-full h-2">
<div id="countdownBar" class="bg-secondary h-2 rounded-full transition-all duration-1000" style="width: 100%;"></div>
</div>
</div>
<div id="messageContainer" class="hidden"></div>
<div class="pt-4 border-t border-outline-variant/30">
<p class="text-center text-sm text-on-surface-variant mb-4">Already verified?</p>
<a href="login.php" class="block w-full text-center bg-surface-container-low text-primary px-6 py-3 rounded-lg font-bold hover:bg-surface-container transition-colors">
Go to Login
</a>
</div>
</div>
</div>
</section>
<script>
let countdownInterval = null;
let cooldownRemaining = 0;
const cooldownEnd = sessionStorage.getItem('verification_cooldown_end');
if (cooldownEnd) {
    const remaining = Math.max(0, Math.ceil((parseInt(cooldownEnd) - Date.now()) / 1000));
    if (remaining > 0) startCountdown(remaining);
}
function resendVerificationEmail() {
    const email = <?php echo json_encode($email); ?>;
    if (!email) { showMessage('Email address not found. Please register again.', 'error'); return; }
    const button = document.getElementById('resendButton');
    const messageContainer = document.getElementById('messageContainer');
    button.disabled = true;
    button.textContent = 'Sending...';
    messageContainer.classList.add('hidden');
    fetch('api/user/resend-verification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Verification email sent successfully!', 'success');
            const cooldown = data.cooldown_remaining || 60;
            startCountdown(cooldown);
            sessionStorage.setItem('verification_cooldown_end', (Date.now() + cooldown * 1000).toString());
        } else {
            showMessage(data.message || 'Failed to send verification email. Please try again.', 'error');
            if (data.cooldown_remaining && data.cooldown_remaining > 0) {
                startCountdown(data.cooldown_remaining);
                sessionStorage.setItem('verification_cooldown_end', (Date.now() + data.cooldown_remaining * 1000).toString());
            } else {
                button.disabled = false;
                button.textContent = 'Resend Verification Email';
            }
        }
    })
    .catch(() => {
        showMessage('An error occurred. Please try again later.', 'error');
        button.disabled = false;
        button.textContent = 'Resend Verification Email';
    });
}
function startCountdown(seconds) {
    const button = document.getElementById('resendButton');
    const resendContainer = document.getElementById('resendContainer');
    const countdownContainer = document.getElementById('countdownContainer');
    cooldownRemaining = seconds;
    if (countdownInterval) clearInterval(countdownInterval);
    resendContainer.classList.add('hidden');
    countdownContainer.classList.remove('hidden');
    updateCountdownDisplay();
    countdownInterval = setInterval(() => {
        cooldownRemaining--;
        updateCountdownDisplay();
        if (cooldownRemaining <= 0) {
            clearInterval(countdownInterval);
            countdownInterval = null;
            countdownContainer.classList.add('hidden');
            resendContainer.classList.remove('hidden');
            button.disabled = false;
            button.textContent = 'Resend Verification Email';
            sessionStorage.removeItem('verification_cooldown_end');
        }
    }, 1000);
}
function updateCountdownDisplay() {
    const countdownDisplay = document.getElementById('countdown');
    const countdownBar = document.getElementById('countdownBar');
    if (countdownDisplay) countdownDisplay.textContent = cooldownRemaining;
    if (countdownBar && cooldownRemaining > 0) countdownBar.style.width = ((cooldownRemaining / 60) * 100) + '%';
}
function showMessage(message, type) {
    const messageContainer = document.getElementById('messageContainer');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700';
    messageContainer.className = `p-4 rounded-lg border text-sm ${bgColor} mb-4`;
    messageContainer.textContent = message;
    messageContainer.classList.remove('hidden');
    if (type === 'success') setTimeout(() => messageContainer.classList.add('hidden'), 5000);
}
</script>
<?php include 'includes/footer.php'; ?>
