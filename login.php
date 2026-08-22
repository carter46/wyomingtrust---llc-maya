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
$bg_image = asset_url('Storage/images/wyoming-business-landscape.jpg');
$favicon_href = asset_url('Storage/images/logo_ant.webp');
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
<link rel="icon" href="<?php echo escape_html($favicon_href); ?>" type="image/webp"/>
<style>
@layer base {
    html, body { margin: 0; padding: 0; height: 100%; }
    body { overscroll-behavior: none; }
}
::-webkit-scrollbar { display: none; }

.bg-premium {
    background: linear-gradient(135deg, #121c2a 0%, #1e293b 50%, #0f172a 100%);
    position: relative;
}
.bg-premium::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('<?php echo escape_html($bg_image); ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.14;
    pointer-events: none;
}
.bg-premium::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top right, rgba(0, 35, 111, 0.35) 0%, transparent 42%),
        radial-gradient(circle at bottom left, rgba(64, 89, 170, 0.18) 0%, transparent 42%),
        linear-gradient(135deg, rgba(18, 28, 42, 0.88) 0%, rgba(15, 23, 42, 0.92) 100%);
    pointer-events: none;
}

.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
}

.glass-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}
.glass-input:focus {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 0 2px rgba(182, 196, 255, 0.1);
}
.glass-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

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
<body class="bg-premium font-body-md text-white min-h-screen flex flex-col antialiased selection:bg-primary-fixed-dim selection:text-primary">
<main class="flex-grow flex items-center justify-center p-gutter relative z-10 w-full min-h-screen">
<div class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-24 relative z-20">

<div class="w-full lg:w-5/12 flex flex-col justify-center space-y-10 animate-[fade-in-up_1s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
<div class="space-y-6">
<a href="<?php echo escape_html($home_href); ?>" class="inline-flex items-center gap-2 text-white/70 hover:text-white transition-colors text-sm font-medium mb-2">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span>Back to <?php echo escape_html($site_name); ?></span>
</a>
<h1 class="font-headline-xl text-5xl lg:text-6xl text-white tracking-tight font-light leading-tight">
Welcome <br/><span class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70">back.</span>
</h1>
<div class="w-12 h-1 bg-gradient-to-r from-primary-fixed-dim to-transparent rounded-full"></div>
<p class="font-body-lg text-white/80 max-w-md leading-relaxed font-light text-lg">
Access your estate plan, manage your crypto trust, and protect your family's digital legacy. Secure, modern planning for your future.
</p>
</div>
<div class="hidden lg:block pt-12">
<div class="flex items-center gap-3 text-white/50 text-label-sm font-label-sm tracking-widest uppercase">
<span class="relative flex h-2 w-2">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-fixed-dim opacity-75"></span>
<span class="relative inline-flex rounded-full h-2 w-2 bg-primary-fixed-dim"></span>
</span>
SECURE CONNECTION ESTABLISHED
</div>
</div>
</div>

<div class="w-full lg:w-7/12 max-w-lg mx-auto lg:mx-0 animate-[fade-in-up_1s_ease-out_forwards] opacity-0" style="animation-delay: 0.3s;">
<div class="glass-panel rounded-3xl p-8 sm:p-12 relative overflow-hidden group" id="loginCard">
<div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-fixed-dim/10 rounded-full blur-3xl pointer-events-none"></div>

<div class="mb-10 relative z-10 text-center">
<div class="text-label-sm font-label-sm text-primary-fixed-dim uppercase tracking-widest mb-3 flex items-center justify-center gap-3">
<div class="h-[1px] w-8 bg-primary-fixed-dim/30"></div>
<span>Client Portal</span>
<div class="h-[1px] w-8 bg-primary-fixed-dim/30"></div>
</div>
<h2 class="font-headline-md text-2xl text-white mb-2 font-semibold">Log in to your account</h2>
<p class="font-body-md text-white/60 text-[15px] font-light">Manage your Wills, Trusts, and digital legacy documents.</p>
</div>

<div id="verificationSuccess" class="hidden mb-6 p-4 bg-emerald-500/10 border border-emerald-400/20 text-emerald-100 font-label-md text-[13px] flex items-center gap-3 rounded-xl backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
<span>Email verified successfully! You can now log in.</span>
</div>
<div id="passwordResetSuccess" class="hidden mb-6 p-4 bg-emerald-500/10 border border-emerald-400/20 text-emerald-100 font-label-md text-[13px] flex items-center gap-3 rounded-xl backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
<span>Password reset successfully! You can now log in with your new password.</span>
</div>
<div id="errorMessage" class="hidden mb-6 p-4 bg-error/10 border border-error/20 text-error-container font-label-md text-[13px] flex items-center gap-3 rounded-xl backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">error</span>
<span id="errorMessageText">Invalid credentials. Please verify and try again.</span>
</div>

<div id="verificationNotice" class="hidden mb-6 p-4 bg-amber-500/10 border border-amber-400/20 rounded-xl backdrop-blur-md">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-amber-300 text-[20px]">warning</span>
<div class="flex-1">
<h3 class="font-bold text-amber-100 mb-1 text-sm">Email Verification Required</h3>
<p class="text-sm text-amber-100/80 mb-3">Please verify your email address before logging in.</p>
<button id="resendVerificationBtn" type="button" class="w-full bg-amber-500/90 hover:bg-amber-400 text-[#121c2a] px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed" onclick="resendVerificationEmail()">
Resend Verification Email
</button>
<div id="resendCountdown" class="hidden text-center mt-3">
<p class="text-xs text-amber-100/80 mb-1">
Please wait <span id="countdownSeconds" class="font-bold">60</span> seconds before requesting another email.
</p>
<div class="w-full bg-amber-900/40 rounded-full h-1.5">
<div id="countdownProgress" class="bg-amber-400 h-1.5 rounded-full transition-all duration-1000" style="width: 100%;"></div>
</div>
</div>
<div id="resendMessage" class="hidden text-xs mt-2"></div>
</div>
</div>
</div>

<form class="space-y-6 relative z-10" id="loginForm">
<div class="space-y-2 group/input">
<label class="block font-label-sm text-white/80 tracking-wide uppercase" for="email">Email Address</label>
<div class="relative">
<span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40 group-focus-within/input:text-white transition-colors">
<span class="material-symbols-outlined text-[20px] font-light">mail</span>
</span>
<input class="w-full h-14 pl-12 pr-4 glass-input rounded-xl font-body-md focus:outline-none transition-all" id="email" name="email" placeholder="name@example.com" required type="email" autocomplete="email"/>
</div>
</div>

<div class="space-y-2 group/input">
<div class="flex justify-between items-center">
<label class="block font-label-sm text-white/80 tracking-wide uppercase" for="password">Password</label>
<a class="font-label-sm text-[12px] text-primary-fixed-dim hover:text-white transition-colors tracking-normal" href="<?php echo escape_html($forgot_href); ?>">Forgot password?</a>
</div>
<div class="relative">
<span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40 group-focus-within/input:text-white transition-colors">
<span class="material-symbols-outlined text-[20px] font-light">lock</span>
</span>
<input class="w-full h-14 pl-12 pr-12 glass-input rounded-xl font-body-md focus:outline-none transition-all" id="password" name="password" placeholder="••••••••" required type="password" autocomplete="current-password"/>
<button class="absolute right-4 top-1/2 -translate-y-1/2 text-white/40 hover:text-white focus:outline-none transition-colors" id="togglePassword" type="button" aria-label="Toggle password visibility">
<span class="material-symbols-outlined text-[20px] font-light" id="visibilityIcon">visibility_off</span>
</button>
</div>
</div>

<div class="flex items-center pt-2">
<div class="relative flex items-center justify-center">
<input class="h-5 w-5 rounded bg-white/5 border-white/20 text-primary-fixed-dim focus:ring-0 focus:ring-offset-0 cursor-pointer appearance-none checked:bg-primary-fixed-dim checked:border-primary-fixed-dim transition-all peer" id="remember_me" name="remember_me" type="checkbox"/>
<span class="material-symbols-outlined absolute text-white text-[14px] pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity">check</span>
</div>
<label class="ml-3 block font-body-md text-[14px] text-white/70 cursor-pointer hover:text-white transition-colors" for="remember_me">
Remember me
</label>
</div>

<input type="hidden" id="redirectTo" value="<?php echo escape_html($redirectTo); ?>">

<button class="w-full h-14 bg-white text-[#121c2a] font-label-md text-[15px] font-semibold rounded-xl hover:bg-white/90 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#121c2a] focus:ring-white transition-all duration-300 flex items-center justify-center gap-2 mt-4 group/btn disabled:opacity-80 disabled:cursor-not-allowed" type="submit" id="submitBtn">
<span>Sign In</span>
<span class="material-symbols-outlined text-[20px] group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
</button>
</form>

<div class="mt-10 text-center">
<p class="font-body-md text-[14px] text-white/60">
Don't have an account?
<a class="font-label-md text-white hover:text-primary-fixed-dim transition-colors ml-1 border-b border-white/30 hover:border-primary-fixed-dim pb-0.5" href="<?php echo escape_html($onboarding_href); ?>">
Get Started
</a>
</p>
</div>

<div class="mt-12 pt-8 border-t border-white/10 grid grid-cols-2 gap-4">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary-fixed-dim text-[24px] font-light">shield_lock</span>
<div>
<h4 class="font-label-sm text-white/90 tracking-wide">Bank-Level Security</h4>
<p class="text-[12px] text-white/50 mt-1 leading-snug font-light">Encrypted &amp; protected data.</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary-fixed-dim text-[24px] font-light">family_restroom</span>
<div>
<h4 class="font-label-sm text-white/90 tracking-wide">Trusted by Families</h4>
<p class="text-[12px] text-white/50 mt-1 leading-snug font-light">Over 500k families trust us.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</main>

<footer class="w-full border-t border-white/5 bg-transparent py-6 relative z-20 mt-auto">
<div class="max-w-7xl mx-auto px-gutter flex flex-col md:flex-row justify-between items-center gap-4">
<div class="flex items-center gap-2 text-white/40 text-label-sm font-label-sm tracking-wide">
<span class="material-symbols-outlined text-[16px]">verified_user</span>
Your data is encrypted and secured by <?php echo escape_html($site_name); ?>.
</div>
<div class="flex gap-8 text-label-sm text-white/40 tracking-wide">
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
    const visibilityIcon = document.getElementById('visibilityIcon');

    if (togglePassword && password && visibilityIcon) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            visibilityIcon.textContent = type === 'password' ? 'visibility_off' : 'visibility';
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
        ? 'bg-emerald-500/20 text-emerald-100'
        : 'bg-error/20 text-error-container';

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
