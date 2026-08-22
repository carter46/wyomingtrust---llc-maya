<?php
require_once __DIR__ . '/../api/helpers.php';

// Onboarding IS the registration process - allow both logged-in and logged-out users
// If logged in, skip registration step. If not logged in, Step 2 will include account creation.

// Treat sessions for deleted users as logged-out
$validUserId = get_valid_session_user_id();
$isLoggedIn = $validUserId !== false;
$userId = $isLoggedIn ? (int) $validUserId : 0;
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;

// If logged in, check email verification (only for logged-in users creating additional trusts)
if ($isLoggedIn) {
    try {
        require_once __DIR__ . '/../api/config.php';
        $db = getDatabase();
        $settings = $db->query('SELECT require_email_verification FROM site_settings WHERE id = 1 LIMIT 1')->fetch();
        $requireVerification = $settings ? (int) $settings['require_email_verification'] : 1;

        if ($requireVerification) {
            $user = $db->prepare('SELECT email_verified FROM users WHERE id = :id LIMIT 1');
            $user->execute([':id' => $userId]);
            $userData = $user->fetch();
            
            if (!$userData || !(int) $userData['email_verified']) {
                header('Location: ../verify-status.php?email=' . urlencode($_SESSION['user_email'] ?? ''));
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Onboarding email verification check failed: ' . $e->getMessage());
    }
}

$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Register Your LLC | ' . $site_name;
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($page_title); ?></title>
<link rel="icon" href="<?php echo escape_html(asset_url('Storage/images/logo_ant.webp')); ?>" type="image/webp"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="../assets/js/wt-icons.js"></script>
<script src="../assets/js/onboarding-data.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap" rel="stylesheet"/>

<script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "inverse-surface": "#2e3132",
                        "on-secondary": "#ffffff",
                        "on-background": "#191c1d",
                        "warm-cream": "#FEFDF3",
                        "on-secondary-container": "#003370",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#0b1d2d",
                        "primary-container": "#1a2b3c",
                        "secondary-fixed-dim": "#acc7ff",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed": "#d4e7db",
                        "background": "#f8f9fa",
                        "primary-fixed-dim": "#b7c8de",
                        "inverse-primary": "#b7c8de",
                        "tertiary-container": "#1d2d25",
                        "secondary-container": "#659dfe",
                        "surface-container-lowest": "#ffffff",
                        "secondary": "#115cb9",
                        "on-surface": "#191c1d",
                        "secondary-fixed": "#d7e2ff",
                        "on-error": "#ffffff",
                        "surface-container-low": "#f3f4f5",
                        "sky-accent": "#B6D6F2",
                        "deep-forest": "#2D4B3F",
                        "surface-variant": "#e1e3e4",
                        "plum-shadow": "#341B2F",
                        "error-container": "#ffdad6",
                        "surface-tint": "#4f6073",
                        "inverse-on-surface": "#f0f1f2",
                        "surface-container-highest": "#e1e3e4",
                        "outline-variant": "#c4c6cd",
                        "on-secondary-fixed-variant": "#004491",
                        "primary": "#041627",
                        "on-error-container": "#93000a",
                        "tertiary-fixed-dim": "#b8cbc0",
                        "surface": "#f8f9fa",
                        "on-primary": "#ffffff",
                        "on-tertiary-container": "#83958b",
                        "tertiary": "#081812",
                        "outline": "#74777d",
                        "surface-dim": "#d9dadb",
                        "on-secondary-fixed": "#001a40",
                        "surface-container-high": "#e7e8e9",
                        "on-primary-fixed-variant": "#38485a",
                        "on-tertiary-fixed": "#0f1f18",
                        "surface-container": "#edeeef",
                        "on-surface-variant": "#44474c",
                        "primary-fixed": "#d2e4fb",
                        "on-tertiary-fixed-variant": "#3a4a42",
                        "surface-bright": "#f8f9fa",
                        "on-primary-container": "#8192a7"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    maxWidth: {
                        "container-max": "1200px"
                    },
                    spacing: {
                        "container-max": "1200px",
                        "section-padding-md": "48px",
                        "margin-mobile": "16px",
                        "stack-gap": "16px",
                        "section-padding-lg": "80px",
                        "gutter": "24px"
                    },
                    fontFamily: {
                        "headline-lg-mobile": ["\"Source Serif 4\"", "serif"],
                        "headline-lg": ["\"Source Serif 4\"", "serif"],
                        "display-lg": ["\"Source Serif 4\"", "serif"],
                        "label-sm": ["DM Sans", "sans-serif"],
                        "label-md": ["DM Sans", "sans-serif"],
                        "body-md": ["DM Sans", "sans-serif"],
                        "headline-md": ["\"Source Serif 4\"", "serif"],
                        "body-lg": ["DM Sans", "sans-serif"]
                    },
                    fontSize: {
                        "headline-lg-mobile": ["28px", { lineHeight: "36px", fontWeight: "600" }],
                        "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }],
                        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "label-sm": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "700" }],
                        "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "500" }],
                        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }]
                    }
                }
            }
        };
    </script>
<style>
        .wt-icon { display: inline-block; vertical-align: middle; flex-shrink: 0; width: 1.25rem; height: 1.25rem; }
        .payment-method-card {
            cursor: pointer;
            transition: all 0.2s;
        }
        .payment-method-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(4, 22, 39, 0.08);
        }
        .payment-method-radio input[type="radio"] {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #c4c6cd;
            border-radius: 50%;
            position: relative;
            cursor: pointer;
        }
        .payment-method-radio input[type="radio"]:checked {
            border-color: #115cb9;
        }
        .payment-method-radio input[type="radio"]:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #115cb9;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen">
<div class="min-h-screen flex flex-col">
<header class="sticky top-0 z-50 bg-surface border-b border-outline-variant/30">
<div class="max-w-container-max mx-auto px-gutter h-16 flex items-center justify-between">
<?php include __DIR__ . '/../includes/components/site-logo.php'; ?>
<div class="flex items-center gap-3 sm:gap-4">
<?php if ($isLoggedIn): ?>
<a href="../dashboard/user/dashboard.php" class="hidden sm:inline font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors">Dashboard</a>
<a href="../api/logout.php" class="font-label-md text-label-md text-error hover:opacity-80 transition-opacity">Logout</a>
<?php else: ?>
<a href="../login.php" class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors">Sign In</a>
<?php endif; ?>
</div>
</div>
</header>

<div class="bg-surface-container border-b border-outline-variant/30">
<div class="max-w-container-max mx-auto px-gutter py-4">
<div class="flex items-center justify-between mb-2 gap-2">
<span class="font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Step <span id="currentStep"><?php echo $step; ?></span> of 5</span>
<span class="font-label-md text-label-md text-on-surface-variant truncate text-right" id="stepTitle">Business Type</span>
</div>
<div class="w-full bg-surface-container-high rounded-full h-1.5 overflow-hidden">
<div id="progressBar" class="bg-secondary h-1.5 rounded-full transition-all duration-300" style="width: <?php echo min(100, max(0, ($step / 5) * 100)); ?>%"></div>
</div>
</div>
</div>

<main class="flex-1 max-w-container-max mx-auto w-full px-gutter py-8 lg:py-12">
<div class="bg-surface-container-lowest rounded-xl shadow-[0_20px_40px_rgba(4,22,39,0.08)] border border-outline-variant/30 p-6 lg:p-8">
<div id="onboardingContent">
<div class="text-center py-6 sm:py-10">
<div class="animate-spin rounded-full h-8 w-8 sm:h-12 sm:w-12 border-4 border-secondary border-t-transparent mx-auto mb-4"></div>
<p class="font-body-md text-body-md text-on-surface-variant">Loading...</p>
</div>
</div>
</div>
</main>
</div>

<script>
let currentStep = <?php echo (int) $step; ?>;
const trustId = <?php echo (int) $trustId; ?>;
let isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
// Prefer shared lists from onboarding-data.js (avoids inline-script constant issues)
const US_JURISDICTIONS = (window.US_JURISDICTIONS || window.US_FORMATIONS || []);
const BUSINESS_ENDING_OPTIONS = (window.BUSINESS_ENDING_OPTIONS || []);
const US_FORMATIONS = US_JURISDICTIONS;
const ONBOARDING_TOTAL_STEPS = 5;
const ONBOARDING_STEP = {
    BUSINESS_ENTITY: 1,
    TRUST_TYPE: 2,
    PERSONAL_INFO: 3,
    BENEFICIARIES: 4,
    REVIEW: 5,
};
const steps = [
    { id: ONBOARDING_STEP.BUSINESS_ENTITY, title: 'Business Type', component: 'businessEntity' },
    { id: ONBOARDING_STEP.TRUST_TYPE, title: 'LLC Structure', component: 'trustType' },
    { id: ONBOARDING_STEP.PERSONAL_INFO, title: 'Business & Personal Info', component: 'personalInfo' },
    { id: ONBOARDING_STEP.BENEFICIARIES, title: 'Beneficiaries', component: 'beneficiaries' },
    { id: ONBOARDING_STEP.REVIEW, title: 'Review & Payment', component: 'review' }
];

function withTimeout(promise, ms) {
    return Promise.race([
        Promise.resolve(promise).catch((err) => {
            console.warn('Timed task failed:', err);
            return null;
        }),
        new Promise((resolve) => setTimeout(resolve, ms))
    ]);
}

function onboardingStepUrl(step) {
    return `onboarding.php?step=${step}${trustId ? '&trust_id=' + trustId : ''}`;
}

async function goToStep(step, options = {}) {
    const target = Number(step);
    if (!Number.isFinite(target) || target < 1 || target > ONBOARDING_TOTAL_STEPS) return;

    if (activeStepNavigation) {
        return activeStepNavigation;
    }

    activeStepNavigation = (async () => {
        saveOnboardingToStorage();
        currentStep = target;
        const url = onboardingStepUrl(target);
        try {
            if (options.replace) {
                history.replaceState({ step: target }, '', url);
            } else {
                history.pushState({ step: target }, '', url);
            }
        } catch (e) {
            // ignore history failures
        }
        await loadStep(target);
    })();

    try {
        await activeStepNavigation;
    } finally {
        activeStepNavigation = null;
    }
}
let onboardingData = {
    trust_service_id: null,
    trust_type: '', // service_key from trust_services
    trust_name: '',
    total_estimated_value: '',
    personal_info: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        street: '',
        city: '',
        state: '',
        zip: ''
        // legacy full_name may appear when reading older drafts/trusts
    },
    business_info: {
        entity_type: '', // new | existing
        company_name: '',
        formation_state: '',
        business_ending: ''
    },
    password: '', // Only used when not logged in (registration)
    confirm_password: '', // Only used when not logged in (registration)
    registration_email: '', // Email used for registration (for OTP verification)
    requires_verification: false, // Whether email verification is required
    email_sent: false, // Whether verification email was sent
    beneficiaries: [], // { name, relationship, email, allocation, wallet_address, is_myself }
    payment_method_id: null,
    payment_method: null,
    payment_stage: 'select', // select | details | confirmed
    payment_confirmed: false,
    created_trust_id: null,
    entrusted_coins: [] // coin_key values for Smart Contract Trust
};

let trustServices = [];
let availableCoins = [];
let coinsLoadState = 'idle'; // idle | loading | loaded | error
let coinsLoadError = '';
let coinsLoadPromise = null;
let activeStepNavigation = null;

// Persist onboarding state across page reloads between steps
// IMPORTANT: Only persists during active onboarding session, clears when done or abandoned
const ONBOARDING_STORAGE_KEY = 'wyomingtrust_onboarding_v2';
const ONBOARDING_STORAGE_KEY_LEGACY = 'wyomingtrust_onboarding_v1';
const ONBOARDING_STORAGE_TIMESTAMP_KEY = 'wyomingtrust_onboarding_timestamp';
const ONBOARDING_STORAGE_MAX_AGE = 2 * 60 * 60 * 1000; // 2 hours max age

// Jurisdiction / ending lists come from onboarding-data.js (see US_JURISDICTIONS / US_FORMATIONS bindings above)

function getPersonalFullName(pi) {
    const info = pi || onboardingData.personal_info || {};
    const composed = [info.first_name, info.last_name].filter(Boolean).join(' ').trim();
    if (composed) return composed;
    return (info.full_name || '').toString().trim();
}

function splitLegacyFullName(fullName) {
    const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return { first_name: '', last_name: '' };
    if (parts.length === 1) return { first_name: parts[0], last_name: '' };
    return { first_name: parts[0], last_name: parts.slice(1).join(' ') };
}

function getBusinessEndingLabel(value) {
    const match = BUSINESS_ENDING_OPTIONS.find(o => o.value === value);
    return match ? match.label : '';
}

function getFormationLabel(code) {
    const match = US_FORMATIONS.find(j => j.code === code);
    return match ? match.name : (code || '');
}

function getBusinessEntityTypeLabel(type) {
    if (type === 'new') return 'New Business';
    if (type === 'existing') return 'Existing Business';
    return '';
}

function isValidBusinessEntityType(type) {
    return type === 'new' || type === 'existing';
}

function formatCompanyDisplayName(bi) {
    const info = bi || onboardingData.business_info || {};
    const company = (info.company_name || '').trim();
    if (!company) return '';
    const ending = info.business_ending;
    if (!ending || ending === 'none') return company;
    const label = getBusinessEndingLabel(ending);
    return label ? `${company} ${label}` : company;
}

function isValidBusinessEnding(value) {
    return BUSINESS_ENDING_OPTIONS.some(o => o.value === value);
}

function isValidFormationState(code) {
    return US_FORMATIONS.some(j => j.code === code);
}

function isValidPhone(phone) {
    const digits = String(phone || '').replace(/\D/g, '');
    return digits.length >= 10;
}

function clearOnboardingStorage() {
    try {
        sessionStorage.removeItem(ONBOARDING_STORAGE_KEY);
        sessionStorage.removeItem(ONBOARDING_STORAGE_KEY_LEGACY);
        sessionStorage.removeItem(ONBOARDING_STORAGE_TIMESTAMP_KEY);
    } catch (e) {
        // ignore storage failures
    }
}

function saveOnboardingToStorage() {
    // Only save if we're actively in the onboarding flow (on onboarding.php page)
    if (!window.location.pathname.includes('onboarding.php')) {
        return;
    }
    
    try {
        const dataToSave = {
            ...onboardingData,
            _saved_at: Date.now()
        };
        sessionStorage.setItem(ONBOARDING_STORAGE_KEY, JSON.stringify(dataToSave));
        sessionStorage.setItem(ONBOARDING_STORAGE_TIMESTAMP_KEY, Date.now().toString());
    } catch (e) {
        // ignore storage failures
    }
}

function loadOnboardingFromStorage() {
    // Only load if we're on the onboarding page
    if (!window.location.pathname.includes('onboarding.php')) {
        clearOnboardingStorage();
        return;
    }

    try {
        const timestamp = sessionStorage.getItem(ONBOARDING_STORAGE_TIMESTAMP_KEY);
        if (timestamp) {
            const age = Date.now() - parseInt(timestamp, 10);
            // Clear if data is too old (stale)
            if (age > ONBOARDING_STORAGE_MAX_AGE) {
                clearOnboardingStorage();
                return;
            }
        }

        const raw = sessionStorage.getItem(ONBOARDING_STORAGE_KEY)
            || sessionStorage.getItem(ONBOARDING_STORAGE_KEY_LEGACY);
        if (!raw) return;

        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') {
            clearOnboardingStorage();
            return;
        }

        // Remove internal metadata
        delete parsed._saved_at;

        const mergedPersonal = {
            ...onboardingData.personal_info,
            ...(parsed.personal_info || {}),
        };
        // Legacy drafts only had full_name — map into first/last when missing
        if ((!mergedPersonal.first_name && !mergedPersonal.last_name) && mergedPersonal.full_name) {
            const split = splitLegacyFullName(mergedPersonal.full_name);
            mergedPersonal.first_name = split.first_name;
            mergedPersonal.last_name = split.last_name;
        }

        // Merge conservatively to avoid breaking expected shape
        onboardingData = {
            ...onboardingData,
            ...parsed,
            personal_info: mergedPersonal,
            business_info: {
                ...onboardingData.business_info,
                ...(parsed.business_info || {}),
            },
            beneficiaries: Array.isArray(parsed.beneficiaries) ? parsed.beneficiaries : onboardingData.beneficiaries,
        };
    } catch (e) {
        // If parsing fails, clear corrupted data
        clearOnboardingStorage();
    }
}

async function prefillPersonalInfoFromProfile() {
    if (!isLoggedIn) return;
    try {
        const resp = await fetch('../api/user/profile.php');
        const data = await resp.json();
        if (data && data.success && data.user) {
            const name = (data.user.full_name || '').toString();
            const email = (data.user.email || '').toString();
            if (!onboardingData.personal_info) onboardingData.personal_info = {};
            if (!onboardingData.personal_info.first_name && !onboardingData.personal_info.last_name && name) {
                const split = splitLegacyFullName(name);
                onboardingData.personal_info.first_name = split.first_name;
                onboardingData.personal_info.last_name = split.last_name;
            }
            if (!onboardingData.personal_info.email && email) onboardingData.personal_info.email = email;
            if (!onboardingData.registration_email && email) onboardingData.registration_email = email;
            saveOnboardingToStorage();
        }
    } catch (e) {
        console.warn('Could not prefill profile:', e);
    }
}

// For logged-in users creating another trust: prefill personal info from the most recent trust,
// since the users table typically only stores name/email (address is stored in trust_data).
async function prefillPersonalInfoFromLastTrust() {
    if (!isLoggedIn) return;
    try {
        const resp = await fetch('../api/user/trusts.php');
        const data = await resp.json();
        if (!data || !data.success || !Array.isArray(data.trusts) || data.trusts.length === 0) return;

        const latestWithData = data.trusts.find(t => t && t.trust_data && (t.trust_data.personal_info || t.trust_data.business_info));
        const pi = latestWithData && latestWithData.trust_data ? latestWithData.trust_data.personal_info : null;
        const bi = latestWithData && latestWithData.trust_data ? latestWithData.trust_data.business_info : null;

        if (!onboardingData.personal_info) onboardingData.personal_info = {};
        const dest = onboardingData.personal_info;

        if (pi && typeof pi === 'object') {
            if (!dest.first_name && pi.first_name) dest.first_name = String(pi.first_name);
            if (!dest.last_name && pi.last_name) dest.last_name = String(pi.last_name);
            if (!dest.first_name && !dest.last_name && pi.full_name) {
                const split = splitLegacyFullName(pi.full_name);
                dest.first_name = split.first_name;
                dest.last_name = split.last_name;
            }
            if (!dest.email && pi.email) dest.email = String(pi.email);
            if (!dest.phone && pi.phone) dest.phone = String(pi.phone);
            if (!dest.street && pi.street) dest.street = String(pi.street);
            if (!dest.city && pi.city) dest.city = String(pi.city);
            if (!dest.state && pi.state) dest.state = String(pi.state);
            if (!dest.zip && pi.zip) dest.zip = String(pi.zip);
        }

        if (bi && typeof bi === 'object') {
            if (!onboardingData.business_info) onboardingData.business_info = {};
            const bdest = onboardingData.business_info;
            if (!bdest.company_name && bi.company_name) bdest.company_name = String(bi.company_name);
            if (!bdest.formation_state && bi.formation_state) bdest.formation_state = String(bi.formation_state);
            if (!bdest.business_ending && bi.business_ending) bdest.business_ending = String(bi.business_ending);
        }

        if (!onboardingData.registration_email && dest.email) onboardingData.registration_email = dest.email;
        saveOnboardingToStorage();
    } catch (e) {
        console.warn('Could not prefill from last trust:', e);
    }
}

// Load available trust services from API (all active services from admin)
async function loadTrustServices() {
    try {
        const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        const timeoutId = controller ? setTimeout(() => controller.abort(), 15000) : null;
        const response = await fetch('../api/trust-services.php', controller ? { signal: controller.signal } : undefined);
        if (timeoutId) clearTimeout(timeoutId);
        const data = await response.json();
        if (data.success && data.services) {
            // Defensive normalization (prevents "0" truthiness bugs even if backend changes)
            trustServices = (data.services || [])
                .filter(s => s.service_key !== 'crypto_asset_trust')
                .map(s => ({
                ...s,
                id: Number(s.id),
                price: Number(s.price || 0),
                is_free: Number(s.is_free || 0),
                is_active: Number(s.is_active || 0),
            }));
        }
    } catch (error) {
        console.error('Failed to load trust services:', error);
    }
}

async function loadAvailableCoins(force = false) {
    if (!force && coinsLoadState === 'loaded') {
        return availableCoins;
    }
    if (!force && coinsLoadPromise) {
        return coinsLoadPromise;
    }

    coinsLoadState = 'loading';
    coinsLoadError = '';
    coinsLoadPromise = (async () => {
        try {
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 15000) : null;
            const response = await fetch('../api/coins.php', controller ? { signal: controller.signal } : undefined);
            if (timeoutId) clearTimeout(timeoutId);

            let data = null;
            try {
                data = await response.json();
            } catch (parseErr) {
                throw new Error('Coins API returned an invalid response');
            }

            if (!response.ok || !data || !data.success || !Array.isArray(data.coins)) {
                throw new Error((data && data.message) ? data.message : 'Failed to load cryptocurrencies');
            }

            availableCoins = data.coins;
            coinsLoadState = 'loaded';
            coinsLoadError = '';
            return availableCoins;
        } catch (error) {
            console.error('Failed to load coins:', error);
            availableCoins = [];
            coinsLoadState = 'error';
            coinsLoadError = error && error.name === 'AbortError'
                ? 'Timed out while loading cryptocurrencies.'
                : (error.message || 'Failed to load cryptocurrencies.');
            return availableCoins;
        } finally {
            coinsLoadPromise = null;
        }
    })();

    return coinsLoadPromise;
}

function refreshCoinSelectionPanel() {
    const mount = document.getElementById('coinSelectionPanel');
    if (!mount) return;
    mount.outerHTML = renderCoinSelectionPanel();
}

async function ensureCoinsForSmartContract() {
    if (!isSmartContractTrustSelected()) {
        refreshCoinSelectionPanel();
        return;
    }
    refreshCoinSelectionPanel();
    if (coinsLoadState !== 'loaded') {
        await loadAvailableCoins();
        refreshCoinSelectionPanel();
    }
}

// Get trust service ID from service_key
function getTrustServiceId(serviceKey) {
    const service = trustServices.find(s => s.service_key === serviceKey);
    return service ? service.id : null;
}

// Get selected trust service details
function getSelectedTrustService() {
    const selectedId = Number(onboardingData.trust_service_id);
    if (!Number.isFinite(selectedId) || selectedId <= 0) return null;
    return trustServices.find(s => Number(s.id) === selectedId) || null;
}

function showOnboardingError(message) {
    const container = document.getElementById('onboardingContent');
    if (!container) return;
    container.innerHTML = `
        <div class="text-center py-10 max-w-lg mx-auto">
            <p class="text-red-600 font-semibold mb-2">Something went wrong</p>
            <p class="text-on-surface-variant text-sm mb-6">${escapeHtml(message || 'Please try again.')}</p>
            <button type="button" onclick="location.reload()" class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary font-semibold">Reload</button>
        </div>
    `;
}

async function loadStep(step) {
    const stepData = steps[step - 1];
    if (!stepData) return;

    if (step > ONBOARDING_STEP.BUSINESS_ENTITY && !isValidBusinessEntityType(onboardingData.business_info?.entity_type)) {
        await goToStep(ONBOARDING_STEP.BUSINESS_ENTITY, { replace: true });
        return;
    }
    
    document.getElementById('currentStep').textContent = step;
    document.getElementById('stepTitle').textContent = stepData.title;
    document.getElementById('progressBar').style.width = ((step / ONBOARDING_TOTAL_STEPS) * 100) + '%';
    
    const container = document.getElementById('onboardingContent');
    
    try {
        switch(step) {
            case ONBOARDING_STEP.BUSINESS_ENTITY:
                container.innerHTML = renderBusinessEntityStep();
                break;
            case ONBOARDING_STEP.TRUST_TYPE:
                if (trustServices.length === 0) {
                    await loadTrustServices();
                }
                // Show trust types immediately — do not block on coins
                container.innerHTML = renderTrustTypeStep();
                if (isSmartContractTrustSelected()) {
                    ensureCoinsForSmartContract();
                } else {
                    // Warm the coins cache so Smart Contract selection is instant
                    loadAvailableCoins().catch(() => {});
                }
                break;
            case ONBOARDING_STEP.PERSONAL_INFO:
                // Never block the form on profile/trust prefills
                if (isLoggedIn) {
                    await withTimeout(Promise.all([
                        prefillPersonalInfoFromProfile(),
                        prefillPersonalInfoFromLastTrust()
                    ]), 2500);
                }
                container.innerHTML = renderPersonalInfoStep();
                break;
            case ONBOARDING_STEP.BENEFICIARIES:
                // Check if user needs email verification before showing beneficiaries step
                if (isLoggedIn) {
                    try {
                        const verificationCheck = withTimeout((async () => {
                            const response = await fetch('../api/user/profile.php?check_verification=true');
                            return response.json();
                        })(), 2500);
                        const data = await verificationCheck;
                        if (data && data.success && data.requires_verification && !data.email_verified) {
                            showOTPVerificationStep();
                            return;
                        }
                    } catch (error) {
                        console.error('Error checking verification status:', error);
                    }
                }
                ensureDefaultBeneficiary();
                if (!isSmartContractTrustSelected()) {
                    onboardingData.beneficiaries.forEach(b => { b.wallet_address = ''; });
                }
                container.innerHTML = renderBeneficiariesStep();
                setupBeneficiariesStep();
                break;
            case ONBOARDING_STEP.REVIEW:
                container.innerHTML = renderReviewStep();
                break;
        }
    } catch (error) {
        console.error('Failed to load onboarding step:', error);
        showOnboardingError(error && error.message ? error.message : 'Failed to load this step.');
    }
}


function getTrustServiceIcon(serviceKey) {
    const icons = {
        revocable_living_trust: 'edit',
        irrevocable_trust: 'lock',
        smart_contract_trust: 'wallet',
        crypto_asset_trust: 'wallet',
        trust_llc: 'business',
    };
    return icons[serviceKey] || 'account_balance';
}

function getTrustTypeLabel(serviceKey) {
    const labels = {
        irrevocable_trust: 'Irrevocable Trust Service',
        revocable_living_trust: 'Revocable Living Trust',
        smart_contract_trust: 'Smart Contract Trust Service',
        crypto_asset_trust: 'Cryptocurrency Asset Trust',
        trust_llc: 'Trust LLC',
    };
    return labels[serviceKey] || serviceKey.replace(/_/g, ' ');
}

function formatTrustServicePrice(service) {
    const price = Number(service.price || 0);
    const isFree = Number(service.is_free) === 1 || price <= 0;
    return {
        isFree,
        label: isFree ? 'FREE' : `$${price.toFixed(2)}`,
    };
}

function renderBusinessEntityStep() {
    const selected = onboardingData.business_info?.entity_type || '';
    const options = [
        {
            value: 'new',
            label: 'New Business',
            description: 'You are forming a new company or entity as part of this LLC setup.',
            icon: 'add-circle',
        },
        {
            value: 'existing',
            label: 'Existing Business',
            description: 'You already have a registered business and want to place it under an LLC structure.',
            icon: 'history',
        },
    ];

    const cards = options.map(option => {
        const isSelected = selected === option.value;
        return `
            <label class="relative border-2 ${isSelected ? 'border-secondary' : 'border-outline-variant/30'} rounded-xl p-4 sm:p-5 cursor-pointer hover:border-secondary transition-all group flex gap-3 sm:gap-4 items-start">
                <input class="peer sr-only" name="business_entity_type" type="radio" value="${escapeHtml(option.value)}" ${isSelected ? 'checked' : ''} onchange="selectBusinessEntityType('${escapeHtml(option.value)}')"/>
                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-secondary rounded-lg flex items-center justify-center text-on-secondary shrink-0">
                    ${wtIcon(option.icon, 'text-lg sm:text-xl')}
                </div>
                <div class="flex-1 min-w-0 pr-8">
                    <h3 class="text-base sm:text-lg font-bold text-primary mb-1">${escapeHtml(option.label)}</h3>
                    <p class="text-on-surface-variant text-xs sm:text-sm leading-relaxed">${escapeHtml(option.description)}</p>
                </div>
                <div class="absolute top-4 right-4 w-5 h-5 rounded-full border-2 ${isSelected ? 'border-secondary bg-secondary' : 'border-outline-variant'} transition-colors shrink-0"></div>
            </label>
        `;
    }).join('');

    return `
        <div class="max-w-2xl mx-auto w-full px-1">
            <div class="text-center mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-bold text-primary mb-2">Is This a New or Existing Business?</h1>
                <p class="text-on-surface-variant text-sm sm:text-base">Choose the option that best describes your situation before selecting an LLC structure.</p>
            </div>
            <div class="flex flex-col gap-3 sm:gap-4 mb-8">
                ${cards}
            </div>
            <div class="fixed bottom-0 left-0 w-full bg-surface-container-lowest border-t border-outline-variant/30 py-4 px-8 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="max-w-container-max mx-auto flex justify-between items-center">
                    <button onclick="handleCancelOrExit(); window.location.href='../index.php'" class="px-6 py-2 text-on-surface-variant hover:text-primary">Cancel</button>
                    <button onclick="validateBusinessEntityStepAndNext()" ${selected ? '' : 'disabled'} id="nextBtn" class="bg-secondary text-on-secondary hover:opacity-90 font-semibold py-3 px-8 rounded-lg flex items-center shadow-lg transform transition hover:-translate-y-0.5 focus:ring-4 focus:ring-secondary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                        <?php echo wt_icon('arrow-forward', 'ml-2 text-lg'); ?>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function selectBusinessEntityType(type) {
    if (!onboardingData.business_info) onboardingData.business_info = {};
    onboardingData.business_info.entity_type = type;
    saveOnboardingToStorage();

    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) nextBtn.disabled = false;

    const container = document.getElementById('onboardingContent');
    if (container) {
        container.innerHTML = renderBusinessEntityStep();
    }
}

function validateBusinessEntityStepAndNext() {
    const type = onboardingData.business_info?.entity_type || '';
    if (!isValidBusinessEntityType(type)) {
        alert('Please select whether this is a new business or an existing business.');
        return;
    }
    saveOnboardingToStorage();
    void nextStep();
}

function renderTrustTypeStep() {
    const selectedServiceId = Number(onboardingData.trust_service_id) || 0;

    const serviceCards = trustServices.length > 0
        ? trustServices.map(service => {
            const isSelected = selectedServiceId > 0 && Number(service.id) === selectedServiceId;
            const { isFree, label } = formatTrustServicePrice(service);
            const title = escapeHtml(service.service_name || service.service_key);
            const categoryLabel = escapeHtml(getTrustTypeLabel(service.service_key));
            const description = escapeHtml(service.description || 'Select this LLC structure to continue.');
            const icon = getTrustServiceIcon(service.service_key);
            return `
                <label class="relative border-2 ${isSelected ? 'border-secondary' : 'border-outline-variant/30'} rounded-xl p-4 sm:p-5 cursor-pointer hover:border-secondary transition-all group flex gap-3 sm:gap-4 items-start">
                    <input class="peer sr-only" name="trust_service_id" type="radio" value="${Number(service.id)}" ${isSelected ? 'checked' : ''} onchange="selectTrustType('${escapeHtml(service.service_key)}', ${Number(service.id)})"/>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 bg-secondary rounded-lg flex items-center justify-center text-on-secondary shrink-0">
                        ${wtIcon(icon, 'text-lg sm:text-xl')}
                    </div>
                    <div class="flex-1 min-w-0 pr-8">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-base sm:text-lg font-bold text-primary">${title}</h3>
                            <span class="text-xs font-bold ${isFree ? 'text-green-600' : 'text-secondary'}">${label}</span>
                        </div>
                        <p class="text-xs text-on-surface-variant mb-1">${categoryLabel}</p>
                        <p class="text-on-surface-variant text-xs sm:text-sm leading-relaxed line-clamp-2 sm:line-clamp-none">${description}</p>
                    </div>
                    <div class="absolute top-4 right-4 w-5 h-5 rounded-full border-2 ${isSelected ? 'border-secondary bg-secondary' : 'border-outline-variant'} transition-colors shrink-0"></div>
                </label>
            `;
        }).join('')
        : `
            <div class="col-span-full text-center py-12 text-on-surface-variant">
                ${wtIcon('info', 'text-4xl mb-3 block')}
                <p>No LLC structures are available right now. Please try again later or contact support.</p>
            </div>
        `;

    return `
        <div class="max-w-2xl mx-auto w-full px-1">
            <div class="text-center mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-bold text-primary mb-2">Choose Your LLC Structure</h1>
                <p class="text-on-surface-variant text-sm sm:text-base">Select the structure that best fits your LLC needs</p>
            </div>
            <div class="flex flex-col gap-3 sm:gap-4 mb-8">
                ${serviceCards}
            </div>
            ${renderCoinSelectionPanel()}
            <div class="bg-secondary-fixed rounded-xl p-4 flex items-start space-x-3 text-on-secondary-fixed-variant border border-secondary/20">
                <?php echo wt_icon('info', 'text-xl mt-0.5 flex-shrink-0 text-secondary'); ?>
                <p class="text-sm leading-relaxed">
                    <span class="font-bold">Not sure?</span> You can change your selection later. Our system will guide you based on your specific needs.
                </p>
            </div>
            <div class="fixed bottom-0 left-0 w-full bg-surface-container-lowest border-t border-outline-variant/30 py-4 px-8 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="max-w-container-max mx-auto flex justify-between items-center">
                    <button onclick="previousStep()" class="px-6 py-2 text-on-surface-variant hover:text-primary">Previous</button>
                    <button onclick="validateTrustTypeStepAndNext()" ${onboardingData.trust_service_id ? '' : 'disabled'} id="nextBtn" class="bg-secondary text-on-secondary hover:opacity-90 font-semibold py-3 px-8 rounded-lg flex items-center shadow-lg transform transition hover:-translate-y-0.5 focus:ring-4 focus:ring-secondary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                        <?php echo wt_icon('arrow-forward', 'ml-2 text-lg'); ?>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderPersonalInfoStep() {
    const pi = onboardingData.personal_info || {};
    const bi = onboardingData.business_info || {};
    const inputClass = 'form-input flex w-full rounded-lg text-primary border border-outline-variant bg-surface-container-low focus:ring-2 focus:ring-secondary focus:border-secondary h-14 placeholder:text-on-surface-variant p-4 text-base font-normal';
    const inputClassSm = 'form-input flex w-full rounded-lg text-primary border border-outline-variant bg-surface-container-low focus:ring-2 focus:ring-secondary focus:border-secondary h-12 placeholder:text-on-surface-variant p-4 text-base font-normal';
    const jurisdictionOptions = US_FORMATIONS.map(j =>
        `<option value="${escapeHtml(j.code)}" ${bi.formation_state === j.code ? 'selected' : ''}>${escapeHtml(j.name)}</option>`
    ).join('');
    const endingOptions = BUSINESS_ENDING_OPTIONS.map(o =>
        `<option value="${escapeHtml(o.value)}" ${bi.business_ending === o.value ? 'selected' : ''}>${escapeHtml(o.label)}</option>`
    ).join('');

    return `
        <div class="max-w-container-max mx-auto">
            <div class="mb-10">
                <div class="flex flex-col gap-3 max-w-[960px] mx-auto">
                    <div class="flex gap-6 justify-between">
                        <p class="text-primary text-base font-semibold leading-normal">Step 3 of 5: Business &amp; Personal Info</p>
                        <p class="text-primary text-sm font-medium leading-normal">60% Complete</p>
                    </div>
                    <div class="rounded-full bg-surface-container h-2 overflow-hidden">
                        <div class="h-full rounded-full bg-secondary transition-all duration-500" style="width: 60%;"></div>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-on-surface-variant text-sm font-normal leading-normal italic">Next: Beneficiaries</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row gap-10 items-start">
                <div class="flex-1 w-full max-w-[700px] space-y-8">
                    <div class="mb-2">
                        <h1 class="text-primary text-4xl font-black leading-tight tracking-tight mb-3">Business &amp; Personal Details</h1>
                        <p class="text-on-surface-variant text-lg font-normal leading-relaxed">Provide your business details and personal contact information separately.${!isLoggedIn ? ' You will also create your account here.' : ''}</p>
                    </div>

                    <form class="space-y-8" onsubmit="event.preventDefault(); savePersonalInfo();">
                        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-8 shadow-sm space-y-6">
                            <div>
                                <h2 class="text-primary text-2xl font-bold tracking-tight">Business Information</h2>
                                <p class="text-on-surface-variant text-sm mt-1">${escapeHtml(getBusinessEntityTypeLabel(onboardingData.business_info?.entity_type) || 'Business details')} — these details belong to the business entity, not the individual.</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="trustNameInput">LLC Name</label>
                                <input type="text" id="trustNameInput" value="${escapeHtml(onboardingData.trust_name || '')}" placeholder="Smith Family Holdings LLC" autocomplete="off" class="${inputClass}" required/>
                                <p class="text-xs text-on-surface-variant">A descriptive name for this LLC. You can change it later from your dashboard.</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="companyNameInput">Company Name</label>
                                <input type="text" id="companyNameInput" value="${escapeHtml(bi.company_name || '')}" placeholder="Carter Holdings" autocomplete="organization" class="${inputClass}" required/>
                                <p class="text-xs text-on-surface-variant">Legal company name without the ending (LLC, Inc, etc.). Ending is selected separately below.</p>
                            </div>
                            ${isCatalogTrustSelected() ? `
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="totalAssetValueInput">Total Asset Value (USD)</label>
                                <input type="number" id="totalAssetValueInput" value="${onboardingData.total_estimated_value != null && onboardingData.total_estimated_value !== '' ? escapeHtml(String(onboardingData.total_estimated_value)) : ''}" min="0" step="0.01" placeholder="500000" autocomplete="off" class="${inputClass}" required/>
                                <p class="text-xs text-on-surface-variant">Estimated total value of assets you plan to place in this LLC.</p>
                            </div>
                            ` : ''}
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="formationStateInput">Formation State / Jurisdiction</label>
                                <select id="formationStateInput" class="${inputClass}" required>
                                    <option value="" ${!bi.formation_state ? 'selected' : ''} disabled>Select a jurisdiction</option>
                                    ${jurisdictionOptions}
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="businessEndingInput">Business Ending</label>
                                <select id="businessEndingInput" class="${inputClass}" required>
                                    <option value="" ${!bi.business_ending ? 'selected' : ''} disabled>Select a business ending</option>
                                    ${endingOptions}
                                </select>
                                <p class="text-xs text-on-surface-variant">Stored separately from company name. Prefer no ending is a valid choice.</p>
                            </div>
                        </div>

                        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-8 shadow-sm space-y-6">
                            <div>
                                <h2 class="text-primary text-2xl font-bold tracking-tight">Personal Information</h2>
                                <p class="text-on-surface-variant text-sm mt-1">These details belong to you as an individual.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="firstNameInput">First Name</label>
                                    <input type="text" id="firstNameInput" value="${escapeHtml(pi.first_name || '')}" placeholder="John" autocomplete="given-name" class="${inputClass}" required/>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="lastNameInput">Last Name</label>
                                    <input type="text" id="lastNameInput" value="${escapeHtml(pi.last_name || '')}" placeholder="Public" autocomplete="family-name" class="${inputClass}" required/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="emailInput">Email</label>
                                <input type="email" id="emailInput" value="${escapeHtml(pi.email || '')}" placeholder="john@example.com" autocomplete="email" class="${inputClass}" required/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="phoneInput">Phone Number</label>
                                <input type="tel" id="phoneInput" value="${escapeHtml(pi.phone || '')}" placeholder="+1 (555) 123-4567" autocomplete="tel" class="${inputClass}" required/>
                            </div>
                            ${!isLoggedIn ? `
                            <div class="border-t border-outline-variant/30 pt-6 space-y-4">
                                <h3 class="text-primary text-lg font-bold mb-4">Create Your Account</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="passwordInput">Password</label>
                                    <div class="relative">
                                        <input type="password" id="passwordInput" placeholder="Enter your password" autocomplete="new-password" class="${inputClass} pr-12" required/>
                                        <button type="button" onclick="togglePasswordVisibility('passwordInput', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary focus:outline-none" aria-label="Toggle password visibility">
                                            <?php echo wt_icon('visibility-off', 'text-xl toggle-password-icon'); ?>
                                        </button>
                                    </div>
                                    <p class="text-xs text-on-surface-variant">Must be at least 8 characters with uppercase, lowercase, number, and special character.</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-on-surface-variant text-sm font-semibold leading-normal" for="confirmPasswordInput">Confirm Password</label>
                                    <div class="relative">
                                        <input type="password" id="confirmPasswordInput" placeholder="Confirm your password" autocomplete="new-password" class="${inputClass} pr-12" required/>
                                        <button type="button" onclick="togglePasswordVisibility('confirmPasswordInput', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary focus:outline-none" aria-label="Toggle password visibility">
                                            <?php echo wt_icon('visibility-off', 'text-xl toggle-password-icon'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            <div class="space-y-4 pt-4 border-t border-outline-variant/20">
                                <label class="text-on-surface-variant text-sm font-semibold leading-normal">Contact Address</label>
                                <div class="flex flex-col gap-2">
                                    <label class="sr-only" for="streetInput">Street Address</label>
                                    <input type="text" id="streetInput" value="${escapeHtml(pi.street || '')}" placeholder="Street Address" autocomplete="street-address" class="${inputClassSm}" required/>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input type="text" id="cityInput" value="${escapeHtml(pi.city || '')}" placeholder="City" autocomplete="address-level2" class="${inputClassSm}" required/>
                                    <input type="text" id="stateInput" value="${escapeHtml(pi.state || '')}" placeholder="State / Province" autocomplete="address-level1" class="${inputClassSm}" required/>
                                    <input type="text" id="zipInput" value="${escapeHtml(pi.zip || '')}" placeholder="ZIP / Postal Code" autocomplete="postal-code" class="${inputClassSm}" required/>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="button" id="continueToBeneficiariesBtn" onclick="savePersonalInfoAndContinue();" class="flex min-w-[180px] cursor-pointer items-center justify-center rounded-lg h-14 px-6 bg-secondary text-on-secondary hover:opacity-90 transition-all text-base font-bold shadow-md shadow-secondary/20 disabled:opacity-60 disabled:cursor-not-allowed">
                                <span>Continue to Beneficiaries</span>
                                <?php echo wt_icon('arrow-forward', 'ml-2 text-sm'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                <aside class="w-full lg:w-[320px] sticky top-24">
                    <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-6 shadow-sm overflow-hidden relative">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>
                        <div class="relative z-10 flex flex-col gap-6">
                            <div>
                                <h3 class="text-primary text-lg font-bold flex items-center gap-2">
                                    <?php echo wt_icon('help', 'text-secondary'); ?>
                                    LLC Tips
                                </h3>
                                <p class="text-on-surface-variant text-sm font-medium mt-1">Why Choose Wyoming?</p>
                            </div>
                            <div class="flex flex-col gap-4">
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-secondary/5 border border-secondary/10">
                                    <?php echo wt_icon('shield', 'text-secondary text-xl'); ?>
                                    <div>
                                        <p class="text-primary text-sm font-bold">Asset Protection</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Wyoming offers some of the strongest statutory protections for LLC assets in the USA.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                                    <?php echo wt_icon('lock', 'text-on-surface-variant text-xl'); ?>
                                    <div>
                                        <p class="text-primary text-sm font-bold">Privacy Protection</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Beneficiary and grantor names are not public record in Wyoming filings.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                                    <?php echo wt_icon('payments', 'text-on-surface-variant text-xl'); ?>
                                    <div>
                                        <p class="text-primary text-sm font-bold">Tax Advantages</p>
                                        <p class="text-xs text-on-surface-variant mt-1">0% state income tax for non-resident LLC owners.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
                                    <?php echo wt_icon('wallet', 'text-on-surface-variant text-xl'); ?>
                                    <div>
                                        <p class="text-primary text-sm font-bold">Crypto-Friendly</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Explicit laws recognizing digital assets as legal property within Wyoming LLCs.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
            <div class="mt-8 flex justify-between">
                <button onclick="previousStep()" class="px-6 py-2 text-on-surface-variant hover:text-primary">Previous</button>
            </div>
        </div>
    `;
}

function isSmartContractTrustSelected() {
    const service = getSelectedTrustService();
    if (service) {
        if (service.is_crypto || service.service_key === 'smart_contract_trust' || service.service_key === 'crypto_asset_trust') {
            return true;
        }
    }
    const key = onboardingData.trust_type || '';
    return key === 'smart_contract_trust' || key === 'crypto_asset_trust';
}

function isCatalogTrustSelected() {
    const service = getSelectedTrustService();
    const key = service?.service_key || onboardingData.trust_type || '';
    return key === 'revocable_living_trust' || key === 'irrevocable_trust';
}

function renderCoinSelectionPanel() {
    if (!isSmartContractTrustSelected()) {
        return `<div id="coinSelectionPanel" class="hidden"></div>`;
    }
    const selected = onboardingData.entrusted_coins || [];

    if (coinsLoadState === 'loading' || coinsLoadState === 'idle') {
        return `
            <div id="coinSelectionPanel" class="mb-10 p-6 border border-outline-variant/30 rounded-2xl bg-surface-container-low">
                <div class="flex items-center gap-3 text-on-surface-variant text-sm">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-secondary border-t-transparent"></div>
                    <p>Loading available cryptocurrencies...</p>
                </div>
            </div>
        `;
    }

    if (coinsLoadState === 'error' || !availableCoins.length) {
        return `
            <div id="coinSelectionPanel" class="mb-10 p-6 border border-red-200 rounded-2xl bg-red-50">
                <p class="text-red-700 text-sm font-semibold mb-2">Could not load cryptocurrencies</p>
                <p class="text-red-600/80 text-xs mb-4">${escapeHtml(coinsLoadError || 'No coins are available right now.')}</p>
                <button type="button" onclick="retryLoadCoins()" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-sm font-semibold">
                    Retry
                </button>
            </div>
        `;
    }

    const coinCards = availableCoins.map(coin => {
        const coinKey = String(coin.coin_key || '');
        const isChecked = selected.includes(coinKey);
        const logo = coin.logo ? `<img src="${escapeHtml(String(coin.logo))}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0" onerror="this.style.display='none'">` : `<span class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-xs font-bold shrink-0">${escapeHtml(String((coin.symbol || '?')).slice(0, 3))}</span>`;
        return `
            <label class="flex items-center gap-3 p-4 border-2 ${isChecked ? 'border-secondary bg-secondary/5' : 'border-outline-variant/30'} rounded-xl cursor-pointer hover:border-secondary transition-all">
                <input type="checkbox" class="rounded text-secondary focus:ring-secondary" ${isChecked ? 'checked' : ''} onchange="toggleEntrustedCoin('${escapeHtml(coinKey)}', this.checked)"/>
                ${logo}
                <span class="min-w-0">
                    <span class="block font-bold text-primary text-sm truncate">${escapeHtml(String(coin.display_name || coinKey))}</span>
                    <span class="block text-xs text-on-surface-variant">${escapeHtml(String(coin.symbol || coinKey.toUpperCase()))}</span>
                </span>
            </label>
        `;
    }).join('');

    return `
        <div id="coinSelectionPanel" class="mb-10 p-6 lg:p-8 border-2 border-secondary/20 rounded-2xl bg-surface-container-low">
            <h2 class="text-xl font-bold text-primary mb-2">Select Cryptocurrencies to Entrust</h2>
            <p class="text-on-surface-variant text-sm mb-6">Choose which digital assets this LLC structure will hold. After your LLC is created, you can deposit crypto and track balances from your dashboard.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                ${coinCards}
            </div>
            <p class="text-xs text-on-surface-variant mt-4">
                Selected: <strong id="coinSelectionCount">${selected.length}</strong> coin${selected.length === 1 ? '' : 's'}
                ${selected.length === 0 ? '<span class="text-red-600"> — select at least one</span>' : ''}
            </p>
        </div>
    `;
}

async function retryLoadCoins() {
    await loadAvailableCoins(true);
    refreshCoinSelectionPanel();
}

function toggleEntrustedCoin(coinKey, checked) {
    if (!Array.isArray(onboardingData.entrusted_coins)) {
        onboardingData.entrusted_coins = [];
    }
    if (checked) {
        if (!onboardingData.entrusted_coins.includes(coinKey)) {
            onboardingData.entrusted_coins.push(coinKey);
        }
    } else {
        onboardingData.entrusted_coins = onboardingData.entrusted_coins.filter(k => k !== coinKey);
    }
    const countEl = document.getElementById('coinSelectionCount');
    if (countEl) {
        countEl.textContent = String(onboardingData.entrusted_coins.length);
    }
    saveOnboardingToStorage();
}

function validateTrustTypeStepAndNext() {
    if (!onboardingData.trust_service_id) {
        alert('Please select an LLC structure.');
        return;
    }
    if (isSmartContractTrustSelected()) {
        if (coinsLoadState === 'loading' || coinsLoadState === 'idle') {
            alert('Please wait for cryptocurrencies to finish loading, then select at least one.');
            ensureCoinsForSmartContract();
            return;
        }
        if (coinsLoadState === 'error' || !availableCoins.length) {
            alert('Cryptocurrencies could not be loaded. Please tap Retry and try again.');
            return;
        }
        if (!onboardingData.entrusted_coins || onboardingData.entrusted_coins.length === 0) {
            alert('Please select at least one cryptocurrency to entrust in this Smart Contract Trust.');
            return;
        }
    } else {
        onboardingData.entrusted_coins = [];
    }
    saveOnboardingToStorage();
    void nextStep();
}

function shouldHideExtraBeneficiaries() {
    const myself = onboardingData.beneficiaries.find(b => b.is_myself);
    if (!myself) return false;
    const alloc = parseFloat(myself.allocation);
    if (Number.isNaN(alloc)) return false;
    return Math.abs(alloc - 100) < 0.01;
}

function syncBeneficiaryVisibility() {
    const hideExtras = shouldHideExtraBeneficiaries();

    if (hideExtras) {
        onboardingData.beneficiaries.forEach(b => {
            if (!b.is_myself) {
                b.allocation = 0;
            }
        });
        saveOnboardingToStorage();
    }

    document.querySelectorAll('.beneficiary-card[data-beneficiary-extra="1"]').forEach(el => {
        el.classList.toggle('hidden', hideExtras);
    });
    const addBtn = document.getElementById('addBeneficiaryBtn');
    if (addBtn) {
        addBtn.classList.toggle('hidden', hideExtras);
    }

    if (hideExtras) {
        updateAllocationDisplay();
    }
}

function getActiveBeneficiaries() {
    if (shouldHideExtraBeneficiaries()) {
        return onboardingData.beneficiaries.filter(b => b.is_myself);
    }
    return onboardingData.beneficiaries.slice();
}

function renderBeneficiariesStep() {
    const showCryptoWallet = isSmartContractTrustSelected();
    const hideExtras = shouldHideExtraBeneficiaries();
    const activeBeneficiaries = getActiveBeneficiaries();
    const totalAllocation = activeBeneficiaries.reduce((sum, ben) => sum + (parseFloat(ben.allocation) || 0), 0);
    const isValid = Math.abs(totalAllocation - 100) < 0.01;
    const hasMyself = onboardingData.beneficiaries.some(b => b.is_myself);
    
    return `
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <h1 class="text-primary text-4xl font-black leading-tight tracking-tight mb-3">Add Beneficiaries</h1>
                <p class="text-on-surface-variant text-lg font-normal leading-relaxed">Who should receive assets from this LLC?</p>
            </div>
            
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-8 shadow-sm mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <input type="checkbox" id="addMyselfCheckbox" ${hasMyself ? 'checked' : ''} class="w-5 h-5 text-secondary border-outline-variant rounded focus:ring-secondary cursor-pointer"/>
                    <label for="addMyselfCheckbox" class="text-primary font-semibold text-lg cursor-pointer">Add Myself as Beneficiary</label>
                </div>
                <p class="text-sm text-on-surface-variant mb-6">Include yourself in the beneficiary list</p>
            </div>
            
            <div id="beneficiariesList" class="space-y-4 mb-6">
                ${onboardingData.beneficiaries.length === 0 ? `
                    <div class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-xl p-8 text-center">
                        <p class="text-on-surface-variant mb-4">No beneficiaries added yet.</p>
                        <p class="text-sm text-on-surface-variant">Check "Add Myself as Beneficiary" above or click "Add Another Beneficiary" below to get started.</p>
                    </div>
                ` : ''}
                ${onboardingData.beneficiaries.map((ben, idx) => `
                    <div class="beneficiary-card bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-6 shadow-sm${!ben.is_myself && hideExtras ? ' hidden' : ''}" data-beneficiary-extra="${ben.is_myself ? '0' : '1'}">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-primary">Beneficiary #${idx + 1}${ben.is_myself ? ' <span class="text-sm text-secondary">(Myself)</span>' : ''}</h3>
                            ${ben.is_myself ? '' : `<button onclick="removeBeneficiary(${idx})" class="text-red-600 hover:text-red-700 font-medium">Remove</button>`}
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Primary Full Name *</label>
                                <input type="text" value="${escapeHtml(ben.name || '')}" onchange="updateBeneficiary(${idx}, 'name', this.value)" ${ben.is_myself ? 'readonly' : ''} placeholder="${ben.is_myself ? 'Your name' : 'Jane Doe'}" autocomplete="off" class="w-full px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary ${ben.is_myself ? 'bg-surface-container cursor-not-allowed' : ''}" required/>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Relationship *</label>
                                <select onchange="updateBeneficiary(${idx}, 'relationship', this.value)" ${ben.is_myself ? 'disabled' : ''} class="w-full px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary ${ben.is_myself ? 'bg-surface-container cursor-not-allowed' : ''}" required>
                                    <option value="">Select</option>
                                    <option value="Self" ${ben.relationship === 'Self' ? 'selected' : ''}>Self</option>
                                    <option value="Spouse" ${ben.relationship === 'Spouse' ? 'selected' : ''}>Spouse</option>
                                    <option value="Child" ${ben.relationship === 'Child' ? 'selected' : ''}>Child</option>
                                    <option value="Parent" ${ben.relationship === 'Parent' ? 'selected' : ''}>Parent</option>
                                    <option value="Sibling" ${ben.relationship === 'Sibling' ? 'selected' : ''}>Sibling</option>
                                    <option value="Other" ${ben.relationship === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Email</label>
                                <input type="email" value="${escapeHtml(ben.email || '')}" onchange="updateBeneficiary(${idx}, 'email', this.value)" ${ben.is_myself ? 'readonly' : ''} placeholder="${ben.is_myself ? 'Your email' : 'jane@example.com'}" autocomplete="off" class="w-full px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary ${ben.is_myself ? 'bg-surface-container cursor-not-allowed' : ''}"/>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Allocation % *</label>
                                <input type="number" id="allocation_${idx}" data-index="${idx}" min="0" max="100" step="0.01" value="${ben.allocation || ''}" onchange="updateBeneficiary(${idx}, 'allocation', this.value)" oninput="updateBeneficiary(${idx}, 'allocation', this.value)" placeholder="50" class="w-full px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary" required/>
                            </div>
                            ${showCryptoWallet ? `
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Crypto Wallet Address (Optional)</label>
                                <input type="text" value="${escapeHtml(ben.wallet_address || '')}" onchange="updateBeneficiary(${idx}, 'wallet_address', this.value)" placeholder="0x..." class="w-full px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary"/>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <button id="addBeneficiaryBtn" onclick="addNewBeneficiary()" class="w-full py-3 border-2 border-dashed border-outline-variant rounded-lg text-on-surface-variant hover:border-secondary hover:text-primary transition-colors mb-6${hideExtras ? ' hidden' : ''}">
                + Add Another Beneficiary
            </button>
            
            <div class="bg-secondary-fixed rounded-xl p-4 border border-secondary/20 mb-6">
                <p class="text-sm text-on-secondary-fixed-variant">
                    <span class="font-bold">Note:</span> Total allocation must equal 100%. 
                    <span id="allocationStatus" class="font-semibold ${isValid ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
                        Current total: ${totalAllocation.toFixed(2)}%
                    </span>
                </p>
            </div>
            
            <div class="mt-8 flex justify-between">
                <button onclick="previousStep()" class="px-6 py-2 text-on-surface-variant hover:text-primary">Previous</button>
                <button onclick="validateAndNext()" ${isValid ? '' : 'disabled'} class="px-6 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next: Review & Payment
                </button>
            </div>
        </div>
    `;
}


function renderReviewStep() {
    const selectedService = getSelectedTrustService();
    const isFreeFlag = selectedService ? (Number(selectedService.is_free) === 1) : true;
    const priceVal = selectedService ? Number(selectedService.price || 0) : 0;
    const price = selectedService ? (isFreeFlag ? 0.00 : priceVal) : 0.00;
    const isFree = !selectedService || isFreeFlag || price <= 0;
    const paymentStage = onboardingData.payment_stage || 'select';
    const pi = onboardingData.personal_info || {};
    const serviceName = selectedService ? selectedService.service_name : 'Not selected';
    const trustTypeName = serviceName;
    
    // Load payment methods only when needed (paid services)
    if (currentStep === ONBOARDING_STEP.REVIEW && !isFree) {
        setTimeout(() => loadPaymentMethods(), 100);
    }

    return `
        <div class="w-full">
            <div class="flex flex-col gap-2 mb-6">
                <h1 class="text-primary text-4xl font-black leading-tight tracking-tight">Final Step: ${isFree ? 'Review & Register Your LLC' : 'Review & Secure Your LLC'}</h1>
                <p class="text-on-surface-variant text-lg font-normal leading-normal">${isFree ? 'Confirm your details, then register your LLC.' : 'Confirm your details and choose a payment method to finalize your LLC.'}</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
                <div class="lg:col-span-3 flex flex-col gap-6">
                    <div class="flex flex-col gap-4">
                        <h2 class="text-primary text-xl font-bold flex items-center gap-2">
                            <?php echo wt_icon('shield', 'text-secondary'); ?>
                            Review Your Information
                        </h2>
                        <details class="group rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-sm" open>
                            <summary class="flex cursor-pointer items-center justify-between p-5 list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-primary">
                                        <?php echo wt_icon('payments', 'text-xl'); ?>
                                    </div>
                                    <p class="text-primary font-semibold">Business Information</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="button" onclick="window.location.href='?step=3'" class="text-secondary text-sm font-bold hover:underline">Edit</button>
                                    <?php echo wt_icon('chevron-down', 'text-on-surface-variant group-open:rotate-180 transition-transform'); ?>
                                </div>
                            </summary>
                            <div class="px-5 pb-5 pt-0 border-t border-outline-variant/20 mt-2">
                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Business Type</p>
                                        <p class="text-on-background font-medium">${escapeHtml(getBusinessEntityTypeLabel(onboardingData.business_info?.entity_type) || 'Not provided')}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">LLC Name</p>
                                        <p class="text-on-background font-medium">${escapeHtml(onboardingData.trust_name || 'Not provided')}</p>
                                    </div>
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Company Name</p>
                                        <p class="text-on-background font-medium">${escapeHtml((onboardingData.business_info && onboardingData.business_info.company_name) || 'Not provided')}</p>
                                    </div>
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Business Ending</p>
                                        <p class="text-on-background font-medium">${escapeHtml(getBusinessEndingLabel((onboardingData.business_info && onboardingData.business_info.business_ending) || '') || 'Not provided')}</p>
                                    </div>
                                    ${formatCompanyDisplayName() ? `
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Display Name</p>
                                        <p class="text-on-background font-medium">${escapeHtml(formatCompanyDisplayName())}</p>
                                    </div>
                                    ` : ''}
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Formation State / Jurisdiction</p>
                                        <p class="text-on-background font-medium">${escapeHtml(getFormationLabel((onboardingData.business_info && onboardingData.business_info.formation_state) || '') || 'Not provided')}</p>
                                    </div>
                                    ${isCatalogTrustSelected() ? `
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Total Asset Value</p>
                                        <p class="text-on-background font-medium">${onboardingData.total_estimated_value != null && onboardingData.total_estimated_value !== '' ? '$' + Number(onboardingData.total_estimated_value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'Not provided'}</p>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </details>
                        <details class="group rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-sm" open>
                            <summary class="flex cursor-pointer items-center justify-between p-5 list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-primary">
                                        <?php echo wt_icon('person', 'text-xl'); ?>
                                    </div>
                                    <p class="text-primary font-semibold">Personal Information</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="button" onclick="window.location.href='?step=3'" class="text-secondary text-sm font-bold hover:underline">Edit</button>
                                    <?php echo wt_icon('chevron-down', 'text-on-surface-variant group-open:rotate-180 transition-transform'); ?>
                                </div>
                            </summary>
                            <div class="px-5 pb-5 pt-0 border-t border-outline-variant/20 mt-2">
                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">First Name</p>
                                        <p class="text-on-background font-medium">${escapeHtml(pi.first_name || (pi.full_name ? splitLegacyFullName(pi.full_name).first_name : '') || 'Not provided')}</p>
                                    </div>
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Last Name</p>
                                        <p class="text-on-background font-medium">${escapeHtml(pi.last_name || (pi.full_name ? splitLegacyFullName(pi.full_name).last_name : '') || 'Not provided')}</p>
                                    </div>
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Email</p>
                                        <p class="text-on-background font-medium">${escapeHtml(pi.email || 'Not provided')}</p>
                                    </div>
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Phone</p>
                                        <p class="text-on-background font-medium">${escapeHtml(pi.phone || 'Not provided')}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Contact Address</p>
                                        <p class="text-on-background font-medium">
                                            ${escapeHtml([pi.street, pi.city, pi.state, pi.zip].filter(Boolean).join(', ') || 'Not provided')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </details>
                        <details class="group rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-sm" open>
                            <summary class="flex cursor-pointer items-center justify-between p-5 list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-primary">
                                        <?php echo wt_icon('group', 'text-xl'); ?>
                                    </div>
                                    <p class="text-primary font-semibold">Beneficiaries</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="button" onclick="window.location.href='?step=4'" class="text-secondary text-sm font-bold hover:underline">Edit</button>
                                    <?php echo wt_icon('chevron-down', 'text-on-surface-variant group-open:rotate-180 transition-transform'); ?>
                                </div>
                            </summary>
                            <div class="px-5 pb-5 pt-0 border-t border-outline-variant/20 mt-2">
                                <div class="pt-4 space-y-4">
                                    ${onboardingData.beneficiaries.length > 0 ? 
                                        onboardingData.beneficiaries.map(ben => `
                                            <div class="flex justify-between items-center bg-surface-container-low p-3 rounded-lg">
                                                <div>
                                                    <p class="font-medium text-primary">${escapeHtml(ben.name)}${ben.is_myself ? ' <span class="text-sm text-secondary">(Myself)</span>' : ''}</p>
                                                    <p class="text-sm text-on-surface-variant">${escapeHtml(ben.relationship || '')} - ${ben.email || 'No email'}</p>
                                                </div>
                                                <span class="font-bold text-primary">${ben.allocation}%</span>
                                            </div>
                                        `).join('') :
                                        '<p class="text-on-surface-variant">No beneficiaries added</p>'
                                    }
                                </div>
                            </div>
                        </details>
                        <details class="group rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-sm" open>
                            <summary class="flex cursor-pointer items-center justify-between p-5 list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-primary">
                                        <?php echo wt_icon('payments', 'text-xl'); ?>
                                    </div>
                                    <p class="text-primary font-semibold">LLC Structure</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="button" onclick="window.location.href='?step=2'" class="text-secondary text-sm font-bold hover:underline">Edit</button>
                                    <?php echo wt_icon('chevron-down', 'text-on-surface-variant group-open:rotate-180 transition-transform'); ?>
                                </div>
                            </summary>
                            <div class="px-5 pb-5 pt-0 border-t border-outline-variant/20 mt-2">
                                <div class="pt-4 space-y-4">
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">LLC Structure</p>
                                        <p class="text-on-background font-medium">${escapeHtml(trustTypeName)}</p>
                                    </div>
                                    ${isSmartContractTrustSelected() && (onboardingData.entrusted_coins || []).length ? `
                                    <div>
                                        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider">Entrusted Cryptocurrencies</p>
                                        <p class="text-on-background font-medium">${(onboardingData.entrusted_coins || []).map(k => escapeHtml(k.replace(/_/g, ' '))).join(', ')}</p>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </details>
                    </div>
                    <div class="bg-secondary-fixed rounded-xl p-6 border border-secondary/20 flex flex-col md:flex-row items-center gap-6">
                        <div class="flex -space-x-2">
                            <div class="size-12 rounded-full border-2 border-surface-container-lowest bg-surface-container-lowest flex items-center justify-center text-primary shadow-sm" title="SSL Encrypted">
                                <?php echo wt_icon('lock', 'w-5 h-5'); ?>
                            </div>
                            <div class="size-12 rounded-full border-2 border-surface-container-lowest bg-surface-container-lowest flex items-center justify-center text-green-500 shadow-sm" title="Compliance Verified">
                                <?php echo wt_icon('shield', 'w-5 h-5'); ?>
                            </div>
                            <div class="size-12 rounded-full border-2 border-surface-container-lowest bg-surface-container-lowest flex items-center justify-center text-primary shadow-sm" title="Secure Payment">
                                <?php echo wt_icon('lock', 'w-5 h-5'); ?>
                            </div>
                        </div>
                        <div>
                            <p class="text-primary font-bold text-lg">Your data is secured with bank-grade encryption</p>
                            <p class="text-on-surface-variant text-sm">We use 256-bit AES encryption and multi-signature security protocols to ensure your legal documents and assets are protected.</p>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-outline-variant/20">
                            <h2 class="text-primary text-xl font-bold flex items-center gap-2">
                                <?php echo wt_icon('payments', 'text-secondary'); ?>
                                ${isFree ? 'Registration' : 'Payment'}
                            </h2>
                            <p class="text-on-surface-variant text-sm mt-1">
                                ${isFree ? 'No payment is required. Register your LLC when you are ready.' : 'Pick a payment method, review the details, then confirm.'}
                            </p>
                        </div>
                        <div class="p-6" id="paymentFlowContainer">
                            ${isFree ? `
                                <div class="rounded-xl border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 p-5">
                                    <p class="font-bold text-primary">No payment required</p>
                                    <p class="text-sm text-on-surface-variant mt-1">Click <strong>Register My LLC</strong> in the order summary. You will see a confirmation, then we will open your new LLC.</p>
                                </div>
                            ` : (
                                paymentStage === 'details'
                                    ? renderSelectedPaymentDetails(price)
                                    : paymentStage === 'confirmed'
                                        ? renderPaymentConfirmed()
                                        : `
                                            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-low p-5">
                                                <p class="font-bold text-primary">Select a payment method</p>
                                                <p class="text-sm text-on-surface-variant mt-1">
                                                    Choose a payment method in the <strong>Order Summary</strong> panel on the right, then click <strong>Continue to Payment Details</strong>.
                                                </p>
                                                ${onboardingData.payment_method_id ? `
                                                    <p class="text-xs text-on-surface-variant mt-3">
                                                        Selected method ID: <span class="font-mono">${onboardingData.payment_method_id}</span>
                                                    </p>
                                                ` : ''}
                                            </div>
                                        `
                            )}
                        </div>
                    </div>
                </div>
                <aside class="flex flex-col gap-6 lg:col-span-2 sticky top-24">
                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-outline-variant/20">
                            <h2 class="text-primary text-xl font-bold">Order Summary</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant">Trust Formation Fee</span>
                                <span class="text-primary font-medium">${isFree ? 'Free' : '$' + price.toFixed(2)}</span>
                            </div>
                            <div class="pt-4 border-t border-outline-variant/20 flex justify-between items-center">
                                <span class="text-primary font-black text-lg">Total Amount</span>
                                <span class="text-secondary font-black text-2xl tracking-tighter">${isFree ? 'Free' : '$' + price.toFixed(2)}</span>
                            </div>
                        </div>
                        <div class="px-6 pb-6">
                            ${isFree ? `
                                <button id="freeCheckoutBtn" type="button" onclick="completeFreeCheckout()" class="w-full bg-secondary text-on-secondary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all shadow-lg flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <?php echo wt_icon('check-circle', 'w-5 h-5'); ?>
                                    <span>Register My LLC</span>
                                </button>
                            ` : (
                                paymentStage === 'details'
                                    ? `
                                        <button id="confirmPaymentBtn" type="button" onclick="confirmPaymentAndCreateTrust()" class="w-full bg-secondary text-on-secondary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all shadow-lg flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <?php echo wt_icon('shield', 'w-5 h-5'); ?>
                                            <span>I’ve made this payment</span>
                                        </button>
                                        <button onclick="backToPaymentSelection()" class="w-full mt-3 border border-outline-variant/30 hover:bg-surface-container-low text-primary font-semibold py-3 px-6 rounded-xl">
                                            Change payment method
                                        </button>
                                    `
                                    : paymentStage === 'confirmed'
                                        ? `
                                            <button onclick="doneToDashboard()" class="w-full bg-secondary text-on-secondary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all shadow-lg flex items-center justify-center gap-3">
                                                <?php echo wt_icon('dashboard', 'w-5 h-5'); ?>
                                                View My Trust
                                            </button>
                                        `
                                        : `
                                            <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider mb-3">Select Payment Method</p>
                                            <div id="paymentMethodsContainer" class="space-y-3 mb-6">
                                                <div class="text-center py-4 text-on-surface-variant text-sm">Loading payment methods...</div>
                                            </div>
                                            <button id="continueToPaymentDetailsBtn" onclick="goToPaymentDetails()" ${onboardingData.payment_method_id ? '' : 'disabled'} class="w-full bg-secondary text-on-secondary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all shadow-lg flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
                                                Continue to Payment Details
                                            </button>
                                        `
                            )}
                            <p class="text-center text-on-surface-variant text-[10px] mt-4 uppercase tracking-widest font-bold">
                                Guaranteed Secure Transaction
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
            <div class="mt-8 flex justify-start">
                <button onclick="previousStep()" class="px-6 py-2 text-on-surface-variant hover:text-primary">Previous</button>
            </div>
        </div>
    `;
}

function selectTrustType(serviceKey, serviceId) {
    onboardingData.trust_service_id = Number(serviceId) || serviceId;
    onboardingData.trust_type = serviceKey;
    if (serviceKey !== 'smart_contract_trust' && serviceKey !== 'crypto_asset_trust') {
        onboardingData.entrusted_coins = [];
    }
    saveOnboardingToStorage();

    // Enable next button and refresh step UI without blocking on a full reload race
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) nextBtn.disabled = false;

    const container = document.getElementById('onboardingContent');
    if (container) {
        container.innerHTML = renderTrustTypeStep();
    }
    ensureCoinsForSmartContract();
}

function savePersonalInfo() {
    const trustNameInput = document.getElementById('trustNameInput');
    if (trustNameInput) {
        onboardingData.trust_name = trustNameInput.value.trim();
    }
    const totalAssetValueInput = document.getElementById('totalAssetValueInput');
    if (totalAssetValueInput) {
        onboardingData.total_estimated_value = totalAssetValueInput.value.trim();
    }

    const companyNameEl = document.getElementById('companyNameInput');
    const formationEl = document.getElementById('formationStateInput');
    const endingEl = document.getElementById('businessEndingInput');
    onboardingData.business_info = {
        entity_type: onboardingData.business_info?.entity_type || '',
        company_name: companyNameEl ? companyNameEl.value.trim() : (onboardingData.business_info?.company_name || ''),
        formation_state: formationEl ? formationEl.value.trim() : (onboardingData.business_info?.formation_state || ''),
        business_ending: endingEl ? endingEl.value.trim() : (onboardingData.business_info?.business_ending || '')
    };

    const firstName = document.getElementById('firstNameInput')?.value.trim() || '';
    const lastName = document.getElementById('lastNameInput')?.value.trim() || '';
    onboardingData.personal_info = {
        first_name: firstName,
        last_name: lastName,
        email: document.getElementById('emailInput')?.value.trim() || '',
        phone: document.getElementById('phoneInput')?.value.trim() || '',
        street: document.getElementById('streetInput')?.value.trim() || '',
        city: document.getElementById('cityInput')?.value.trim() || '',
        state: document.getElementById('stateInput')?.value.trim() || '',
        zip: document.getElementById('zipInput')?.value.trim() || ''
    };
    
    // Save password if not logged in
    if (!isLoggedIn) {
        const passwordInput = document.getElementById('passwordInput');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');
        if (passwordInput) onboardingData.password = passwordInput.value;
        if (confirmPasswordInput) onboardingData.confirm_password = confirmPasswordInput.value;
    }
    saveOnboardingToStorage();
}

function validateStep2Fields() {
    if (!onboardingData.trust_name) {
        alert('Please enter an LLC name.');
        return false;
    }
    const bi = onboardingData.business_info || {};
    if (!bi.company_name) {
        alert('Please enter a company name.');
        return false;
    }
    if (!isValidFormationState(bi.formation_state)) {
        alert('Please select a Formation State / Jurisdiction.');
        return false;
    }
    if (!isValidBusinessEnding(bi.business_ending)) {
        alert('Please select a business ending.');
        return false;
    }
    if (isCatalogTrustSelected()) {
        const totalValue = parseFloat(onboardingData.total_estimated_value);
        if (onboardingData.total_estimated_value === '' || Number.isNaN(totalValue) || totalValue < 0) {
            alert('Please enter a valid total asset value.');
            return false;
        }
        onboardingData.total_estimated_value = totalValue;
    }

    const pi = onboardingData.personal_info || {};
    if (!pi.first_name) {
        alert('Please enter your first name.');
        return false;
    }
    if (!pi.last_name) {
        alert('Please enter your last name.');
        return false;
    }
    if (!pi.email) {
        alert('Please enter your email address.');
        return false;
    }
    if (!isValidPhone(pi.phone)) {
        alert('Please enter a valid phone number.');
        return false;
    }
    if (!pi.street || !pi.city || !pi.state || !pi.zip) {
        alert('Please complete your contact address.');
        return false;
    }
    return true;
}

async function savePersonalInfoAndContinue() {
    const continueBtn = document.getElementById('continueToBeneficiariesBtn');
    if (continueBtn?.disabled || activeStepNavigation) {
        return;
    }

    savePersonalInfo();
    saveOnboardingToStorage();

    if (!validateStep2Fields()) {
        return;
    }
    saveOnboardingToStorage();

    const setContinueLoading = (loading) => {
        if (!continueBtn) return;
        continueBtn.disabled = loading;
        if (loading) {
            if (!continueBtn.dataset.defaultLabel) {
                continueBtn.dataset.defaultLabel = continueBtn.innerHTML;
            }
            continueBtn.innerHTML = '<span>Continuing...</span>';
        } else if (continueBtn.dataset.defaultLabel) {
            continueBtn.innerHTML = continueBtn.dataset.defaultLabel;
        }
    };

    setContinueLoading(true);
    
    // If not logged in, register the user first
    if (!isLoggedIn) {
        if (!onboardingData.password || !onboardingData.confirm_password) {
            alert('Please enter and confirm your password.');
            setContinueLoading(false);
            return;
        }
        
        if (onboardingData.password !== onboardingData.confirm_password) {
            alert('Passwords do not match.');
            setContinueLoading(false);
            return;
        }
        
        if (onboardingData.password.length < 8) {
            alert('Password must be at least 8 characters long.');
            setContinueLoading(false);
            return;
        }
        
        // Register the user — full_name is the PERSON, never company_name
        try {
            const response = await fetch('../api/register.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    full_name: getPersonalFullName(),
                    email: onboardingData.personal_info.email,
                    password: onboardingData.password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store registration data
                onboardingData.registration_email = data.email;
                onboardingData.requires_verification = data.requires_verification;
                onboardingData.email_sent = data.email_sent;
                saveOnboardingToStorage();
                
                // If email verification is required, show OTP step
                if (data.requires_verification) {
                    // Show OTP verification step instead of going to beneficiaries
                    isLoggedIn = true;
                    showOTPVerificationStep();
                } else {
                    // No verification needed, continue to beneficiaries
                    isLoggedIn = true;
                    saveOnboardingToStorage();
                    await goToStep(ONBOARDING_STEP.BENEFICIARIES);
                }
            } else {
                alert('Registration failed: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Registration error:', error);
            alert('An error occurred during registration. Please try again.');
        } finally {
            setContinueLoading(false);
        }
    } else {
        // Already logged in, continue to beneficiaries
        try {
            await goToStep(ONBOARDING_STEP.BENEFICIARIES);
        } finally {
            setContinueLoading(false);
        }
    }
}

function showOTPVerificationStep() {
    const content = document.getElementById('onboardingContent');
    content.innerHTML = renderOTPVerificationStep();
    // Update progress to show email verification before beneficiaries
    document.getElementById('currentStep').textContent = String(ONBOARDING_STEP.PERSONAL_INFO);
    document.getElementById('stepTitle').textContent = 'Verify Email';
    document.getElementById('progressBar').style.width = ((ONBOARDING_STEP.PERSONAL_INFO / ONBOARDING_TOTAL_STEPS) * 100) + '%';
    // Attach OTP input event listeners after HTML is inserted
    setupOTPInputs();
    saveOnboardingToStorage();
}

function renderOTPVerificationStep() {
    const email = onboardingData.registration_email || onboardingData.personal_info?.email || '';
    return `
        <div class="max-w-md mx-auto">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-secondary/10 rounded-full mb-4">
                    <?php echo wt_icon('mail', 'text-secondary text-3xl'); ?>
                </div>
                <h1 class="text-primary text-3xl font-black leading-tight tracking-tight mb-3">Verify Your Email</h1>
                <p class="text-on-surface-variant text-base leading-relaxed">
                    We've sent a 6-digit verification code to<br>
                    <strong class="text-primary">${escapeHtml(email)}</strong>
                </p>
                <p class="text-on-surface-variant text-sm leading-relaxed mt-3 max-w-sm mx-auto">
                    Don't see it in your inbox? Check your <strong class="text-primary">spam or junk folder</strong> — verification emails sometimes land there.
                </p>
            </div>
            
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-8 shadow-sm mb-6">
                <form onsubmit="event.preventDefault(); verifyOTP();" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-3 text-center">
                            Enter Verification Code
                        </label>
                        <div class="flex justify-center gap-2 mb-4">
                            <input type="text" id="otp1" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                            <input type="text" id="otp2" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                            <input type="text" id="otp3" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                            <span class="w-2"></span>
                            <input type="text" id="otp4" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                            <input type="text" id="otp5" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                            <input type="text" id="otp6" maxlength="1" pattern="[0-9]" inputmode="numeric" 
                                   class="w-12 h-14 text-center text-2xl font-bold border-2 border-outline-variant rounded-lg bg-surface-container-low text-primary focus:ring-2 focus:ring-secondary focus:border-secondary" 
                                   autocomplete="off" required/>
                        </div>
                        
                        <div id="otpError" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm text-center"></div>
                        
                        <button type="submit" id="verifyOTPBtn" class="w-full bg-secondary text-on-secondary hover:opacity-90 font-semibold py-3 px-6 rounded-lg transition-opacity">
                            Verify & Continue
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 pt-6 border-t border-outline-variant/30">
                    <p class="text-center text-sm text-on-surface-variant mb-4">
                        Didn't receive the code?
                    </p>
                    <button onclick="resendOTP()" id="resendOTPBtn" class="w-full text-secondary hover:text-secondary/80 font-semibold py-2 px-4 rounded-lg transition-colors">
                        Resend Code
                    </button>
                    <p id="resendCooldown" class="hidden text-center text-xs text-on-surface-variant dark:text-on-surface-variant mt-2"></p>
                </div>
            </div>
            
            <div class="text-center">
                <button onclick="goBackToPersonalInfo()" class="text-on-surface-variant hover:text-primary text-sm">
                    ← Back to Personal Info
                </button>
            </div>
        </div>
    `;
}

function setupOTPInputs() {
    // Auto-focus and move to next input
    const otpInputs = ['otp1', 'otp2', 'otp3', 'otp4', 'otp5', 'otp6'];
    otpInputs.forEach((inputId, index) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        input.addEventListener('input', (e) => {
            // Only allow numbers
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value && index < otpInputs.length - 1) {
                const nextInput = document.getElementById(otpInputs[index + 1]);
                if (nextInput) nextInput.focus();
            }
        });
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                const prevInput = document.getElementById(otpInputs[index - 1]);
                if (prevInput) {
                    prevInput.focus();
                    prevInput.value = '';
                }
            }
        });
        
        // Prevent paste on individual inputs - allow paste only on first input
        if (index === 0) {
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const digits = pastedData.replace(/[^0-9]/g, '').slice(0, 6);
                if (digits.length === 6) {
                    otpInputs.forEach((id, idx) => {
                        const inp = document.getElementById(id);
                        if (inp) inp.value = digits[idx] || '';
                    });
                    document.getElementById('otp6').focus();
                }
            });
        }
    });
    
    // Focus first input on load
    setTimeout(() => {
        const firstInput = document.getElementById('otp1');
        if (firstInput) firstInput.focus();
    }, 100);
}

async function verifyOTP() {
    const otp1 = document.getElementById('otp1').value;
    const otp2 = document.getElementById('otp2').value;
    const otp3 = document.getElementById('otp3').value;
    const otp4 = document.getElementById('otp4').value;
    const otp5 = document.getElementById('otp5').value;
    const otp6 = document.getElementById('otp6').value;
    
    const otpCode = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
    
    if (otpCode.length !== 6) {
        showOTPError('Please enter all 6 digits');
        return;
    }
    
    const email = onboardingData.registration_email || onboardingData.personal_info?.email || '';
    if (!email) {
        showOTPError('Email not found. Please go back and re-enter your information.');
        return;
    }
    
    const verifyBtn = document.getElementById('verifyOTPBtn');
    const errorDiv = document.getElementById('otpError');
    
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';
    errorDiv.classList.add('hidden');
    
    try {
        const response = await fetch('../api/verify-otp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                otp: otpCode,
                email: email
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Email verified successfully - continue to beneficiaries
            showToast('Email verified successfully!', 'success');
            isLoggedIn = true;
            saveOnboardingToStorage();
            await goToStep(ONBOARDING_STEP.BENEFICIARIES);
        } else {
            showOTPError(data.message || 'Invalid verification code. Please try again.');
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify & Continue';
            // Clear OTP inputs
            document.querySelectorAll('#otp1, #otp2, #otp3, #otp4, #otp5, #otp6').forEach(input => input.value = '');
            document.getElementById('otp1').focus();
        }
    } catch (error) {
        console.error('OTP verification error:', error);
        showOTPError('An error occurred. Please try again.');
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify & Continue';
    }
}

function showOTPError(message) {
    const errorDiv = document.getElementById('otpError');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }
}

async function resendOTP() {
    const email = onboardingData.registration_email || onboardingData.personal_info?.email || '';
    if (!email) {
        showToast('Email not found', 'error');
        return;
    }
    
    const resendBtn = document.getElementById('resendOTPBtn');
    const cooldownDiv = document.getElementById('resendCooldown');
    
    resendBtn.disabled = true;
    resendBtn.textContent = 'Sending...';
    
    try {
        const response = await fetch('../api/user/resend-verification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ email: email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Verification code sent! Please check your email.', 'success');
            // Start cooldown countdown
            if (data.cooldown_remaining) {
                startResendCooldown(data.cooldown_remaining);
            }
        } else {
            showToast(data.message || 'Failed to resend code. Please try again.', 'error');
            if (data.cooldown_remaining && data.cooldown_remaining > 0) {
                startResendCooldown(data.cooldown_remaining);
            } else {
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
            }
        }
    } catch (error) {
        console.error('Resend OTP error:', error);
        showToast('An error occurred. Please try again.', 'error');
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend Code';
    }
}

function startResendCooldown(seconds) {
    const resendBtn = document.getElementById('resendOTPBtn');
    const cooldownDiv = document.getElementById('resendCooldown');
    
    let remaining = seconds;
    resendBtn.disabled = true;
    cooldownDiv.classList.remove('hidden');
    cooldownDiv.textContent = `Please wait ${remaining} seconds before requesting another code`;
    
    const interval = setInterval(() => {
        remaining--;
        if (remaining > 0) {
            cooldownDiv.textContent = `Please wait ${remaining} seconds before requesting another code`;
        } else {
            clearInterval(interval);
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend Code';
            cooldownDiv.classList.add('hidden');
        }
    }, 1000);
}

function goBackToPersonalInfo() {
    saveOnboardingToStorage();
    window.location.href = 'onboarding.php?step=3';
}

function addNewBeneficiary() {
    onboardingData.beneficiaries.push({
        name: '',
        relationship: '',
        email: '',
        allocation: 0,
        wallet_address: '',
        is_myself: false
    });
    saveOnboardingToStorage();
    loadStep(ONBOARDING_STEP.BENEFICIARIES);
}

function updateBeneficiary(index, field, value) {
    if (onboardingData.beneficiaries[index]) {
        // Don't allow editing name, email, or relationship for "myself"
        if (onboardingData.beneficiaries[index].is_myself && (field === 'name' || field === 'email' || field === 'relationship')) {
            return;
        }
        
        onboardingData.beneficiaries[index][field] = value;
        if (field === 'allocation') {
            onboardingData.beneficiaries[index].allocation = parseFloat(value) || 0;
            updateAllocationDisplay();
            syncBeneficiaryVisibility();
        }
        saveOnboardingToStorage();
    }
}

function updateAllocationDisplay() {
    const activeBeneficiaries = getActiveBeneficiaries();
    const totalAllocation = activeBeneficiaries.reduce((sum, ben) => sum + (parseFloat(ben.allocation) || 0), 0);
    const isValid = Math.abs(totalAllocation - 100) < 0.01;
    const statusEl = document.getElementById('allocationStatus');
    const nextBtn = document.querySelector('button[onclick="validateAndNext()"]');
    
    if (statusEl) {
        statusEl.textContent = `Current total: ${totalAllocation.toFixed(2)}%`;
        statusEl.className = `font-semibold ${isValid ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`;
    }
    
    if (nextBtn) {
        if (isValid) {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

function removeBeneficiary(index) {
    // Don't allow removing "myself" via this function
    if (onboardingData.beneficiaries[index] && onboardingData.beneficiaries[index].is_myself) {
        return;
    }
    onboardingData.beneficiaries.splice(index, 1);
    saveOnboardingToStorage();
    loadStep(ONBOARDING_STEP.BENEFICIARIES);
}

function toggleAddMyself(checked) {
    if (checked) {
        // Check if already added
        const exists = onboardingData.beneficiaries.find(b => b.is_myself);
        if (!exists && getPersonalFullName()) {
            onboardingData.beneficiaries.unshift({
                name: getPersonalFullName(),
                relationship: 'Self',
                email: onboardingData.personal_info.email || '',
                allocation: 0,
                wallet_address: '',
                is_myself: true
            });
        }
    } else {
        // Remove myself
        const index = onboardingData.beneficiaries.findIndex(b => b.is_myself);
        if (index >= 0) {
            onboardingData.beneficiaries.splice(index, 1);
        }
    }
    saveOnboardingToStorage();
    loadStep(ONBOARDING_STEP.BENEFICIARIES);
}

function setupBeneficiariesStep() {
    // Attach checkbox event listener
    const checkbox = document.getElementById('addMyselfCheckbox');
    if (checkbox) {
        // Remove existing listeners by cloning
        const newCheckbox = checkbox.cloneNode(true);
        checkbox.parentNode.replaceChild(newCheckbox, checkbox);
        
        // Add fresh event listener
        document.getElementById('addMyselfCheckbox').addEventListener('change', function(e) {
            toggleAddMyself(e.target.checked);
        });
    }
    
    // Update allocation display on load
    updateAllocationDisplay();
    syncBeneficiaryVisibility();
}

function ensureDefaultBeneficiary() {
    if (!Array.isArray(onboardingData.beneficiaries)) onboardingData.beneficiaries = [];
    const hasNonMyself = onboardingData.beneficiaries.some(b => !b.is_myself);
    if (!hasNonMyself) {
        onboardingData.beneficiaries.push({
            name: '',
            relationship: '',
            email: '',
            allocation: 0,
            wallet_address: '',
            is_myself: false
        });
        saveOnboardingToStorage();
    }
}

function validateAndNext() {
    const activeBeneficiaries = getActiveBeneficiaries();
    const totalAllocation = activeBeneficiaries.reduce((sum, ben) => sum + (parseFloat(ben.allocation) || 0), 0);
    if (Math.abs(totalAllocation - 100) > 0.01) {
        alert('Total allocation must equal 100%. Current total: ' + totalAllocation.toFixed(2) + '%');
        return;
    }
    for (let i = 0; i < activeBeneficiaries.length; i++) {
        const ben = activeBeneficiaries[i];
        if (!ben.name || !ben.relationship || ben.allocation === '' || ben.allocation === null || ben.allocation === undefined) {
            alert('Please fill in all required fields for Beneficiary #' + (i + 1));
            return;
        }
    }
    void nextStep();
}

// Load payment methods when review step is loaded
let paymentMethods = [];

async function loadPaymentMethods() {
    try {
        const response = await fetch('../api/payment-methods.php');
        const data = await response.json();
        
        if (data.success && data.methods) {
            paymentMethods = data.methods;
            renderPaymentMethods(data.methods);
            // If we're on step 4 and in details stage, refresh the details panel now that methods exist
            if (currentStep === ONBOARDING_STEP.REVIEW && onboardingData.payment_stage === 'details') {
                loadStep(ONBOARDING_STEP.REVIEW);
            }
        } else {
            const container = document.getElementById('paymentMethodsContainer') || document.getElementById('paymentFlowContainer');
            if (container) {
                container.innerHTML = '<div class="text-center py-4 text-red-500 text-sm">Failed to load payment methods</div>';
            }
        }
    } catch (error) {
        console.error('Error loading payment methods:', error);
        const container = document.getElementById('paymentMethodsContainer') || document.getElementById('paymentFlowContainer');
        if (container) {
            container.innerHTML = '<div class="text-center py-4 text-red-500 text-sm">Error loading payment methods</div>';
        }
    }
}

function renderPaymentMethods(methods) {
    const container = document.getElementById('paymentMethodsContainer');
    if (!container) {
        // If we are not on the select stage, this container won't exist; no-op safely.
        return;
    }
    if (!methods || methods.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-on-surface-variant text-sm">No payment methods available</div>';
        return;
    }

    const selectedId = onboardingData.payment_method_id;
    const iconFor = (type) => {
        if (type === 'crypto') return 'currency_bitcoin';
        if (type === 'bank_transfer') return 'account_balance';
        if (type === 'paypal') return 'payments';
        return 'credit_card';
    };

    container.innerHTML = `
        <div class="grid grid-cols-1 gap-3">
            ${methods.map(m => `
                <button type="button"
                    onclick="selectPaymentMethod(${m.id}, '${m.method_type}')"
                    class="payment-method-card w-full rounded-xl border p-4 text-left transition-all hover:shadow-sm ${
                        (selectedId == m.id)
                            ? 'border-secondary ring-2 ring-secondary/30 bg-secondary/5'
                            : 'border-outline-variant/30 bg-surface-container-low'
                    }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center">
                                ${wtIcon(iconFor(m.method_type), 'text-secondary')}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-primary truncate">${escapeHtml(m.method_name || 'Payment Method')}</p>
                                <p class="text-xs text-on-surface-variant">${escapeHtml((m.method_type || '').replace('_', ' '))}</p>
                            </div>
                        </div>
                        <div class="payment-method-radio">
                            <input type="radio" name="selected_payment_method" value="${m.id}" ${selectedId == m.id ? 'checked' : ''} />
                        </div>
                    </div>
                </button>
            `).join('')}
        </div>
    `;
}

function renderCryptoPaymentMethod(method, config) {
    const walletAddress = config.wallet_address || '';
    const qrCode = config.qr_code || '';
    const coinName = config.coin_name || method.method_name;
    const networkType = config.network_type || '';
    
    return `
        <div class="border border-outline-variant/30 rounded-lg p-4 mb-3 payment-method-card" onclick="selectPaymentMethod(${method.id}, 'crypto')">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h4 class="font-bold text-primary mb-1">${escapeHtml(coinName)}</h4>
                    ${networkType ? `<p class="text-xs text-on-surface-variant">${escapeHtml(networkType)}</p>` : ''}
                </div>
                <div class="payment-method-radio">
                    <input type="radio" name="selected_payment_method" value="${method.id}" id="payment_${method.id}" 
                           ${onboardingData.payment_method_id == method.id ? 'checked' : ''}>
                    <label for="payment_${method.id}" class="cursor-pointer"></label>
                </div>
            </div>
            ${walletAddress ? `
                <div class="bg-surface-container rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <label class="text-xs font-semibold text-on-surface-variant">Wallet Address</label>
                        <button onclick="event.stopPropagation(); copyToClipboard('${escapeHtml(walletAddress)}', ${method.id})" 
                                class="text-secondary hover:text-secondary/80 text-xs font-semibold flex items-center gap-1">
                            <?php echo wt_icon('share', 'text-sm'); ?>
                            Copy
                        </button>
                    </div>
                    <p class="text-xs font-mono text-primary break-all" id="wallet_${method.id}">${escapeHtml(walletAddress)}</p>
                    <div id="copyFeedback_${method.id}" class="hidden text-xs text-green-600 dark:text-green-400 mt-1">✓ Copied!</div>
                </div>
            ` : ''}
            ${qrCode ? `
                <div class="flex justify-center mb-2">
                    <img src="../${qrCode}" alt="QR Code" class="max-w-32 max-h-32 border border-outline-variant/30 rounded-lg p-2 bg-white">
                </div>
                <p class="text-xs text-center text-on-surface-variant">Scan QR code to send payment</p>
            ` : ''}
        </div>
    `;
}

function renderBankPaymentMethod(method, config) {
    const bankName = config.bank_name || '';
    const accountName = config.account_name || '';
    const accountNumber = config.account_number_masked || config.account_number || '';
    const routingNumber = config.routing_number || '';
    const swiftCode = config.swift_code || '';
    const additionalDetails = config.additional_details || '';
    
    return `
        <div class="border border-outline-variant/30 rounded-lg p-4 mb-3 payment-method-card" onclick="selectPaymentMethod(${method.id}, 'bank_transfer')">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h4 class="font-bold text-primary mb-1">${escapeHtml(bankName)}</h4>
                    <p class="text-xs text-on-surface-variant">${escapeHtml(accountName)}</p>
                </div>
                <div class="payment-method-radio">
                    <input type="radio" name="selected_payment_method" value="${method.id}" id="payment_${method.id}"
                           ${onboardingData.payment_method_id == method.id ? 'checked' : ''}>
                    <label for="payment_${method.id}" class="cursor-pointer"></label>
                </div>
            </div>
            <div class="space-y-2 text-xs">
                ${accountNumber ? `
                    <div class="flex items-center justify-between">
                        <span class="text-on-surface-variant">Account Number:</span>
                        <span class="font-mono text-primary">${escapeHtml(accountNumber)}</span>
                    </div>
                ` : ''}
                ${routingNumber ? `
                    <div class="flex items-center justify-between">
                        <span class="text-on-surface-variant">Routing Number:</span>
                        <span class="font-mono text-primary">${escapeHtml(routingNumber)}</span>
                    </div>
                ` : ''}
                ${swiftCode ? `
                    <div class="flex items-center justify-between">
                        <span class="text-on-surface-variant">SWIFT/BIC:</span>
                        <span class="font-mono text-primary">${escapeHtml(swiftCode)}</span>
                    </div>
                ` : ''}
                ${additionalDetails ? `
                    <div class="mt-2 pt-2 border-t border-outline-variant/30">
                        <p class="text-on-surface-variant mb-1">Additional Details:</p>
                        <p class="text-primary">${escapeHtml(additionalDetails)}</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

function renderPayPalPaymentMethod(method, config) {
    const paypalEmail = config.paypal_email || config.paypal_tag || '';
    
    return `
        <div class="border border-outline-variant/30 rounded-lg p-4 mb-3 payment-method-card" onclick="selectPaymentMethod(${method.id}, 'paypal')">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h4 class="font-bold text-primary mb-1">PayPal</h4>
                    ${paypalEmail ? `<p class="text-xs text-on-surface-variant">${escapeHtml(paypalEmail)}</p>` : ''}
                </div>
                <div class="payment-method-radio">
                    <input type="radio" name="selected_payment_method" value="${method.id}" id="payment_${method.id}"
                           ${onboardingData.payment_method_id == method.id ? 'checked' : ''}>
                    <label for="payment_${method.id}" class="cursor-pointer"></label>
                </div>
            </div>
            <p class="text-xs text-on-surface-variant">You will be redirected to PayPal to complete the payment.</p>
        </div>
    `;
}

function selectPaymentMethod(methodId, methodType) {
    onboardingData.payment_method_id = methodId;
    onboardingData.payment_method = methodType;
    onboardingData.payment_stage = 'select';
    onboardingData.payment_confirmed = false;
    saveOnboardingToStorage();
    
    // Update radio button selection
    document.querySelectorAll('input[name="selected_payment_method"]').forEach(radio => {
        radio.checked = radio.value == methodId;
    });
    
    // Update visual selection
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('border-secondary', 'ring-2', 'ring-secondary');
        card.classList.add('border-outline-variant/30');
    });
    
    const selectedCard = document.querySelector(`input[name="selected_payment_method"][value="${methodId}"]`)?.closest('.payment-method-card');
    if (selectedCard) {
        selectedCard.classList.remove('border-outline-variant/30');
        selectedCard.classList.add('border-secondary', 'ring-2', 'ring-secondary');
    }

    // Enable the "Continue to Payment Details" button immediately (no full re-render needed)
    const continueBtn = document.getElementById('continueToPaymentDetailsBtn');
    if (continueBtn) {
        continueBtn.disabled = false;
    }
}

function goToPaymentDetails() {
    if (!onboardingData.payment_method_id) return;
    onboardingData.payment_stage = 'details';
    saveOnboardingToStorage();
    loadStep(ONBOARDING_STEP.REVIEW);
}

function backToPaymentSelection() {
    onboardingData.payment_stage = 'select';
    onboardingData.payment_confirmed = false;
    saveOnboardingToStorage();
    loadStep(ONBOARDING_STEP.REVIEW);
}

function getSelectedPaymentMethodObj() {
    if (!paymentMethods || !Array.isArray(paymentMethods)) return null;
    return paymentMethods.find(m => (m.id == onboardingData.payment_method_id)) || null;
}

function renderSelectedPaymentDetails(amount) {
    const method = getSelectedPaymentMethodObj();
    if (!method) {
        return `
            <div class="text-center py-4">
                <p class="text-sm text-on-surface-variant">Select a payment method to continue.</p>
                <button onclick="backToPaymentSelection()" class="mt-3 text-secondary font-semibold">Back</button>
            </div>
        `;
    }

    const config = method.config_data || {};

    let detailsHtml = '';
    if (method.method_type === 'crypto') {
        const walletAddress = config.wallet_address || '';
        const qrCode = config.qr_code || '';
        const coinName = config.coin_name || method.method_name;
        const networkType = config.network_type || '';
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant/30 p-4">
                <p class="font-bold text-primary mb-2">Crypto payment details</p>
                <p class="text-sm text-on-surface-variant">Send <strong class="text-primary">$${amount.toFixed(2)}</strong> using ${escapeHtml(coinName)} ${networkType ? `(${escapeHtml(networkType)})` : ''}.</p>
                ${walletAddress ? `
                    <div class="mt-4 bg-surface-container rounded-lg p-3">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide">Wallet Address</p>
                            <button onclick="copyToClipboard('${escapeHtml(walletAddress)}', ${method.id})" class="text-secondary text-xs font-semibold flex items-center gap-1">
                                <?php echo wt_icon('share', 'text-sm'); ?> Copy
                            </button>
                        </div>
                        <p class="text-xs font-mono text-primary break-all" id="wallet_${method.id}">${escapeHtml(walletAddress)}</p>
                        <div id="copyFeedback_${method.id}" class="hidden text-xs text-green-600 dark:text-green-400 mt-1">✓ Copied!</div>
                    </div>
                ` : ''}
                ${qrCode ? `
                    <div class="mt-4 flex flex-col sm:flex-row items-center gap-4">
                        <img src="../${qrCode}" alt="QR Code" class="max-w-40 max-h-40 border border-outline-variant/30 rounded-lg p-2 bg-white">
                        <p class="text-xs text-on-surface-variant">Scan to pay.</p>
                    </div>
                ` : ''}
            </div>
        `;
    } else if (method.method_type === 'bank_transfer') {
        const bankName = config.bank_name || '';
        const accountName = config.account_name || '';
        const accountNumber = config.account_number_masked || config.account_number || '';
        const routingNumber = config.routing_number || '';
        const swiftCode = config.swift_code || '';
        const additionalDetails = config.additional_details || '';
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant/30 p-4">
                <p class="font-bold text-primary mb-2">Bank transfer details</p>
                <p class="text-sm text-on-surface-variant mb-3">Transfer <strong class="text-primary">$${amount.toFixed(2)}</strong> using the details below.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">Bank</p><p class="text-primary">${escapeHtml(bankName)}</p></div>
                    <div><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">Account Name</p><p class="text-primary">${escapeHtml(accountName)}</p></div>
                    ${accountNumber ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">Account #</p><p class="text-primary font-mono">${escapeHtml(accountNumber)}</p></div>` : ''}
                    ${routingNumber ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">Routing</p><p class="text-primary font-mono">${escapeHtml(routingNumber)}</p></div>` : ''}
                    ${swiftCode ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">SWIFT/BIC</p><p class="text-primary font-mono">${escapeHtml(swiftCode)}</p></div>` : ''}
                </div>
                ${additionalDetails ? `<div class="mt-3 text-sm"><p class="text-xs text-on-surface-variant uppercase font-bold tracking-wide">Notes</p><p class="text-primary whitespace-pre-wrap">${escapeHtml(additionalDetails)}</p></div>` : ''}
            </div>
        `;
    } else if (method.method_type === 'paypal') {
        const paypalEmail = config.paypal_email || config.paypal_tag || '';
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant/30 p-4">
                <p class="font-bold text-primary mb-2">PayPal details</p>
                <p class="text-sm text-on-surface-variant">Send <strong class="text-primary">$${amount.toFixed(2)}</strong> to:</p>
                <p class="mt-2 font-mono text-primary">${escapeHtml(paypalEmail || 'Not configured')}</p>
            </div>
        `;
    } else {
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant/30 p-4">
                <p class="text-sm text-on-surface-variant">Payment method details not available.</p>
            </div>
        `;
    }

    return `
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-primary truncate">${escapeHtml(method.method_name || 'Payment Method')}</p>
                    <p class="text-xs text-on-surface-variant">Amount due: <strong class="text-primary">$${amount.toFixed(2)}</strong></p>
                </div>
                <button onclick="backToPaymentSelection()" class="text-sm font-semibold text-on-surface-variant hover:text-primary">Change</button>
            </div>
            ${detailsHtml}
            <div class="rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-900/20 p-4">
                <p class="text-sm text-on-background">
                    <strong>Note:</strong> Payment will be approved once received by the admin.
                </p>
            </div>
        </div>
    `;
}

function renderPaymentConfirmed() {
    return `
        <div class="space-y-4">
            <div class="rounded-xl border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 p-5">
                <p class="font-bold text-primary">Payment submitted</p>
                <p class="text-sm text-on-surface-variant mt-1">Your LLC has been created. Payment will be approved once received by the admin.</p>
            </div>
            <button onclick="doneToDashboard()" class="w-full bg-secondary text-on-secondary hover:opacity-90 font-bold py-3 px-5 rounded-xl shadow">
                View My Trust
            </button>
        </div>
    `;
}

function getTrustManageUrl(trustId) {
    if (trustId) {
        return `../dashboard/user/manage-trust.php?id=${encodeURIComponent(trustId)}`;
    }
    return '../dashboard/user/dashboard.php';
}

function setTrustCheckoutButtonLoading(loading, buttonId) {
    const btn = buttonId ? document.getElementById(buttonId) : null;
    if (!btn) return;
    btn.disabled = loading;
    if (loading) {
        if (!btn.dataset.defaultLabel) {
            btn.dataset.defaultLabel = btn.innerHTML;
        }
        btn.innerHTML = `<span class="inline-flex items-center justify-center gap-2"><span class="inline-block w-5 h-5 border-2 border-current border-t-transparent rounded-full animate-spin"></span><span>Registering your LLC...</span></span>`;
    } else if (btn.dataset.defaultLabel) {
        btn.innerHTML = btn.dataset.defaultLabel;
    }
}

async function showTrustRegistrationSuccess(trustId, options = {}) {
    const trustName = options.trustName || onboardingData.trust_name || 'Your LLC';
    const minDurationMs = Math.max(5000, Number(options.minDurationMs) || 5000);
    const redirectUrl = getTrustManageUrl(trustId);

    clearOnboardingStorage();

    const existing = document.getElementById('trustRegistrationSuccessOverlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'trustRegistrationSuccessOverlay';
    overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-navy-900/50 backdrop-blur-sm p-6';
    overlay.innerHTML = `
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-2xl max-w-md w-full p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-5 mx-auto">
                ${typeof wtIcon === 'function' ? wtIcon('check-circle', 'w-9 h-9 text-green-600') : ''}
            </div>
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-secondary border-t-transparent mx-auto mb-5"></div>
            <h2 class="text-xl font-bold text-primary mb-2">LLC Registered Successfully</h2>
            <p class="text-on-surface-variant text-sm"><strong>${escapeHtml(trustName)}</strong> has been registered.</p>
            <p class="text-on-surface-variant text-xs mt-4">Opening your LLC workspace...</p>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    await new Promise(resolve => setTimeout(resolve, minDurationMs));
    window.location.href = redirectUrl;
}

async function confirmPaymentAndCreateTrust() {
    onboardingData.payment_confirmed = true;
    saveOnboardingToStorage();
    setTrustCheckoutButtonLoading(true, 'confirmPaymentBtn');
    try {
        const result = await createTrust({ redirect: false });
        if (result && result.success) {
            onboardingData.payment_stage = 'confirmed';
            onboardingData.created_trust_id = result.trust_id || null;
            await showTrustRegistrationSuccess(result.trust_id, { trustName: onboardingData.trust_name });
        }
    } finally {
        setTrustCheckoutButtonLoading(false, 'confirmPaymentBtn');
    }
}

function doneToDashboard() {
    const trustId = onboardingData.created_trust_id;
    clearOnboardingStorage();
    window.location.href = getTrustManageUrl(trustId);
}

async function completeFreeCheckout() {
    onboardingData.payment_confirmed = false;
    onboardingData.payment_stage = 'select';
    saveOnboardingToStorage();
    setTrustCheckoutButtonLoading(true, 'freeCheckoutBtn');
    try {
        const result = await createTrust({ redirect: false });
        if (result && result.success) {
            await showTrustRegistrationSuccess(result.trust_id, { trustName: onboardingData.trust_name });
        }
    } finally {
        setTrustCheckoutButtonLoading(false, 'freeCheckoutBtn');
    }
}

function copyToClipboard(text, methodId) {
    navigator.clipboard.writeText(text).then(() => {
        const feedback = document.getElementById(`copyFeedback_${methodId}`);
        if (feedback) {
            feedback.classList.remove('hidden');
            setTimeout(() => {
                feedback.classList.add('hidden');
            }, 2000);
        }
        // Fallback notification
        showToast('Wallet address copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showToast('Failed to copy address', 'error');
    });
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

async function nextStep() {
    if (currentStep < ONBOARDING_TOTAL_STEPS) {
        await goToStep(currentStep + 1);
    }
}

async function previousStep() {
    if (currentStep > 1) {
        await goToStep(currentStep - 1);
    }
}

// Clear storage when user navigates away from onboarding (Cancel button, home page, etc.)
function handleCancelOrExit() {
    clearOnboardingStorage();
}

async function createTrust(options = { redirect: true }) {
    if (!isValidBusinessEntityType(onboardingData.business_info?.entity_type)) {
        alert('Please select whether this is a new or existing business.');
        window.location.href = 'onboarding.php?step=1';
        return;
    }

    if (!onboardingData.trust_service_id) {
        alert('Error: Please select an LLC structure first.');
        window.location.href = 'onboarding.php?step=2';
        return;
    }
    
    const selectedService = getSelectedTrustService();
    const isFreeFlag = selectedService ? (Number(selectedService.is_free) === 1) : true;
    const priceVal = selectedService ? Number(selectedService.price || 0) : 0;
    const price = selectedService ? (isFreeFlag ? 0.00 : priceVal) : 0.00;
    const isFree = !selectedService || isFreeFlag || price <= 0;
    
    if (!isFree && !onboardingData.payment_method_id) {
        alert('Please select a payment method to continue.');
        return;
    }
    
    // Validate all required data
    if (!onboardingData.trust_name) {
        alert('Please enter an LLC name.');
        window.location.href = 'onboarding.php?step=3';
        return;
    }

    const bi = onboardingData.business_info || {};
    if (!bi.company_name || !isValidFormationState(bi.formation_state) || !isValidBusinessEnding(bi.business_ending)) {
        alert('Please complete all required business information.');
        window.location.href = 'onboarding.php?step=3';
        return;
    }

    if (isCatalogTrustSelected()) {
        const totalValue = parseFloat(onboardingData.total_estimated_value);
        if (onboardingData.total_estimated_value === '' || Number.isNaN(totalValue) || totalValue < 0) {
            alert('Please enter a valid total asset value.');
            window.location.href = 'onboarding.php?step=3';
            return;
        }
    }

    const personName = getPersonalFullName();
    const pi = onboardingData.personal_info || {};
    if (!personName || !pi.email || !isValidPhone(pi.phone) || !pi.street || !pi.city || !pi.state || !pi.zip) {
        alert('Please complete all required personal information.');
        window.location.href = 'onboarding.php?step=3';
        return;
    }
    
    const activeBeneficiaries = getActiveBeneficiaries();
    if (!activeBeneficiaries.length) {
        alert('Please add at least one beneficiary.');
        window.location.href = 'onboarding.php?step=4';
        return;
    }
    
    const totalAllocation = activeBeneficiaries.reduce((sum, ben) => sum + (parseFloat(ben.allocation) || 0), 0);
    if (Math.abs(totalAllocation - 100) > 0.01) {
        alert('Total allocation must equal 100%. Current total: ' + totalAllocation.toFixed(2) + '%');
        window.location.href = 'onboarding.php?step=4';
        return;
    }
    
    // Ensure user is logged in before creating trust
    if (!isLoggedIn) {
        alert('Please complete registration first. Redirecting...');
        window.location.href = 'onboarding.php?step=3';
        return;
    }
    
    try {
        // Get CSRF token (api/session.php always returns JSON including csrf_token)
        let csrfToken = null;
        try {
            const csrfResponse = await fetch('../api/session.php');
            const csrfData = await csrfResponse.json();
            if (csrfData && csrfData.csrf_token) csrfToken = csrfData.csrf_token;
        } catch (e) {
            console.warn('Could not fetch CSRF token:', e);
        }
        
        const requestBody = {
            trust_service_id: onboardingData.trust_service_id,
            payment_method_id: isFree ? null : onboardingData.payment_method_id,
            trust_data: {
                trust_name: onboardingData.trust_name || '',
                ...(isCatalogTrustSelected() ? {
                    total_estimated_value: parseFloat(onboardingData.total_estimated_value) || 0
                } : {}),
                business_info: onboardingData.business_info || {},
                personal_info: onboardingData.personal_info,
                beneficiaries: activeBeneficiaries,
                ...(isSmartContractTrustSelected() && onboardingData.entrusted_coins?.length ? {
                    entrusted_coins: onboardingData.entrusted_coins
                } : {}),
                payment_info: isFree ? {
                    type: 'free',
                    amount: 0,
                } : {
                    payment_method_id: onboardingData.payment_method_id,
                    payment_method_type: onboardingData.payment_method,
                    amount: price,
                    user_confirmed: !!onboardingData.payment_confirmed,
                    confirmed_at: new Date().toISOString(),
                }
            }
        };
        
        if (csrfToken) {
            requestBody.csrf_token = csrfToken;
        }
        
        console.log('Creating trust with payload:', requestBody);
        
        const response = await fetch('../api/user/trusts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(requestBody)
        });
        
        const responseText = await response.text();
        console.log('API Response status:', response.status);
        console.log('API Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse API response as JSON:', parseError);
            console.error('Response was:', responseText);
            alert('Server returned invalid response. Please check console for details.');
            return { success: false };
        }
        
        if (data.success) {
            const newTrustId = data.trust?.id || null;
            onboardingData.created_trust_id = newTrustId;

            if (options && options.redirect) {
                await showTrustRegistrationSuccess(newTrustId, { trustName: onboardingData.trust_name });
            }
            return { success: true, trust_id: newTrustId };
        } else {
            const errorMsg = data.message || data.error_details || 'Unknown error';
            console.error('Trust creation failed:', data);
            alert('Failed to create LLC: ' + errorMsg);
            return { success: false };
        }
    } catch (error) {
        console.error('Error creating trust:', error);
        console.error('Error stack:', error.stack);
        alert('An error occurred while creating LLC: ' + (error.message || 'Unknown error') + '. Please check console for details.');
        return { success: false };
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

// Password visibility toggle function
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    // Hard refresh on step 1 starts clean; step 2+ restores draft
    if (currentStep <= 1) {
        clearOnboardingStorage();
    } else {
        loadOnboardingFromStorage();
    }

    // Load services with a hard timeout so the UI never sits on Loading forever
    await withTimeout(loadTrustServices(), 8000);

    try {
        await loadStep(currentStep);
    } catch (error) {
        console.error('Failed to initialize onboarding step:', error);
        showOnboardingError(error && error.message ? error.message : 'Failed to load onboarding.');
    }

    // Disable next button when required selections are missing on early steps
    if (currentStep === ONBOARDING_STEP.BUSINESS_ENTITY && !isValidBusinessEntityType(onboardingData.business_info?.entity_type)) {
        const nextBtn = document.getElementById('nextBtn');
        if (nextBtn) nextBtn.disabled = true;
    }
    if (currentStep === ONBOARDING_STEP.TRUST_TYPE && !onboardingData.trust_service_id) {
        const nextBtn = document.getElementById('nextBtn');
        if (nextBtn) nextBtn.disabled = true;
    }
});

window.addEventListener('popstate', async (event) => {
    const stepFromState = event.state && event.state.step;
    const stepFromUrl = parseInt(new URLSearchParams(window.location.search).get('step') || '1', 10);
    const step = Number(stepFromState || stepFromUrl || 1);
    currentStep = Number.isFinite(step) && step >= 1 && step <= ONBOARDING_TOTAL_STEPS ? step : 1;
    // Restore draft when browsing history within onboarding (do not wipe on back to step 1)
    loadOnboardingFromStorage();
    await loadStep(currentStep);
});

// Clear storage when user leaves the onboarding page (browser navigation, close tab, etc.)
window.addEventListener('beforeunload', function() {
    // Only clear if navigating away from onboarding (not just reloading)
    if (!window.location.pathname.includes('onboarding.php')) {
        clearOnboardingStorage();
    }
});

// Also clear on visibility change if user switches tabs/apps for too long
let visibilityTimeout = null;
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // User switched away - set timeout to clear after 30 minutes of inactivity
        visibilityTimeout = setTimeout(() => {
            if (document.hidden) {
                clearOnboardingStorage();
            }
        }, 30 * 60 * 1000); // 30 minutes
    } else {
        // User came back - clear the timeout
        if (visibilityTimeout) {
            clearTimeout(visibilityTimeout);
            visibilityTimeout = null;
        }
    }
});
</script>
</body>
</html>
