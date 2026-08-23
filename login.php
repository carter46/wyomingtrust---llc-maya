<?php
require_once __DIR__ . '/api/helpers.php';

if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard';
    if ($redirect === 'onboarding') {
        header('Location: onboarding/onboarding.php');
        exit;
    }
    header('Location: dashboard/user/dashboard.php');
    exit;
}

$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Sign In | ' . $site_name;
$redirectTo = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard';
$logo_url = site_logo_url();
$favicon_href = site_favicon_url();
$forgot_href = asset_url('forgot-password.php');
$onboarding_href = asset_url('onboarding/onboarding.php');
$privacy_href = asset_url('privacy-policy.php');
$terms_href = asset_url('terms-of-service.php');
$home_href = asset_url('index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo escape_html($page_title); ?></title>
<?php if ($favicon_href): ?>
<link rel="icon" href="<?php echo escape_html($favicon_href); ?>"/>
<?php endif; ?>
<style>
@layer base {
    html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
    body { overscroll-behavior: none; }
}
::-webkit-scrollbar { display: none; }

<?php echo wt_premium_auth_bg_css(); ?>

.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    vertical-align: middle;
}

@keyframes fade-in-up {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "surface-bright": "#f9f9ff",
                "on-error": "#ffffff",
                "on-secondary": "#ffffff",
                "inverse-on-surface": "#ebf1ff",
                "inverse-primary": "#b6c4ff",
                "surface-pure": "#FFFFFF",
                "ink-dark": "#111827",
                "outline": "#757682",
                "on-primary-fixed-variant": "#264191",
                "on-tertiary-container": "#f39461",
                "ink-muted": "#6B7280",
                "secondary-container": "#fd761a",
                "inverse-surface": "#273140",
                "secondary-fixed": "#ffdbca",
                "primary-fixed": "#dce1ff",
                "on-primary": "#ffffff",
                "on-secondary-fixed-variant": "#783200",
                "on-error-container": "#93000a",
                "on-tertiary-fixed-variant": "#773205",
                "surface": "#f9f9ff",
                "on-surface-variant": "#444651",
                "on-tertiary-fixed": "#341100",
                "secondary": "#9d4300",
                "tertiary": "#4b1c00",
                "on-secondary-fixed": "#341100",
                "surface-variant": "#d9e3f7",
                "surface-tint": "#4059aa",
                "primary-container": "#1e3a8a",
                "error": "#ba1a1a",
                "on-background": "#121c2a",
                "tertiary-fixed": "#ffdbcb",
                "background": "#f9f9ff",
                "surface-container-high": "#dee9fd",
                "on-primary-container": "#90a8ff",
                "on-primary-fixed": "#00164e",
                "surface-container": "#e6eeff",
                "tertiary-fixed-dim": "#ffb691",
                "on-secondary-container": "#5c2400",
                "primary": "#00236f",
                "on-surface": "#121c2a",
                "surface-container-lowest": "#ffffff",
                "surface-dim": "#d0daef",
                "primary-fixed-dim": "#b6c4ff",
                "surface-container-highest": "#d9e3f7",
                "tertiary-container": "#6e2c00",
                "surface-subtle": "#F3F4F6",
                "outline-variant": "#c5c5d3",
                "on-tertiary": "#ffffff",
                "secondary-fixed-dim": "#ffb690",
                "surface-container-low": "#eff3ff",
                "error-container": "#ffdad6"
            },
            borderRadius: {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem",
                "2xl": "1rem",
                "3xl": "1.5rem"
            },
            spacing: {
                "section-gap-lg": "8rem",
                "gutter": "1.5rem",
                "grid-margin": "2rem",
                "stack-sm": "0.5rem",
                "section-gap-md": "4rem",
                "stack-md": "1rem"
            },
            fontFamily: {
                "headline-xl": ["Hanken Grotesk"],
                "body-lg": ["Inter"],
                "label-sm": ["Inter"],
                "headline-lg": ["Hanken Grotesk"],
                "headline-md": ["Hanken Grotesk"],
                "label-md": ["Inter"],
                "headline-lg-mobile": ["Hanken Grotesk"],
                "body-md": ["Inter"]
            },
            fontSize: {
                "headline-xl": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600", "letterSpacing": "0.05em" }],
                "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }],
                "headline-lg-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "600" }],
                "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }]
            }
        },
    },
}
</script>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;600;700&amp;family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
</head>
<body class="bg-premium bg-premium-auth font-body-md text-white h-dvh max-h-dvh overflow-hidden flex flex-col antialiased">
<main class="flex-1 min-h-0 flex items-center justify-center px-4 py-3 sm:px-6 w-full overflow-y-auto">
<div class="w-full max-w-5xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-6 lg:gap-12">

<div class="hidden lg:flex w-full lg:w-5/12 flex-col justify-center space-y-5 animate-[fade-in-up_1s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
<div class="space-y-4">
<a href="<?php echo escape_html($home_href); ?>" class="inline-flex items-center gap-2 text-white/75 hover:text-white transition-colors text-sm font-medium">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span>Back to Home</span>
</a>
<h1 class="font-headline-xl text-4xl xl:text-5xl text-white tracking-tight font-light leading-tight">
Welcome <br/><span class="font-bold text-primary-fixed-dim">back.</span>
</h1>
<div class="w-12 h-1 bg-gradient-to-r from-primary-fixed-dim to-transparent rounded-full"></div>
<p class="font-body-md text-white/80 max-w-md leading-relaxed font-light text-[15px]">
Access your Wyoming LLC dashboard, manage your business filings, and keep your company in good standing.
</p>
</div>
<div class="pt-4">
<div class="flex items-center gap-3 text-white/55 text-label-sm font-label-sm tracking-widest uppercase">
<span class="relative flex h-2 w-2">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-fixed-dim opacity-75"></span>
<span class="relative inline-flex rounded-full h-2 w-2 bg-primary-fixed-dim"></span>
</span>
SECURE CONNECTION ESTABLISHED
</div>
</div>
</div>

<div class="w-full lg:w-7/12 max-w-md mx-auto lg:mx-0 animate-[fade-in-up_1s_ease-out_forwards] opacity-0" style="animation-delay: 0.3s;">
<div class="bg-surface-pure rounded-xl p-5 sm:p-6 shadow-lg border border-outline-variant/30 relative overflow-hidden group text-on-surface" id="loginCard">

<a href="<?php echo escape_html($home_href); ?>" class="lg:hidden inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors text-xs font-medium mb-3 relative z-10">
<span class="material-symbols-outlined text-[16px]">arrow_back</span>
<span>Back</span>
</a>

<div class="mb-4 relative z-10 text-center">
<?php if ($logo_url): ?>
<div class="mb-4 flex justify-center">
<img src="<?php echo escape_html($logo_url); ?>" alt="<?php echo escape_html($site_name); ?>" class="h-16 sm:h-20 w-auto max-w-[280px] object-contain"/>
</div>
<div class="text-label-sm font-label-sm text-secondary uppercase tracking-widest mb-1.5 flex items-center justify-center gap-3">
<div class="h-[1px] w-6 bg-outline-variant/50"></div>
<span>Client Portal</span>
<div class="h-[1px] w-6 bg-outline-variant/50"></div>
</div>
<h2 class="font-headline-md text-xl text-primary mb-1 font-semibold">Log in to your account</h2>
<?php else: ?>
<h1 class="font-headline-lg text-headline-lg text-primary mb-3"><?php echo escape_html($site_name); ?></h1>
<p class="font-body-md text-on-surface-variant text-sm mb-1">Log in to your account</p>
<?php endif; ?>
<p class="font-body-md text-on-surface-variant text-[13px] font-light">Manage your LLC, documents, and business account.</p>
</div>

<div id="verificationSuccess" class="hidden mb-3 p-3 bg-green-50 border border-green-200 text-green-800 font-label-md text-[12px] flex items-center gap-2 rounded-lg">
<span class="material-symbols-outlined text-[16px]">check_circle</span>
<span>Email verified successfully! You can now log in.</span>
</div>
<div id="passwordResetSuccess" class="hidden mb-3 p-3 bg-green-50 border border-green-200 text-green-800 font-label-md text-[12px] flex items-center gap-2 rounded-lg">
<span class="material-symbols-outlined text-[16px]">check_circle</span>
<span>Password reset successfully! You can now log in with your new password.</span>
</div>
<div id="errorMessage" class="hidden mb-3 p-3 bg-red-50 border border-red-200 text-red-700 font-label-md text-[12px] flex items-center gap-2 rounded-lg">
<span class="material-symbols-outlined text-[16px]">error</span>
<span id="errorMessageText">Invalid credentials. Please verify and try again.</span>
</div>

<div id="verificationNotice" class="hidden mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
<div class="flex items-start gap-2">
<span class="material-symbols-outlined text-amber-600 text-[18px]">warning</span>
<div class="flex-1">
<h3 class="font-bold text-amber-900 mb-0.5 text-xs">Email Verification Required</h3>
<p class="text-xs text-amber-800 mb-2">Please verify your email before logging in.</p>
<button id="resendVerificationBtn" type="button" class="w-full bg-secondary text-on-secondary px-3 py-2 rounded-lg text-xs font-semibold transition-colors hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" onclick="resendVerificationEmail()">
Resend Verification Email
</button>
<div id="resendCountdown" class="hidden text-center mt-2">
<p class="text-[11px] text-amber-800 mb-1">
Please wait <span id="countdownSeconds" class="font-bold">60</span> seconds before requesting another email.
</p>
<div class="w-full bg-amber-200 rounded-full h-1">
<div id="countdownProgress" class="bg-secondary h-1 rounded-full transition-all duration-1000" style="width: 100%;"></div>
</div>
</div>
<div id="resendMessage" class="hidden text-[11px] mt-2"></div>
</div>
</div>
</div>

<form class="space-y-3 relative z-10" id="loginForm">
<div class="space-y-1 group/input">
<label class="block font-label-sm text-on-surface-variant tracking-wide uppercase text-[11px]" for="email">Email Address</label>
<div class="relative">
<span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline group-focus-within/input:text-secondary transition-colors">
<span class="material-symbols-outlined text-[18px] font-light">mail</span>
</span>
<input class="w-full h-11 pl-11 pr-3 bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg font-body-md text-sm text-on-surface focus:outline-none transition-colors" id="email" name="email" placeholder="name@example.com" required type="email" autocomplete="email"/>
</div>
</div>

<div class="space-y-1 group/input">
<div class="flex justify-between items-center">
<label class="block font-label-sm text-on-surface-variant tracking-wide uppercase text-[11px]" for="password">Password</label>
<a class="font-label-sm text-[11px] text-secondary font-bold hover:underline transition-colors tracking-normal" href="<?php echo escape_html($forgot_href); ?>">Forgot password?</a>
</div>
<div class="relative">
<span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline group-focus-within/input:text-secondary transition-colors">
<span class="material-symbols-outlined text-[18px] font-light">lock</span>
</span>
<input class="w-full h-11 pl-11 pr-11 bg-surface-container-low border-0 border-b-2 border-outline-variant focus:border-secondary focus:ring-0 rounded-t-lg font-body-md text-sm text-on-surface focus:outline-none transition-colors" id="password" name="password" placeholder="••••••••" required type="password" autocomplete="current-password"/>
<button class="absolute right-3.5 top-1/2 -translate-y-1/2 text-outline hover:text-secondary focus:outline-none transition-colors flex items-center justify-center" id="togglePassword" type="button" aria-label="Show password">
<span id="visibilityIconHide"><?php echo wt_icon('visibility-off', 'w-[18px] h-[18px]', 'currentColor'); ?></span>
<span id="visibilityIconShow" class="hidden"><?php echo wt_icon('visibility', 'w-[18px] h-[18px]', 'currentColor'); ?></span>
</button>
</div>
</div>

<div class="flex items-center">
<div class="relative flex items-center justify-center">
<input class="h-4 w-4 rounded border-outline-variant text-secondary focus:ring-secondary focus:ring-offset-0 cursor-pointer" id="remember_me" name="remember_me" type="checkbox"/>
</div>
<label class="ml-2.5 block font-body-md text-[13px] text-on-surface-variant cursor-pointer hover:text-on-surface transition-colors" for="remember_me">
Remember me
</label>
</div>

<input type="hidden" id="redirectTo" value="<?php echo escape_html($redirectTo); ?>">

<button class="w-full h-11 bg-secondary text-on-secondary font-label-md text-[14px] font-bold rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 transition-all duration-300 flex items-center justify-center gap-2 group/btn disabled:opacity-80 disabled:cursor-not-allowed" type="submit" id="submitBtn">
<span>Sign In</span>
<span class="material-symbols-outlined text-[18px] group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
</button>
</form>

<div class="mt-4 text-center">
<p class="font-body-md text-[13px] text-on-surface-variant">
Don't have an account?
<a class="font-label-md text-secondary font-bold hover:underline transition-colors ml-1" href="<?php echo escape_html($onboarding_href); ?>">
Get Started
</a>
</p>
</div>

<div class="mt-4 pt-4 border-t border-outline-variant/30 grid grid-cols-2 gap-3">
<div class="flex items-start gap-2">
<span class="material-symbols-outlined text-secondary text-[20px] font-light">shield_lock</span>
<div>
<h4 class="font-label-sm text-on-surface tracking-wide text-[11px]">Bank-Level Security</h4>
<p class="text-[11px] text-on-surface-variant mt-0.5 leading-snug font-light">Encrypted &amp; protected data.</p>
</div>
</div>
<div class="flex items-start gap-2">
<span class="material-symbols-outlined text-secondary text-[20px] font-light">family_restroom</span>
<div>
<h4 class="font-label-sm text-on-surface tracking-wide text-[11px]">Trusted by Founders</h4>
<p class="text-[11px] text-on-surface-variant mt-0.5 leading-snug font-light">100k+ businesses formed.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</main>

<footer class="w-full border-t border-white/10 bg-transparent py-2.5 sm:py-3 shrink-0">
<div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-2">
<div class="flex items-center gap-2 text-white/50 text-[11px] font-label-sm tracking-wide">
<span class="material-symbols-outlined text-[14px]">verified_user</span>
Your data is encrypted and secured by <?php echo escape_html($site_name); ?>.
</div>
<div class="flex gap-5 text-[11px] text-white/50 tracking-wide">
<a class="hover:text-white transition-colors" href="<?php echo escape_html($privacy_href); ?>">Privacy Policy</a>
<a class="hover:text-white transition-colors" href="<?php echo escape_html($terms_href); ?>">Terms of Service</a>
<a class="hover:text-white transition-colors" href="<?php echo escape_html($home_href); ?>">Home</a>
</div>
</div>
</footer>

<script>
let countdownInterval = null;
let cooldownRemaining = 0;
let currentEmail = '';

document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const visibilityIconHide = document.getElementById('visibilityIconHide');
    const visibilityIconShow = document.getElementById('visibilityIconShow');

    if (togglePassword && password && visibilityIconHide && visibilityIconShow) {
        togglePassword.addEventListener('click', function() {
            const showPassword = password.getAttribute('type') === 'password';
            password.setAttribute('type', showPassword ? 'text' : 'password');
            visibilityIconHide.classList.toggle('hidden', showPassword);
            visibilityIconShow.classList.toggle('hidden', !showPassword);
            togglePassword.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        });
    }

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
    if (!loginForm) return;

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const errorMessage = document.getElementById('errorMessage');
        const errorMessageText = document.getElementById('errorMessageText');
        const verificationNotice = document.getElementById('verificationNotice');
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnHtml = submitBtn.innerHTML;

        currentEmail = emailInput.value;

        errorMessage.classList.add('hidden');
        verificationNotice.classList.add('hidden');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[20px]">refresh</span><span>Authenticating...</span>';

        try {
            const response = await fetch('api/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    email: currentEmail,
                    password: passwordInput.value
                })
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
                errorMessageText.textContent = data.message || 'Login failed';
                errorMessage.classList.remove('hidden');

                if (response.status === 403 || data.message?.toLowerCase().includes('verify')) {
                    verificationNotice.classList.remove('hidden');
                    errorMessage.classList.add('hidden');
                } else {
                    verificationNotice.classList.add('hidden');
                    shakeLoginCard();
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        } catch (err) {
            console.error(err);
            errorMessageText.textContent = 'Something went wrong. Please try again.';
            errorMessage.classList.remove('hidden');
            shakeLoginCard();
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
});

function shakeLoginCard() {
    const card = document.getElementById('loginCard');
    if (!card) return;
    card.animate([
        { transform: 'translateX(0)' },
        { transform: 'translateX(-8px)' },
        { transform: 'translateX(8px)' },
        { transform: 'translateX(-8px)' },
        { transform: 'translateX(8px)' },
        { transform: 'translateX(0)' }
    ], { duration: 400, easing: 'ease-in-out' });
}

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
        headers: { 'Content-Type': 'application/json' },
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
    const verificationNotice = document.getElementById('verificationNotice');

    cooldownRemaining = seconds;
    currentEmail = email;

    if (countdownInterval) clearInterval(countdownInterval);

    if (verificationNotice) verificationNotice.classList.remove('hidden');
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

    if (countdownDisplay) countdownDisplay.textContent = cooldownRemaining;
    if (countdownProgress && cooldownRemaining > 0) {
        countdownProgress.style.width = ((cooldownRemaining / 60) * 100) + '%';
    }
}

function showResendMessage(message, type) {
    const messageContainer = document.getElementById('resendMessage');
    const bgColor = type === 'success'
        ? 'bg-green-50 text-green-800 border border-green-200'
        : 'bg-red-50 text-red-700 border border-red-200';

    messageContainer.className = `p-2 rounded text-xs ${bgColor}`;
    messageContainer.textContent = message;
    messageContainer.classList.remove('hidden');

    if (type === 'success') {
        setTimeout(() => messageContainer.classList.add('hidden'), 5000);
    }
}
</script>
</body>
</html>
