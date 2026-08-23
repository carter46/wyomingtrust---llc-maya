<?php
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Site Settings';
require_once __DIR__ . '/includes/layout.php';

function renderSettingsContent() {
?>

<section class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6 sm:mb-8">
    <h2 class="text-xl sm:text-2xl font-bold text-navy-900 dark:text-white mb-2 flex items-center gap-2">
        <span class="material-icons-outlined text-primary text-lg sm:text-xl">settings</span>
        <span>Site Settings</span>
    </h2>
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Update branding, site options, and file uploads in one place.</p>

    <form id="siteSettingsForm" class="space-y-6" onsubmit="return false;">
        <!-- Branding -->
        <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
            <h3 class="text-base sm:text-lg font-semibold text-navy-900 dark:text-white mb-4">Branding</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Site Name</label>
                    <input type="text" id="siteName" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="WyomingTrust">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Tagline</label>
                    <input type="text" id="tagline" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Secure Your Digital Legacy">
                </div>
            </div>
        </div>

        <!-- Logo & Favicon -->
        <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
            <h3 class="text-base sm:text-lg font-semibold text-navy-900 dark:text-white mb-4">Logo &amp; Favicon</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Site Logo</label>
                    <div id="logoPreview" class="mb-3 min-h-[4rem]"></div>
                    <input type="file" id="logoFile" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-navy-900 file:font-semibold">
                    <p class="text-xs text-slate-500 mt-1">PNG, JPEG, or WEBP. Max 2MB. Leave empty to keep current logo.</p>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Site Favicon</label>
                    <div id="faviconPreview" class="mb-3 min-h-[4rem]"></div>
                    <input type="file" id="faviconFile" name="favicon" accept="image/png,image/jpeg,image/jpg,image/webp,image/x-icon,image/svg+xml,.ico" class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-navy-900 file:font-semibold">
                    <p class="text-xs text-slate-500 mt-1">PNG, JPEG, WEBP, ICO, or SVG. Max 500KB. Recommended 32×32px.</p>
                </div>
            </div>
        </div>

        <!-- Email Verification -->
        <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                <div class="flex-1 min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-navy-900 dark:text-white mb-2">Email Verification</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Require users to verify their email before accessing the dashboard.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 self-start sm:self-center">
                    <input type="checkbox" id="emailVerificationToggle" class="sr-only peer" onchange="updateEmailVerificationStatus(this.checked)">
                    <div class="w-12 h-6 sm:w-14 sm:h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 sm:after:h-6 sm:after:w-6 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                </label>
            </div>
            <div id="emailVerificationStatus" class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-400"></div>
        </div>

        <!-- Wallet Link -->
        <div class="pb-2">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4 mb-4">
                <div class="flex-1 min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-navy-900 dark:text-white mb-2">Wallet Link Modal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Show wallet modal when enabled, or redirect to a custom URL when disabled.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 self-start sm:self-center">
                    <input type="checkbox" id="walletLinkUseModalToggle" class="sr-only peer" onchange="toggleWalletLinkModal(this.checked, false)">
                    <div class="w-12 h-6 sm:w-14 sm:h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 sm:after:h-6 sm:after:w-6 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                </label>
            </div>
            <div id="walletLinkUrlContainer" class="hidden">
                <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Wallet Link URL</label>
                <input type="url" id="walletLinkUrl" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="https://example.com/wallet-connect">
            </div>
        </div>

        <div id="settingsMessage"></div>

        <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-700">
            <button type="button" id="saveSettingsBtn" onclick="saveAllSettings()" class="bg-primary text-navy-900 px-6 py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 transition-opacity w-full sm:w-auto">
                Save Site Settings
            </button>
        </div>
    </form>
</section>

<script src="includes/modal.js"></script>
<script>
function updateEmailVerificationStatus(enabled) {
    const statusDiv = document.getElementById('emailVerificationStatus');
    if (!statusDiv) return;
    if (enabled) {
        statusDiv.innerHTML = '<div class="flex items-center gap-2 text-green-600 dark:text-green-400"><span class="material-icons-outlined text-sm">check_circle</span><span>Email verification is <strong>ENABLED</strong></span></div>';
    } else {
        statusDiv.innerHTML = '<div class="flex items-center gap-2 text-amber-600 dark:text-amber-400"><span class="material-icons-outlined text-sm">warning</span><span>Email verification is <strong>DISABLED</strong></span></div>';
    }
}

function toggleWalletLinkModal(enabled, saveImmediately) {
    const walletUrlContainer = document.getElementById('walletLinkUrlContainer');
    const walletUrlInput = document.getElementById('walletLinkUrl');
    if (enabled) {
        walletUrlContainer.classList.add('hidden');
        if (walletUrlInput) walletUrlInput.disabled = true;
    } else {
        walletUrlContainer.classList.remove('hidden');
        if (walletUrlInput) walletUrlInput.disabled = false;
    }
}

function renderLogoPreview(path) {
    const el = document.getElementById('logoPreview');
    if (!el) return;
    el.innerHTML = path
        ? `<img src="../../${path}" alt="Site Logo" class="max-h-24 max-w-48 object-contain border border-slate-200 dark:border-slate-600 rounded-lg p-2">`
        : '<p class="text-sm text-slate-500">No logo uploaded</p>';
}

function renderFaviconPreview(path) {
    const el = document.getElementById('faviconPreview');
    if (!el) return;
    el.innerHTML = path
        ? `<img src="../../${path}" alt="Site Favicon" class="h-16 w-16 object-contain border border-slate-200 dark:border-slate-600 rounded-lg p-2">`
        : '<p class="text-sm text-slate-500">No favicon uploaded</p>';
}

async function loadSettings() {
    try {
        const response = await fetch('../../api/admin/settings.php', { credentials: 'same-origin' });
        const data = await response.json();
        if (!data.success || !data.settings) return;

        const settings = data.settings;
        document.getElementById('siteName').value = settings.site_name || '';
        document.getElementById('tagline').value = settings.tagline || '';

        const emailToggle = document.getElementById('emailVerificationToggle');
        if (emailToggle) {
            emailToggle.checked = settings.require_email_verification == 1;
            updateEmailVerificationStatus(emailToggle.checked);
        }

        const walletModalToggle = document.getElementById('walletLinkUseModalToggle');
        if (walletModalToggle) {
            const useModal = settings.wallet_link_use_modal == 1;
            walletModalToggle.checked = useModal;
            toggleWalletLinkModal(useModal, false);
        }
        const walletUrlInput = document.getElementById('walletLinkUrl');
        if (walletUrlInput) walletUrlInput.value = settings.wallet_link_url || '';

        renderLogoPreview(settings.logo || '');
        renderFaviconPreview(settings.favicon || '');
    } catch (error) {
        console.error('Failed to load settings:', error);
        showToast('Failed to load settings', 'error');
    }
}

async function uploadSettingFile(fieldName, file) {
    const formData = new FormData();
    formData.append(fieldName, file);
    const response = await fetch('../../api/admin/settings.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    });
    return response.json();
}

async function saveAllSettings() {
    const saveBtn = document.getElementById('saveSettingsBtn');
    const messageDiv = document.getElementById('settingsMessage');
    messageDiv.innerHTML = '';
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const siteName = document.getElementById('siteName').value;
    const tagline = document.getElementById('tagline').value;
    const emailVerification = document.getElementById('emailVerificationToggle').checked ? 1 : 0;
    const useModal = document.getElementById('walletLinkUseModalToggle').checked ? 1 : 0;
    const walletUrl = document.getElementById('walletLinkUrl').value;
    const logoFile = document.getElementById('logoFile').files[0];
    const faviconFile = document.getElementById('faviconFile').files[0];

    if (logoFile && logoFile.size > 2 * 1024 * 1024) {
        messageDiv.innerHTML = '<div class="text-red-600 text-sm">Logo file must be less than 2MB</div>';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Site Settings';
        return;
    }
    if (faviconFile && faviconFile.size > 500 * 1024) {
        messageDiv.innerHTML = '<div class="text-red-600 text-sm">Favicon file must be less than 500KB</div>';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Site Settings';
        return;
    }

    try {
        const patchRes = await fetch('../../api/admin/settings.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                site_name: siteName,
                tagline: tagline,
                require_email_verification: emailVerification,
                wallet_link_use_modal: useModal,
                wallet_link_url: walletUrl
            })
        });
        const patchData = await patchRes.json();
        if (!patchData.success) {
            throw new Error(patchData.message || 'Failed to save settings');
        }

        if (logoFile) {
            const logoData = await uploadSettingFile('logo', logoFile);
            if (!logoData.success) throw new Error(logoData.message || 'Failed to upload logo');
            renderLogoPreview(logoData.path);
            document.getElementById('logoFile').value = '';
        }

        if (faviconFile) {
            const favData = await uploadSettingFile('favicon', faviconFile);
            if (!favData.success) throw new Error(favData.message || 'Failed to upload favicon');
            renderFaviconPreview(favData.path);
            document.getElementById('faviconFile').value = '';
        }

        updateEmailVerificationStatus(emailVerification === 1);
        messageDiv.innerHTML = '<div class="text-green-600 dark:text-green-400 text-sm">All site settings saved successfully.</div>';
        showToast('Site settings saved successfully', 'success');
    } catch (error) {
        console.error('Failed to save settings:', error);
        messageDiv.innerHTML = `<div class="text-red-600 text-sm">${error.message || 'Failed to save settings'}</div>`;
        showToast(error.message || 'Failed to save settings', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Site Settings';
    }
}

document.addEventListener('DOMContentLoaded', loadSettings);
</script>

<?php
}

renderAdminLayout($page_title, 'settings', 'renderSettingsContent');
