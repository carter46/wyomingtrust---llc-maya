<?php
require_once __DIR__ . '/api/helpers.php';

if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard';
    if ($redirect === 'onboarding') {
        header('Location: onboarding/onboarding.php');
        exit;
    } else {
        header('Location: dashboard/user/dashboard.php');
        exit;
    }
}

$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Sign In | ' . $site_name;
$redirectTo = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard';
include 'includes/header.php';
?>

<section class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-section-gap-md px-6 lg:px-12 bg-surface">
<div class="max-w-7xl w-full mx-auto grid lg:grid-cols-2 gap-16 items-center">
<!-- Left: branding -->
<div class="space-y-8">
<div>
<h1 class="font-headline-xl text-headline-xl text-primary mb-4">Welcome back.</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-md">
                        Access your estate plan, manage your crypto trust, and protect your family's digital legacy. Secure, modern planning for your future.
                    </p>
</div>
<div class="space-y-6">
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center flex-shrink-0">
<?php echo wt_icon('shield', 'text-primary'); ?>
</div>
<div>
<h3 class="font-label-md text-label-md text-primary uppercase tracking-wider">Bank-Level Security</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Your data is encrypted and protected by industry-leading security protocols.</p>
</div>
</div>
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-full bg-tertiary-fixed flex items-center justify-center flex-shrink-0">
<?php echo wt_icon('group', 'text-primary'); ?>
</div>
<div>
<h3 class="font-label-md text-label-md text-primary uppercase tracking-wider">Trusted by Families</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Over 500,000 families trust us to protect their legacy and loved ones.</p>
</div>
</div>
</div>
</div>

<!-- Right: login form -->
<div class="bg-surface-container-lowest rounded-xl p-8 md:p-10 shadow-[0_20px_40px_rgba(4,22,39,0.08)] border border-outline-variant/30">
<span class="inline-block px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm mb-4">SIGN IN</span>
<h2 class="font-headline-md text-headline-md text-primary mb-2">Log in to your account</h2>
<p class="font-body-md text-body-md text-on-surface-variant mb-8">Manage your Wills, Trusts, and digital legacy documents in one place.</p>

<form class="space-y-6" id="loginForm">
<div class="space-y-2">
<label for="email" class="font-label-md text-label-md text-on-surface-variant">Email Address</label>
<input id="email" name="email" type="email" required class="w-full bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg px-4 py-3 transition-colors" placeholder="john.doe@example.com"/>
</div>
<div class="space-y-2">
<label for="password" class="font-label-md text-label-md text-on-surface-variant">Password</label>
<div class="relative">
<input id="password" name="password" type="password" required class="w-full bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg px-4 py-3 pr-12 transition-colors" placeholder="Enter your password"/>
<button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary focus:outline-none" aria-label="Toggle password visibility">
<?php echo wt_icon('visibility-off', 'text-xl toggle-password-icon'); ?>
</button>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-secondary focus:ring-secondary border-outline-variant rounded">
<label for="remember_me" class="font-label-md text-label-md text-on-surface-variant">Remember me</label>
</div>
<a href="forgot-password.php" class="font-label-md text-label-md text-secondary font-bold hover:underline">Forgot password?</a>
</div>
<input type="hidden" id="redirectTo" value="<?php echo htmlspecialchars($redirectTo); ?>">

<div id="verificationSuccess" class="hidden bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
    <div class="flex items-center gap-2">
        <?php echo wt_icon('check-circle', 'text-sm'); ?>
        <span>Email verified successfully! You can now log in.</span>
    </div>
</div>
<div id="passwordResetSuccess" class="hidden bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
    <div class="flex items-center gap-2">
        <?php echo wt_icon('check-circle', 'text-sm'); ?>
        <span>Password reset successfully! You can now log in with your new password.</span>
    </div>
</div>
<div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>

<div id="verificationNotice" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-4">
    <div class="flex items-start gap-3">
        <?php echo wt_icon('warning', 'text-amber-600'); ?>
        <div class="flex-1">
            <h3 class="font-bold text-amber-900 mb-1">Email Verification Required</h3>
            <p class="text-sm text-amber-800 mb-3">Please verify your email address before logging in.</p>
            <div class="space-y-2">
                <button
                    id="resendVerificationBtn"
                    type="button"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    onclick="resendVerificationEmail()"
                >
                    Resend Verification Email
                </button>
                <div id="resendCountdown" class="hidden text-center">
                    <p class="text-xs text-amber-700 mb-1">
                        Please wait <span id="countdownSeconds" class="font-bold">60</span> seconds before requesting another email.
                    </p>
                    <div class="w-full bg-amber-200 rounded-full h-1.5">
                        <div id="countdownProgress" class="bg-amber-600 h-1.5 rounded-full transition-all duration-1000" style="width: 100%;"></div>
                    </div>
                </div>
                <div id="resendMessage" class="hidden text-xs mt-2"></div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="w-full bg-secondary text-on-secondary flex items-center justify-center gap-2 py-4 rounded-lg font-label-md text-label-md font-bold hover:opacity-90 transition-all shadow-md">
Sign In
<?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
</button>

<div class="text-center pt-2 space-y-2">
<p class="text-on-surface-variant font-body-md">
Don't have an account? <a class="text-secondary font-bold hover:underline" href="onboarding/onboarding.php">Get Started</a>
</p>
<p class="text-xs text-on-surface-variant flex items-center justify-center gap-1">
<?php echo wt_icon('lock', 'text-sm'); ?>
Your data is encrypted and secured
</p>
</div>
</form>
</div>
</div>
</section>

<script>
let countdownInterval = null;
let cooldownRemaining = 0;
let currentEmail = '';

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('verified') === '1') {
        const verificationSuccess = document.getElementById('verificationSuccess');
        if (verificationSuccess) {
            verificationSuccess.classList.remove('hidden');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    if (urlParams.get('reset') === 'success') {
        const passwordResetSuccess = document.getElementById('passwordResetSuccess');
        if (passwordResetSuccess) {
            passwordResetSuccess.classList.remove('hidden');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    const cooldownEnd = sessionStorage.getItem('login_verification_cooldown_end');
    if (cooldownEnd) {
        const remaining = Math.max(0, Math.ceil((parseInt(cooldownEnd) - Date.now()) / 1000));
        if (remaining > 0) {
            const email = sessionStorage.getItem('login_verification_email');
            if (email) {
                startResendCountdown(remaining, email);
            }
        }
    }

    const loginForm = document.getElementById('loginForm');
    if (!loginForm) {
        console.error('Login form not found');
        return;
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const errorMessage = document.getElementById('errorMessage');
        const verificationNotice = document.getElementById('verificationNotice');

        currentEmail = emailInput.value;

        const formData = {
            email: currentEmail,
            password: passwordInput.value
        };

        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            sessionStorage.removeItem('login_verification_cooldown_end');
            sessionStorage.removeItem('login_verification_email');

            setTimeout(() => {
                const redirectTo = document.getElementById('redirectTo').value || 'dashboard';
                if (redirectTo === 'onboarding') {
                    window.location.href = 'onboarding/onboarding.php';
                } else {
                    window.location.href = 'dashboard/user/dashboard.php';
                }
            }, 100);
        } else {
            errorMessage.textContent = data.message || 'Login failed';
            errorMessage.classList.remove('hidden');

            if (response.status === 403 || data.message?.toLowerCase().includes('verify')) {
                verificationNotice.classList.remove('hidden');
                errorMessage.classList.add('hidden');
            } else {
                verificationNotice.classList.add('hidden');
            }
        }
    });
});

function resendVerificationEmail() {
    const email = currentEmail || document.getElementById('email').value;

    if (!email) {
        showResendMessage('Please enter your email address first.', 'error');
        return;
    }

    const button = document.getElementById('resendVerificationBtn');
    const messageContainer = document.getElementById('resendMessage');

    button.disabled = true;
    button.textContent = 'Sending...';
    messageContainer.classList.add('hidden');

    fetch('api/user/resend-verification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResendMessage(data.message || 'Verification email sent successfully!', 'success');

            const cooldown = data.cooldown_remaining || 60;
            startResendCountdown(cooldown, email);

            const cooldownEnd = Date.now() + (cooldown * 1000);
            sessionStorage.setItem('login_verification_cooldown_end', cooldownEnd.toString());
            sessionStorage.setItem('login_verification_email', email);
        } else {
            showResendMessage(data.message || 'Failed to send verification email. Please try again.', 'error');

            if (data.cooldown_remaining && data.cooldown_remaining > 0) {
                startResendCountdown(data.cooldown_remaining, email);
                const cooldownEnd = Date.now() + (data.cooldown_remaining * 1000);
                sessionStorage.setItem('login_verification_cooldown_end', cooldownEnd.toString());
                sessionStorage.setItem('login_verification_email', email);
            } else {
                button.disabled = false;
                button.textContent = 'Resend Verification Email';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResendMessage('An error occurred. Please try again later.', 'error');
        button.disabled = false;
        button.textContent = 'Resend Verification Email';
    });
}

function startResendCountdown(seconds, email) {
    const button = document.getElementById('resendVerificationBtn');
    const countdownContainer = document.getElementById('resendCountdown');
    const countdownDisplay = document.getElementById('countdownSeconds');
    const countdownProgress = document.getElementById('countdownProgress');

    cooldownRemaining = seconds;
    currentEmail = email;

    if (countdownInterval) {
        clearInterval(countdownInterval);
    }

    button.classList.add('hidden');
    countdownContainer.classList.remove('hidden');
    updateResendCountdown();

    countdownInterval = setInterval(() => {
        cooldownRemaining--;
        updateResendCountdown();

        if (cooldownRemaining <= 0) {
            clearInterval(countdownInterval);
            countdownInterval = null;

            countdownContainer.classList.add('hidden');
            button.classList.remove('hidden');
            button.disabled = false;
            button.textContent = 'Resend Verification Email';

            sessionStorage.removeItem('login_verification_cooldown_end');
            sessionStorage.removeItem('login_verification_email');
        }
    }, 1000);
}

function updateResendCountdown() {
    const countdownDisplay = document.getElementById('countdownSeconds');
    const countdownProgress = document.getElementById('countdownProgress');

    if (countdownDisplay) {
        countdownDisplay.textContent = cooldownRemaining;
    }

    if (countdownProgress && cooldownRemaining > 0) {
        const percentage = (cooldownRemaining / 60) * 100;
        countdownProgress.style.width = percentage + '%';
    }
}

function showResendMessage(message, type) {
    const messageContainer = document.getElementById('resendMessage');

    const bgColor = type === 'success'
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-700';

    messageContainer.className = `p-2 rounded text-xs ${bgColor}`;
    messageContainer.textContent = message;
    messageContainer.classList.remove('hidden');

    if (type === 'success') {
        setTimeout(() => {
            messageContainer.classList.add('hidden');
        }, 5000);
    }
}

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
