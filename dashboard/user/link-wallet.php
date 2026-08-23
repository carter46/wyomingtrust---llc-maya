<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$db = getDatabase();
$stmt = $db->prepare('SELECT wallet_link_use_modal, wallet_link_url FROM site_settings WHERE id = 1 LIMIT 1');
$stmt->execute();
$walletSettings = $stmt->fetch(PDO::FETCH_ASSOC);

$useModal = isset($walletSettings['wallet_link_use_modal']) ? (int) $walletSettings['wallet_link_use_modal'] : 1;
$walletLinkUrl = $walletSettings['wallet_link_url'] ?? '';

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$page_title = 'Link Wallet | WyomingTrust';
$active_nav = $trustIdParam > 0 ? 'trusts' : '';
$extra_styles = '.card-shadow { box-shadow: 0 4px 20px rgba(4, 22, 39, 0.05); }';

include __DIR__ . '/includes/layout.php';
?>

<section class="w-full min-w-0">
<?php if ($trustIdParam > 0 && $coinKeyParam !== ''): ?>
<a href="asset-detail.php?coin_key=<?php echo escape_html($coinKeyParam); ?>&trust_id=<?php echo $trustIdParam; ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back to Asset
</a>
<?php elseif ($trustIdParam > 0): ?>
<a href="manage-trust.php?id=<?php echo $trustIdParam; ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back to LLC
</a>
<?php endif; ?>

<div class="mb-6">
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Link Your Wallet</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Connect an external wallet to sync balances and manage your digital assets securely.</p>
</div>

<div class="bg-surface-container-low border border-outline-variant rounded-2xl p-4 sm:p-6 mb-6">
<div class="flex items-start gap-3">
<?php echo wt_icon('shield', 'text-secondary flex-shrink-0'); ?>
<div class="text-sm text-on-surface">
<p class="font-semibold mb-2">Security Information:</p>
<ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-on-surface-variant">
<li>Wallet data is encrypted using AES-256-CBC encryption</li>
<li>We never store your private keys or seed phrases</li>
<li>All wallet information is encrypted at rest</li>
<li>Connections are non-custodial — you remain in control</li>
</ul>
</div>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant card-shadow p-6 sm:p-10 mb-8">
<div class="flex flex-col items-center text-center">
<div class="relative mb-6">
<div class="w-20 h-20 bg-secondary/10 rounded-full flex items-center justify-center">
<?php echo wt_icon('wallet', 'text-secondary w-10 h-10'); ?>
</div>
<div class="absolute -bottom-1 -right-1 w-8 h-8 bg-surface-container-lowest rounded-full border-4 border-surface-container-lowest flex items-center justify-center shadow-sm">
<?php echo wt_icon('lock', 'text-primary w-4 h-4'); ?>
</div>
</div>
<h2 class="font-headline-md text-headline-md text-primary mb-2">Connect Your Wallet</h2>
<p class="font-body-md text-on-surface-variant max-w-md mb-8">
Securely link MetaMask, Coinbase Wallet, Trust Wallet, or WalletConnect to access your funds.
</p>
<button id="connectWalletBtn" type="button" onclick="openWalletModal()" class="w-full sm:w-auto min-w-[240px] inline-flex items-center justify-center gap-2 py-4 px-8 bg-primary text-on-primary text-base font-bold rounded-xl hover:bg-primary/90 transition-all group">
<span>Connect Wallet</span>
<?php echo wt_icon('arrow-forward', 'w-5 h-5 group-hover:translate-x-1 transition-transform'); ?>
</button>
</div>
</div>

<section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
<div class="flex flex-col gap-2 rounded-2xl p-5 border border-outline-variant bg-surface-container-lowest card-shadow">
<div class="flex items-center gap-2 text-secondary mb-1">
<?php echo wt_icon('shield', 'w-5 h-5'); ?>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Privacy</p>
</div>
<p class="text-primary text-sm font-semibold">Non-custodial and secure</p>
</div>
<div class="flex flex-col gap-2 rounded-2xl p-5 border border-outline-variant bg-surface-container-lowest card-shadow">
<div class="flex items-center gap-2 text-secondary mb-1">
<?php echo wt_icon('wallet', 'w-5 h-5'); ?>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Supported</p>
</div>
<p class="text-primary text-sm font-semibold">20+ wallet providers</p>
</div>
<div class="flex flex-col gap-2 rounded-2xl p-5 border border-outline-variant bg-surface-container-lowest card-shadow">
<div class="flex items-center gap-2 text-secondary mb-1">
<?php echo wt_icon('refresh', 'w-5 h-5'); ?>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Sync</p>
</div>
<p class="text-primary text-sm font-semibold">Fast balance updates</p>
</div>
</section>
</section>

<?php if ($useModal): ?>
<div id="walletModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
<div class="bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant max-w-md w-full max-h-[90vh] overflow-y-auto">
<div class="p-6 border-b border-outline-variant flex items-center justify-between">
<h3 class="font-headline-md text-headline-md text-primary">Select Wallet</h3>
<button type="button" onclick="closeWalletModal()" class="p-2 hover:bg-surface-container-low rounded-lg transition-colors text-on-surface-variant">
<?php echo wt_icon('close', 'w-5 h-5'); ?>
</button>
</div>
<div class="p-6 space-y-3">
<div class="p-4 bg-surface-container-low border border-outline-variant rounded-xl mb-4">
<p class="text-xs text-on-surface font-semibold mb-2">Before you connect:</p>
<ul class="text-xs text-on-surface-variant space-y-1 list-disc pl-4">
<li>Only connect wallets you own and control</li>
<li>Never share your seed phrase with anyone</li>
<li>Review permissions before approving</li>
</ul>
</div>
<div id="wallet-metamask" onclick="selectWallet('metamask')" class="flex items-center justify-between p-4 rounded-xl bg-secondary/10 border-2 border-secondary shadow-sm cursor-pointer hover:bg-secondary/15 transition-colors">
<div class="flex items-center gap-3">
<div class="size-10 rounded-full bg-surface-container-lowest p-1 flex items-center justify-center">
<img class="size-full object-contain" alt="MetaMask logo" src="<?php echo escape_html(asset_url('Storage/images/metamask-logo.png')); ?>"/>
</div>
<div class="text-left">
<span class="text-primary font-semibold text-base block">MetaMask</span>
<span class="text-xs text-on-surface-variant">Browser Extension</span>
</div>
</div>
<span id="metamask-selected" class="bg-secondary text-on-secondary text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider hidden">Selected</span>
</div>
<div id="wallet-coinbase" onclick="selectWallet('coinbase')" class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-surface-container-low transition-colors">
<div class="size-10 rounded-full bg-surface-container-lowest p-1 flex items-center justify-center">
<?php echo wt_icon('wallet', 'text-secondary w-6 h-6'); ?>
</div>
<div class="text-left flex-1">
<span class="text-primary font-medium text-base block">Coinbase Wallet</span>
<span class="text-xs text-on-surface-variant">Mobile &amp; Extension</span>
</div>
</div>
<div id="wallet-trust" onclick="selectWallet('trust')" class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-surface-container-low transition-colors">
<div class="size-10 rounded-full bg-surface-container-lowest p-1 flex items-center justify-center">
<?php echo wt_icon('shield', 'text-primary w-6 h-6'); ?>
</div>
<div class="text-left flex-1">
<span class="text-primary font-medium text-base block">Trust Wallet</span>
<span class="text-xs text-on-surface-variant">Mobile App</span>
</div>
</div>
<div id="wallet-walletconnect" onclick="selectWallet('walletconnect')" class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-surface-container-low transition-colors">
<div class="size-10 rounded-full bg-surface-container-lowest p-1 flex items-center justify-center">
<?php echo wt_icon('refresh', 'text-secondary w-6 h-6'); ?>
</div>
<div class="text-left flex-1">
<span class="text-primary font-medium text-base block">WalletConnect</span>
<span class="text-xs text-on-surface-variant">Universal Protocol</span>
</div>
</div>
</div>
<div class="p-6 border-t border-outline-variant">
<button id="connectBtn" type="button" onclick="linkWallet()" class="w-full py-3 bg-primary text-on-primary text-base font-bold rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
Connect
</button>
</div>
</div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/modal.php'; ?>

<script>
const wallets = {
    metamask: { name: 'MetaMask', iconUrl: <?php echo json_encode(asset_url('Storage/images/metamask-logo.png')); ?> },
    coinbase: { name: 'Coinbase Wallet' },
    trust: { name: 'Trust Wallet' },
    walletconnect: { name: 'WalletConnect' }
};

const returnTrustId = <?php echo $trustIdParam; ?>;
const returnCoinKey = <?php echo json_encode($coinKeyParam); ?>;
let selectedWallet = 'metamask';

function closeModal() {
    const modal = document.getElementById('customModal');
    if (modal) modal.classList.add('hidden');
}

function showAlertModal(title, message, type = 'info') {
    return new Promise((resolve) => {
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        document.getElementById('modalInput').classList.add('hidden');
        cancelBtn.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = 'OK';
        if (type === 'success') {
            setModalIcon('check-circle', 'text-deep-forest text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-deep-forest/10 sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg px-4 py-2 bg-primary text-on-primary font-bold sm:ml-3 sm:w-auto sm:text-sm';
        } else if (type === 'error') {
            setModalIcon('error', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg px-4 py-2 bg-error text-on-primary font-bold sm:ml-3 sm:w-auto sm:text-sm';
        } else {
            setModalIcon('info', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg px-4 py-2 bg-primary text-on-primary font-bold sm:ml-3 sm:w-auto sm:text-sm';
        }
        confirmBtn.onclick = () => { closeModal(); resolve(true); };
        modal.classList.remove('hidden');
    });
}

function getReturnUrl() {
    if (returnTrustId > 0 && returnCoinKey) {
        return `asset-detail.php?coin_key=${encodeURIComponent(returnCoinKey)}&trust_id=${returnTrustId}`;
    }
    if (returnTrustId > 0) {
        return `manage-trust.php?id=${returnTrustId}`;
    }
    return 'dashboard.php';
}

function openWalletModal() {
    <?php if ($useModal): ?>
    const modal = document.getElementById('walletModal');
    if (modal) {
        modal.classList.remove('hidden');
        selectWallet('metamask');
    }
    <?php else: ?>
    window.location.href = <?php echo json_encode($walletLinkUrl ?: '#'); ?>;
    <?php endif; ?>
}

function closeWalletModal() {
    <?php if ($useModal): ?>
    const modal = document.getElementById('walletModal');
    if (modal) modal.classList.add('hidden');
    <?php endif; ?>
}

function selectWallet(walletId) {
    selectedWallet = walletId;
    ['metamask', 'coinbase', 'trust', 'walletconnect'].forEach((id) => {
        const walletEl = document.getElementById('wallet-' + id);
        const selectedBadge = document.getElementById(id + '-selected');
        if (walletEl) {
            walletEl.className = 'flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-surface-container-low transition-colors';
        }
        if (selectedBadge) selectedBadge.classList.add('hidden');
    });
    const selectedEl = document.getElementById('wallet-' + walletId);
    const selectedBadge = document.getElementById(walletId + '-selected');
    if (selectedEl) {
        selectedEl.className = 'flex items-center justify-between p-4 rounded-xl bg-secondary/10 border-2 border-secondary shadow-sm cursor-pointer hover:bg-secondary/15 transition-colors';
    }
    if (selectedBadge) selectedBadge.classList.remove('hidden');
}

async function linkWallet() {
    const wallet = wallets[selectedWallet];
    const connectBtn = document.getElementById('connectBtn');
    if (connectBtn) {
        connectBtn.disabled = true;
        connectBtn.textContent = 'Connecting...';
    }
    try {
        const tokenResponse = await fetch('../../api/session.php');
        const tokenData = await tokenResponse.json();
        const csrfToken = tokenData.csrf_token || null;
        const response = await fetch('../../api/user/wallets.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken || ''
            },
            body: JSON.stringify({
                wallet_type: selectedWallet,
                wallet_name: wallet.name,
                wallet_data: { connected: true, timestamp: new Date().toISOString() },
                csrf_token: csrfToken
            })
        });
        const data = await response.json();
        closeWalletModal();
        if (data.success) {
            await showAlertModal('Wallet Linked', 'Your wallet was connected successfully.', 'success');
            window.location.href = getReturnUrl();
        } else {
            await showAlertModal('Connection Failed', data.message || 'Failed to link wallet. Please try again.', 'error');
            if (connectBtn) {
                connectBtn.disabled = false;
                connectBtn.textContent = 'Connect';
            }
        }
    } catch (error) {
        console.error('Error linking wallet:', error);
        closeWalletModal();
        await showAlertModal('Error', 'An error occurred while linking your wallet. Please try again.', 'error');
        if (connectBtn) {
            connectBtn.disabled = false;
            connectBtn.textContent = 'Connect';
        }
    }
}

<?php if ($useModal): ?>
document.addEventListener('click', function(event) {
    const modal = document.getElementById('walletModal');
    if (modal && event.target === modal) closeWalletModal();
});
<?php endif; ?>
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
