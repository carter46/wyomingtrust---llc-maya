<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$trustId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userName = $_SESSION['user_name'] ?? 'User';

if ($trustId <= 0) {
    $page_title = 'LLC Management | WyomingTrust';
    $active_nav = 'trusts';
    include __DIR__ . '/includes/layout.php';
    ?>

<section class="flex flex-wrap justify-between items-end gap-4">
<div>
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">LLC Management</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">View and manage your LLCs.</p>
</div>
<a href="../../onboarding/onboarding.php" class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-primary text-on-primary text-xs sm:text-sm font-bold hover:bg-primary/90 h-8 sm:h-10 transition-colors">
<?php echo wt_icon('add', 'text-xs sm:text-sm'); ?>
Create New LLC
</a>
</section>

<section>
<div id="trustsList" class="space-y-4">
<div class="text-center py-10 text-on-surface-variant">Loading LLCs...</div>
</div>
</section>

<?php include __DIR__ . '/includes/modal.php'; ?>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function renderListTrustAction(trust) {
    const meta = trust.service_meta || {};
    if (meta.is_irrevocable) {
        return '';
    }
    const status = (trust.status || 'pending').toString().toLowerCase();
    if (status === 'pending') {
        return '';
    }
    const fee = parseFloat(meta.liquidation_fee || 0);
    const label = meta.allows_liquidation ? `Liquidate${fee > 0 ? ' ($' + fee.toFixed(2) + ')' : ''}` : 'Delete';
    return `<button onclick="liquidateTrustFromList(${trust.id}, ${fee})" class="px-4 py-2 rounded-lg bg-error/10 text-error border border-error/30 font-bold hover:bg-error hover:text-on-primary h-10 flex items-center">${escapeHtml(label)}</button>`;
}

let csrfToken = null;

async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    try {
        const res = await fetch('../../api/session.php', { credentials: 'same-origin' });
        const data = await res.json();
        csrfToken = data.csrf_token || null;
    } catch (error) {
        console.error('Failed to get CSRF token:', error);
    }
    return csrfToken;
}

async function submitTrustLiquidation(targetTrustId) {
    const token = await getCsrfToken();
    const res = await fetch('../../api/user/trusts.php', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': token || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id: targetTrustId, liquidate: true, csrf_token: token }),
    });
    const data = await res.json();
    if (!data.success && data.redirect_checkout) {
        window.location.href = `checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`;
        return null;
    }
    return data;
}

async function requiresTrustLiquidationCheckout(targetTrustId, fee) {
    if (!(parseFloat(fee) > 0)) return false;
    try {
        const res = await fetch(`../../api/user/checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`, { credentials: 'same-origin' });
        const data = await res.json();
        if (data.success && data.has_fee && !data.already_submitted) {
            window.location.href = `checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`;
            return true;
        }
    } catch (error) {
        console.error('Liquidation checkout check failed:', error);
    }
    return false;
}

async function liquidateTrustFromList(trustId, fee) {
    const feeText = fee > 0 ? ` You will be taken to checkout to pay the $${fee.toFixed(2)} liquidation fee.` : '';
    const confirmed = await showConfirmModal(
        'Liquidate LLC',
        `This will begin the LLC liquidation process.${feeText} This action cannot be easily undone.`,
        fee > 0 ? 'Continue to Checkout' : 'Liquidate LLC',
        'Cancel',
        'danger'
    );
    if (!confirmed) return;
    if (await requiresTrustLiquidationCheckout(trustId, fee)) return;
    try {
        const data = await submitTrustLiquidation(trustId);
        if (!data) return;
        if (data.success) {
            await showAlertModal('Liquidation Started', 'Your liquidation request has been submitted and is pending processing.', 'success');
            loadTrusts();
        } else if (data.payment_pending) {
            await showAlertModal('Payment Pending', data.message || 'Liquidation fee payment is pending approval.', 'warning');
        } else {
            await showAlertModal('Error', data.message || 'Failed to liquidate LLC', 'error');
        }
    } catch (e) {
        console.error(e);
        await showAlertModal('Error', 'Error processing liquidation', 'error');
    }
}

async function loadTrusts() {
    try {
        const res = await fetch('../../api/user/trusts.php');
        const data = await res.json();
        const container = document.getElementById('trustsList');
        if (!data.success || !data.trusts) {
            container.innerHTML = '<div class="text-center py-10 text-error">Failed to load LLCs</div>';
            return;
        }
        if (data.trusts.length === 0) {
            container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No LLCs yet. <a class="text-secondary font-semibold hover:underline" href="../../onboarding/onboarding.php">Create your first LLC</a></div>';
            return;
        }
        container.innerHTML = data.trusts.map(t => {
            const trustName = t.trust_name || t.service_name || 'Untitled LLC';
            const serviceName = t.service_name || 'LLC';
            const status = (t.status || 'pending').toString();
            const createdAt = t.created_at ? new Date(t.created_at).toLocaleDateString() : '';
            const bens = Array.isArray(t.beneficiaries) ? t.beneficiaries.length :
                        (Array.isArray(t.trust_data?.beneficiaries) ? t.trust_data.beneficiaries.length : 0);
            const showServiceBadge = t.trust_name && t.trust_name !== serviceName;
            return `
                <div class="p-5 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-xl font-black text-primary">${escapeHtml(trustName)}</p>
                                ${showServiceBadge ? `<span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-secondary/10 text-secondary">${escapeHtml(serviceName)}</span>` : ''}
                                ${llcStatusBadge(status)}
                            </div>
                            <p class="text-xs text-on-surface-variant mt-1">Share Holders: <strong>${bens}</strong> · Created: ${escapeHtml(createdAt)}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="manage-trust.php?id=${t.id}" class="px-4 py-2 rounded-lg bg-primary text-on-primary font-bold hover:bg-primary/90 h-10 flex items-center">Manage</a>
                            ${renderListTrustAction(t)}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (e) {
        console.error(e);
        document.getElementById('trustsList').innerHTML = '<div class="text-center py-10 text-error">Error loading LLCs</div>';
    }
}

function llcStatusBadge(status) {
    const raw = String(status || 'pending').toLowerCase();
    let label = raw.replace(/_/g, ' ');
    let classes = 'bg-surface-container text-on-surface-variant';
    if (raw === 'pending') {
        classes = 'bg-amber-100 text-amber-800';
        label = 'pending';
    } else if (raw === 'active' || raw === 'approved') {
        classes = 'bg-deep-forest/15 text-deep-forest';
        label = raw === 'approved' ? 'approved' : 'active';
    } else if (raw === 'rejected' || raw === 'denied') {
        classes = 'bg-error-container/40 text-error';
        label = 'rejected';
    } else if (raw === 'liquidated' || raw === 'inactive' || raw === 'suspended') {
        classes = 'bg-surface-container text-on-surface-variant';
    }
    return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${classes}">${escapeHtml(label)}</span>`;
}

var modalResolve = null;
var modalReject = null;

function showConfirmModal(title, message, confirmText = 'Confirm', cancelText = 'Cancel', type = 'warning') {
    return new Promise((resolve, reject) => {
        modalResolve = resolve;
        modalReject = reject;
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');

        inputDiv.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        if (type === 'danger') {
            setModalIcon('warning', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-error text-base font-bold text-on-primary hover:bg-error/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm';
        } else {
            setModalIcon('help', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-bold text-on-primary hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm';
        }

        confirmBtn.onclick = () => {
            closeModal({ reject: false });
            resolve(true);
        };

        modal.classList.remove('hidden');
    });
}

function showAlertModal(title, message, type = 'info') {
    return new Promise((resolve) => {
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');

        inputDiv.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = 'OK';

        if (type === 'success') {
            setModalIcon('check-circle', 'text-deep-forest text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-deep-forest/10 sm:mx-0 sm:h-10 sm:w-10';
        } else if (type === 'error') {
            setModalIcon('error', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
        } else {
            setModalIcon('info', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
        }

        confirmBtn.onclick = () => {
            closeModal({ reject: false });
            resolve();
        };

        modal.classList.remove('hidden');
    });
}

function closeModal(options = {}) {
    const reject = options.reject !== false;
    const modal = document.getElementById('customModal');
    const cancelBtn = document.getElementById('modalCancelBtn');
    modal.classList.add('hidden');
    cancelBtn.classList.remove('hidden');
    if (reject && modalReject) {
        modalReject(false);
    }
    modalReject = null;
    modalResolve = null;
}

document.addEventListener('DOMContentLoaded', loadTrusts);
</script>

<?php
    include __DIR__ . '/includes/layout-footer.php';
    exit;
}

$page_title = 'Manage LLC | WyomingTrust';
$active_nav = 'trusts';
$extra_styles = '
@media print { aside, header, .no-print { display: none !important; } body { background: white; color: black; } }
.card-shadow { box-shadow: 0 4px 20px rgba(4, 22, 39, 0.05); }
.bg-primary-fixed { background-color: #d2e4fb; }
.text-on-primary-fixed-variant { color: #38485a; }
.text-on-error-container { color: #93000a; }
.border-surface-container-high { border-color: #e7e8e9; }
.bg-background { background-color: #f8f9fa; }
.crypto-action-btn { white-space: nowrap; }
@media (max-width: 639px) {
    .crypto-layout-card { padding: 1rem !important; }
    .crypto-beneficiary-card { padding: 1rem !important; min-width: 0; overflow: hidden; }
}
';
include __DIR__ . '/includes/layout.php';
?>

<div id="pendingRegistrationBanner" class="hidden mb-8 rounded-xl border border-amber-400/50 bg-amber-50 p-4">
<p class="text-sm font-bold text-primary">Your LLC registration is pending approval.</p>
<p class="text-xs text-on-surface-variant mt-1">This LLC is not operational yet. You can review details below. Most actions will unlock after your registration is approved.</p>
</div>

<div id="standardTrustLayout" class="space-y-8">
<section class="flex flex-wrap justify-between items-end gap-4 pb-6 border-b border-outline-variant">
<div class="flex flex-col gap-2">
<div class="flex items-center gap-2">
<span id="trustTypeBadge" class="bg-secondary/10 text-secondary text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded">Loading...</span>
</div>
<p id="trustName" class="font-headline-lg text-headline-lg text-primary leading-tight">Loading...</p>
<p id="trustId" class="text-on-surface-variant text-sm font-mono font-medium">ID: Loading...</p>
</div>
<div class="flex flex-wrap gap-2 items-center no-print w-full sm:w-auto">
<button onclick="window.location.href='../../onboarding/onboarding.php'" class="flex items-center justify-center rounded-lg h-8 sm:h-10 px-2.5 sm:px-4 bg-primary text-on-primary text-xs sm:text-sm font-bold gap-1 sm:gap-2 hover:bg-primary/90 transition-all">
<?php echo wt_icon('add', 'text-xs sm:text-sm'); ?>
<span>Create New LLC</span>
</button>
<button onclick="window.location.href='manage-trust.php'" class="flex items-center justify-center rounded-lg h-8 sm:h-10 px-2.5 sm:px-4 bg-primary-container text-on-primary text-xs sm:text-sm font-bold gap-1 sm:gap-2 hover:bg-primary transition-all">
<?php echo wt_icon('arrow-back', 'text-xs sm:text-sm'); ?>
<span>Back to LLCs</span>
</button>
</div>
</section>

<section id="trustMetricsSection" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
<div id="portfolioAssetsCard" class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Portfolio Assets</p>
<div class="flex items-baseline gap-2 min-w-0">
<div class="dashboard-metric-value-wrap min-w-0 flex-1">
<p id="portfolioAssets" class="dashboard-metric-value text-primary" data-fit-max="24" data-fit-min="12">0/0</p>
</div>
<span id="portfolioAllocation" class="text-xs text-on-surface-variant shrink-0">0% allocation</span>
</div>
</div>
<div id="totalValueCard" class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Total Value</p>
<div class="dashboard-metric-value-wrap">
<p id="totalValue" class="dashboard-metric-value text-primary tracking-tight" data-fit-max="28" data-fit-min="11">$0.00</p>
</div>
</div>
<div id="declaredValueCard" class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm hidden">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Declared Value</p>
<div class="dashboard-metric-value-wrap">
<p id="declaredUnverifiedValue" class="dashboard-metric-value text-primary tracking-tight" data-fit-max="28" data-fit-min="11">$0.00</p>
</div>
<p id="declaredUnverifiedHint" class="text-xs text-amber-700 font-semibold">Unverified — not yet deposited</p>
</div>
<div id="verifiedFundedCard" class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm hidden">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Verified / Funded Value</p>
<div class="dashboard-metric-value-wrap">
<p id="verifiedFundedValue" class="dashboard-metric-value text-primary tracking-tight" data-fit-max="28" data-fit-min="11">$0.00</p>
</div>
<p class="text-xs text-on-surface-variant">Verified assets</p>
</div>
<div class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Share Holders</p>
<div class="dashboard-metric-value-wrap">
<p id="beneficiaryCount" class="dashboard-metric-value text-primary" data-fit-max="28" data-fit-min="14">0</p>
</div>
</div>
<div class="dashboard-metric-card flex flex-col gap-2 rounded-xl p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Status</p>
<div class="flex items-center gap-2">
<?php echo wt_icon('shield', 'text-secondary text-xl'); ?>
<p id="trustStatus" class="text-primary text-lg font-bold">Loading...</p>
</div>
</div>
</section>

<section class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 sm:gap-3 mb-8 bg-surface-container-low p-3 sm:p-4 rounded-xl border border-outline-variant no-print">
<p class="text-primary text-xs sm:text-sm font-bold shrink-0">Quick Actions:</p>
<div class="flex flex-wrap gap-2">
<button onclick="exportTrustReport()" class="flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-xs sm:text-sm font-semibold text-primary hover:bg-secondary/10 transition-colors">
<?php echo wt_icon('share', 'w-3.5 h-3.5 sm:w-4 sm:h-4 text-secondary shrink-0'); ?>
<span>Export Report</span>
</button>
<button onclick="printTrustDetails()" class="flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-xs sm:text-sm font-semibold text-primary hover:bg-secondary/10 transition-colors">
<?php echo wt_icon('print', 'w-3.5 h-3.5 sm:w-4 sm:h-4 text-secondary shrink-0'); ?>
<span>Print Details</span>
</button>
<button onclick="shareWithAdvisor()" class="flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-xs sm:text-sm font-semibold text-primary hover:bg-secondary/10 transition-colors">
<?php echo wt_icon('group', 'w-3.5 h-3.5 sm:w-4 sm:h-4 text-secondary shrink-0'); ?>
<span>Share with Advisor</span>
</button>
</div>
</section>

<section class="mb-8">
<h2 class="font-headline-md text-headline-md text-primary pb-4">LLC Settings</h2>
<div class="flex flex-col gap-3">
<div id="businessInfoCard" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm hidden">
<p class="text-primary text-base font-bold mb-3">Business Information</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
<div>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Company Name</p>
<p id="bizCompanyName" class="text-primary font-semibold">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Business Ending</p>
<p id="bizBusinessEnding" class="text-primary font-semibold">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Display Name</p>
<p id="bizDisplayName" class="text-primary font-semibold">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Formation State / Jurisdiction</p>
<p id="bizFormationState" class="text-primary font-semibold">—</p>
</div>
<div class="sm:col-span-2" id="bizAssetValueWrap" style="display:none;">
<p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider">Total Asset Value</p>
<p id="bizAssetValue" class="text-primary font-semibold">—</p>
</div>
</div>
</div>
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
<div class="flex flex-col gap-1">
<p class="text-primary text-base font-bold">Edit LLC Name</p>
<p class="text-on-surface-variant text-sm">Modify the official title of this LLC.</p>
</div>
<button onclick="editTrustName()" class="flex min-w-[120px] cursor-pointer items-center justify-center rounded-lg h-9 px-4 bg-primary text-on-primary text-sm font-bold hover:bg-primary/90 transition-all">
Edit Name
</button>
</div>
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
<div class="flex flex-col gap-1">
<p class="text-primary text-base font-bold">Change Status</p>
<div class="flex items-center gap-2">
<span id="statusDot" class="size-2 bg-outline-variant rounded-full"></span>
<p class="text-on-surface-variant text-sm">Current Status: <span id="statusBadge" class="font-bold text-on-surface">Loading...</span></p>
</div>
</div>
<button id="changeStatusBtn" onclick="changeStatus()" class="flex min-w-[120px] cursor-pointer items-center justify-center rounded-lg h-9 px-4 bg-primary-container text-on-primary text-sm font-bold hover:bg-primary transition-all">
Change Status
</button>
</div>
</div>
</section>

<section class="mb-8" id="cryptoTrustSection" style="display:none;">
<div class="flex justify-between items-center pb-4">
<h2 class="font-headline-md text-headline-md text-primary">Selected Crypto Portfolio</h2>
<div class="flex items-center gap-3">
<a id="addCoinsLinkLegacy" href="#" class="hidden text-secondary text-sm font-bold hover:underline inline-flex items-center gap-1"><?php echo wt_icon('add-circle', 'w-4 h-4'); ?> Add Assets</a>
<a href="assets.php" class="text-secondary text-sm font-bold hover:underline inline-flex items-center gap-1">View All Assets <?php echo wt_icon('arrow-forward', 'w-4 h-4'); ?></a>
</div>
</div>
<div id="entrustedCoinsList" class="flex flex-wrap gap-2 mb-4"></div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
<a href="assets.php" class="flex items-center gap-2 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest hover:border-secondary transition-colors">
<?php echo wt_icon('wallet', 'w-5 h-5 text-secondary'); ?>
<span class="font-semibold text-sm text-primary">Portfolio & Balances</span>
</a>
<a href="receive.php" class="flex items-center gap-2 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest hover:border-secondary transition-colors">
<?php echo wt_icon('receive', 'w-5 h-5 text-secondary'); ?>
<span class="font-semibold text-sm text-primary">Receive Crypto</span>
</a>
<a href="send.php" class="flex items-center gap-2 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest hover:border-secondary transition-colors">
<?php echo wt_icon('send', 'w-5 h-5 text-secondary'); ?>
<span class="font-semibold text-sm text-primary">Send Crypto</span>
</a>
</div>
</section>

<section class="mb-8" id="trustAssetsSection" style="display:none;">
<div class="flex justify-between items-center pb-4">
<h2 class="font-headline-md text-headline-md text-primary">LLC Assets <span id="assetsCountLabel" class="text-on-surface-variant text-base font-normal"></span></h2>
<button type="button" onclick="openAddAssetModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-bold hover:bg-primary/90">
<?php echo wt_icon('add', 'w-4 h-4'); ?>
Add Asset
</button>
</div>
<div id="trustAssetsList" class="flex flex-col gap-3 mb-4">
<div class="text-center py-8 text-on-surface-variant text-sm">Loading assets...</div>
</div>
</section>

<section class="mb-4">
<div class="flex justify-between items-center pb-4">
<h2 class="font-headline-md text-headline-md text-primary">Manage Share Holders</h2>
<div class="flex gap-3 items-center">
<button id="saveChangesBtn" onclick="saveBeneficiaries()" class="hidden px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-bold hover:bg-primary/90">Save Changes</button>
<button onclick="addBeneficiary()" class="text-secondary text-sm font-bold hover:underline flex items-center gap-1">
<?php echo wt_icon('add-circle', 'text-sm'); ?>
Add Share Holder
</button>
</div>
</div>
<div id="beneficiariesContainer" class="flex flex-col gap-4 mb-4">
<div class="text-center py-10 text-on-surface-variant">Loading beneficiaries...</div>
</div>
</section>

<section class="rounded-xl border-2 border-error/20 bg-error-container/30 p-6 mb-12 no-print" id="dangerZoneSection">
<div class="flex items-center gap-3 mb-4">
<?php echo wt_icon('warning', 'w-5 h-5 text-error'); ?>
<h2 class="text-error text-lg font-bold" id="dangerZoneTitle">Danger Zone</h2>
</div>
<p class="text-error/70 text-sm mb-6 max-w-2xl" id="dangerZoneDesc">Actions in this section are permanent and may require legal authorization. Proceed with extreme caution.</p>
<div class="flex flex-wrap gap-4" id="dangerZoneActions">
<button id="suspendTrustBtn" onclick="suspendTrust()" class="px-6 py-2.5 rounded-lg bg-surface-container-lowest border border-error/20 text-error text-sm font-bold hover:bg-error hover:text-on-primary transition-all shadow-sm">
Suspend LLC
</button>
<button onclick="archiveTrust()" id="liquidateTrustBtn" class="px-6 py-2.5 rounded-lg bg-error text-on-primary text-sm font-bold hover:bg-error/90 transition-all shadow-md">
Liquidate LLC
</button>
</div>
</section>

<section id="irrevocableNotice" class="hidden rounded-xl border border-outline-variant bg-surface-container-low p-6">
<?php echo wt_icon('lock', 'w-5 h-5 text-secondary inline-block mr-2'); ?>
<p class="text-sm text-on-surface-variant inline"><strong class="text-primary">Irrevocable Structure:</strong> This LLC cannot be deleted or liquidated. Assets placed here are managed under irrevocable terms.</p>
</section>
</div>

<!-- Smart Contract Trust — Crypto Portfolio Dashboard layout -->
<div id="cryptoTrustLayout" class="hidden space-y-6 sm:space-y-10">
<section class="flex flex-wrap justify-between items-end gap-4 pb-2 border-b border-surface-container-high">
<div class="flex flex-col gap-2">
<div class="flex items-center gap-2">
<span id="cryptoTrustTypeBadge" class="bg-secondary/10 text-secondary text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded">Smart Contract Trust</span>
</div>
<p id="cryptoTrustName" class="font-headline-lg text-headline-lg text-primary leading-tight">Loading...</p>
<p id="cryptoTrustId" class="text-on-surface-variant text-sm font-mono font-medium">ID: Loading...</p>
</div>
<div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center no-print w-full sm:w-auto">
<button type="button" onclick="scrollToCryptoPortfolio()" class="crypto-action-btn flex items-center justify-center rounded-lg h-10 px-3 sm:px-4 bg-primary text-on-primary text-xs sm:text-sm font-bold gap-1.5 hover:bg-primary/90 transition-all">
<?php echo wt_icon('wallet', 'text-sm shrink-0'); ?>
<span>View Assets</span>
</button>
</div>
</section>

<section id="cryptoMetricsSection" class="grid grid-cols-1 md:grid-cols-4 gap-gutter">
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-xl card-shadow border border-surface-container-high">
<p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider mb-2">Portfolio Assets</p>
<div class="dashboard-metric-value-wrap">
<p id="cryptoPortfolioAssets" class="dashboard-metric-value font-headline-lg text-primary" data-fit-max="28" data-fit-min="14">0</p>
</div>
</div>
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-xl card-shadow border border-surface-container-high">
<p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider mb-2">Total Value</p>
<div class="dashboard-metric-value-wrap">
<p id="cryptoTotalValue" class="dashboard-metric-value font-headline-lg text-primary" data-fit-max="28" data-fit-min="11">$0.00</p>
</div>
<p id="cryptoDepositHint" class="hidden text-xs text-on-surface-variant mt-2">Please deposit assets</p>
</div>
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-xl card-shadow border border-surface-container-high">
<p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider mb-2">Share Holders</p>
<div class="flex items-center justify-between gap-2 min-w-0">
<div class="dashboard-metric-value-wrap min-w-0 flex-1">
<p id="cryptoBeneficiaryCount" class="dashboard-metric-value font-headline-lg text-primary" data-fit-max="28" data-fit-min="14">0</p>
</div>
<?php echo wt_icon('group', 'text-secondary w-6 h-6 shrink-0'); ?>
</div>
</div>
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-xl card-shadow border border-surface-container-high">
<p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider mb-2">Status</p>
<div class="flex items-center gap-2">
<span id="cryptoStatusDot" class="w-3 h-3 rounded-full bg-deep-forest animate-pulse"></span>
<p id="cryptoTrustStatus" class="font-headline-lg text-headline-lg text-deep-forest">Loading...</p>
</div>
</div>
</section>

<a id="cryptoPortfolioSection"></a>
<div id="cryptoPortfolioMobileBlock" class="md:hidden bg-surface-container-lowest p-4 rounded-xl card-shadow border border-surface-container-high crypto-layout-card min-w-0">
<div class="flex justify-between items-center gap-2 mb-4">
<h3 class="font-headline-md text-headline-md text-primary">Selected Crypto Portfolio</h3>
<a id="addCoinsLinkMobile" href="#" class="text-secondary text-xs font-bold hover:underline inline-flex items-center gap-1 shrink-0"><?php echo wt_icon('add-circle', 'w-4 h-4'); ?> Add Assets</a>
</div>
<div id="cryptoPortfolioMobileList" class="space-y-3 min-w-0">
<div class="py-8 text-center text-on-surface-variant text-sm">Loading portfolio...</div>
</div>
</div>

<section id="cryptoQuickActions" class="bg-primary-fixed p-3 sm:p-4 rounded-xl flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 sm:gap-gutter no-print">
<span class="font-label-md text-label-md text-on-primary-fixed-variant text-xs sm:text-sm sm:ml-2">Quick Actions:</span>
<div class="flex flex-wrap gap-2">
<button type="button" onclick="exportTrustReport()" class="crypto-action-btn bg-surface-container-lowest text-primary px-3 py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-1.5 hover:bg-surface-container transition-colors border border-outline-variant">
<?php echo wt_icon('share', 'w-4 h-4 shrink-0'); ?> <span>Export</span>
</button>
<button type="button" onclick="printTrustDetails()" class="crypto-action-btn bg-surface-container-lowest text-primary px-3 py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-1.5 hover:bg-surface-container transition-colors border border-outline-variant">
<?php echo wt_icon('print', 'w-4 h-4 shrink-0'); ?> <span>Print</span>
</button>
<button type="button" onclick="shareWithAdvisor()" class="crypto-action-btn bg-surface-container-lowest text-primary px-3 py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-1.5 hover:bg-surface-container transition-colors border border-outline-variant">
<?php echo wt_icon('share', 'w-4 h-4 shrink-0'); ?> <span>Share</span>
</button>
</div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter min-w-0">
<div class="lg:col-span-7 space-y-6 sm:space-y-10 min-w-0">
<div class="bg-surface-container-lowest p-4 sm:p-8 rounded-xl card-shadow border border-surface-container-high crypto-layout-card min-w-0">
<div class="flex justify-between items-center mb-6">
<h3 class="font-headline-md text-headline-md text-primary">LLC Settings</h3>
<?php echo wt_icon('settings', 'text-outline w-6 h-6'); ?>
</div>
<div class="space-y-4">
<div id="cryptoBusinessInfoCard" class="p-3 sm:p-4 bg-background rounded-lg border border-surface-container hidden">
<p class="font-label-md text-label-md text-on-surface-variant text-xs sm:text-sm mb-3 font-bold">Business Information</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
<div>
<p class="text-on-surface-variant text-xs uppercase tracking-wider">Company Name</p>
<p id="cryptoBizCompanyName" class="font-bold text-primary">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs uppercase tracking-wider">Business Ending</p>
<p id="cryptoBizBusinessEnding" class="font-bold text-primary">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs uppercase tracking-wider">Display Name</p>
<p id="cryptoBizDisplayName" class="font-bold text-primary">—</p>
</div>
<div>
<p class="text-on-surface-variant text-xs uppercase tracking-wider">Formation State / Jurisdiction</p>
<p id="cryptoBizFormationState" class="font-bold text-primary">—</p>
</div>
<div class="sm:col-span-2" id="cryptoBizAssetValueWrap" style="display:none;">
<p class="text-on-surface-variant text-xs uppercase tracking-wider">Total Asset Value</p>
<p id="cryptoBizAssetValue" class="font-bold text-primary">—</p>
</div>
</div>
</div>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 sm:p-4 bg-background rounded-lg border border-surface-container">
<div class="min-w-0">
<p class="font-label-md text-label-md text-on-surface-variant text-xs sm:text-sm">LLC Name</p>
<p id="cryptoTrustNameDisplay" class="font-body-lg text-body-lg font-bold text-primary break-words">Loading...</p>
</div>
<button type="button" onclick="editTrustName()" class="text-secondary font-label-md text-label-md hover:underline text-sm shrink-0 self-start sm:self-center">Edit</button>
</div>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 sm:p-4 bg-background rounded-lg border border-surface-container">
<div class="min-w-0">
<p class="font-label-md text-label-md text-on-surface-variant text-xs sm:text-sm">LLC Status</p>
<p id="cryptoStatusBadge" class="font-body-lg text-body-lg font-bold text-primary">Loading...</p>
</div>
<button id="cryptoChangeStatusBtn" type="button" onclick="changeStatus()" class="text-secondary font-label-md text-label-md hover:underline text-sm shrink-0 self-start sm:self-center">Change</button>
</div>
</div>
</div>

<div id="cryptoPortfolioDesktopBlock" class="hidden md:block bg-surface-container-lowest p-4 sm:p-8 rounded-xl card-shadow border border-surface-container-high overflow-hidden crypto-layout-card min-w-0">
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 sm:mb-8">
<h3 class="font-headline-md text-headline-md text-primary">Selected Crypto Portfolio</h3>
<a id="addCoinsLinkDesktop" href="#" class="text-secondary text-sm font-bold hover:underline inline-flex items-center gap-1"><?php echo wt_icon('add-circle', 'w-4 h-4'); ?> Add Assets</a>
</div>
<table class="w-full text-left">
<thead>
<tr class="border-b border-surface-container text-on-surface-variant font-label-md text-label-md">
<th class="pb-4 font-medium">Asset</th>
<th class="pb-4 font-medium text-right">Balance</th>
<th class="pb-4 font-medium text-right">Allocation</th>
<th class="pb-4 font-medium text-right">Status</th>
</tr>
</thead>
<tbody id="cryptoPortfolioTableBody" class="divide-y divide-surface-container-low">
<tr><td colspan="4" class="py-8 text-center text-on-surface-variant text-sm">Loading portfolio...</td></tr>
</tbody>
</table>
</div>
</div>

<div class="lg:col-span-5 space-y-6 sm:space-y-10 min-w-0">
<div class="bg-surface-container-lowest p-4 sm:p-8 rounded-xl card-shadow border border-surface-container-high h-fit crypto-layout-card min-w-0 overflow-hidden">
<div class="flex flex-col gap-3 mb-6">
<h3 class="font-headline-md text-headline-md text-primary">Manage Share Holders</h3>
<div class="flex flex-col gap-2 w-full">
<button type="button" id="cryptoSaveChangesBtn" onclick="saveBeneficiaries()" class="hidden w-full sm:w-auto text-center sm:text-left text-deep-forest font-label-md text-label-md hover:underline py-2">Save Changes</button>
<button type="button" onclick="addBeneficiary()" class="w-full sm:w-auto text-secondary flex items-center justify-center sm:justify-start gap-1 font-label-md text-label-md hover:underline no-print py-2 px-3 rounded-lg border border-outline-variant sm:border-transparent">
<?php echo wt_icon('add-circle', 'w-[18px] h-[18px] shrink-0'); ?> Add Share Holder
</button>
</div>
</div>
<div id="cryptoBeneficiariesContainer" class="space-y-6 min-w-0">
<div class="text-center py-10 text-on-surface-variant">Loading beneficiaries...</div>
</div>
</div>

<section class="bg-error-container/20 p-4 sm:p-8 rounded-xl border border-error/20 space-y-4 sm:space-y-6 no-print crypto-layout-card" id="cryptoDangerZoneSection">
<div class="flex items-center gap-3">
<?php echo wt_icon('warning', 'w-6 h-6 text-error'); ?>
<h3 class="font-headline-md text-headline-md text-error">Danger Zone</h3>
</div>
<p class="text-sm text-on-error-container/80 leading-relaxed italic">
Warning: The following actions are irreversible and may require additional legal authorization under Wyoming Digital Asset statutes. Please proceed with extreme caution.
</p>
<div class="flex flex-col gap-3">
<button type="button" id="cryptoSuspendTrustBtn" onclick="suspendTrust()" class="w-full py-3 px-4 rounded-lg border-2 border-error text-error font-bold text-xs sm:text-sm hover:bg-error hover:text-on-primary transition-colors text-center whitespace-nowrap">
Suspend LLC
</button>
<button type="button" onclick="archiveTrust()" id="cryptoLiquidateTrustBtn" class="w-full py-3 px-4 rounded-lg bg-error text-on-primary font-bold text-xs sm:text-sm hover:opacity-90 transition-opacity text-center shadow-md whitespace-nowrap">
Liquidate LLC
</button>
</div>
</section>
</div>
</div>
</div>

<section id="irrevocableNoticeCrypto" class="hidden rounded-xl border border-outline-variant bg-surface-container-low p-6 mb-12">
<?php echo wt_icon('lock', 'w-5 h-5 text-secondary inline-block mr-2'); ?>
<p class="text-sm text-on-surface-variant inline"><strong class="text-primary">Irrevocable Structure:</strong> This LLC cannot be deleted or liquidated.</p>
</section>

<script src="<?php echo escape_html(asset_url('assets/js/trust-asset-ui.js')); ?>"></script>

<?php include __DIR__ . '/includes/modal.php'; ?>

<script>
const trustId = <?php echo $trustId; ?>;
let currentTrust = null;
let beneficiariesState = [];
let hasBeneficiaryChanges = false;
let originalBeneficiariesState = [];
let isCryptoLayout = false;
let cryptoBenEditing = null;

let csrfToken = null;

async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    try {
        const res = await fetch('../../api/session.php', { credentials: 'same-origin' });
        const data = await res.json();
        csrfToken = data.csrf_token || null;
    } catch (error) {
        console.error('Failed to get CSRF token:', error);
    }
    return csrfToken;
}

async function submitTrustLiquidation(targetTrustId) {
    const token = await getCsrfToken();
    const res = await fetch('../../api/user/trusts.php', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': token || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id: targetTrustId, liquidate: true, csrf_token: token }),
    });
    const data = await res.json();
    if (!data.success && data.redirect_checkout) {
        window.location.href = `checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`;
        return null;
    }
    return data;
}

async function requiresTrustLiquidationCheckout(targetTrustId, fee) {
    if (!(parseFloat(fee) > 0)) return false;
    try {
        const res = await fetch(`../../api/user/checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`, { credentials: 'same-origin' });
        const data = await res.json();
        if (data.success && data.has_fee && !data.already_submitted) {
            window.location.href = `checkout.php?type=trust_liquidation&trust_id=${targetTrustId}`;
            return true;
        }
    } catch (error) {
        console.error('Liquidation checkout check failed:', error);
    }
    return false;
}

function applyTrustLayout(trust) {
    isCryptoLayout = !!(trust?.service_meta?.is_crypto);
    const standard = document.getElementById('standardTrustLayout');
    const crypto = document.getElementById('cryptoTrustLayout');
    if (standard) standard.classList.toggle('hidden', isCryptoLayout);
    if (crypto) crypto.classList.toggle('hidden', !isCryptoLayout);
    if (isCryptoLayout) {
        document.title = 'Crypto Portfolio Dashboard | WyomingTrust';
    }
}

function syncCryptoHeader(trust) {
    const name = trust.trust_name || 'Untitled LLC';
    const typeLabel = trust.service_meta?.is_crypto ? 'Smart Contract Trust' : (trust.trust_type || 'Trust');
    const els = {
        cryptoTrustName: name,
        cryptoTrustId: `ID: ${trust.id || 'N/A'}`,
        cryptoTrustNameDisplay: name,
        cryptoTrustTypeBadge: typeLabel,
    };
    Object.entries(els).forEach(([id, text]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    });
    syncAddCoinsLinks(trust);
}

function syncAddCoinsLinks(trust) {
    const id = trust?.id || trustId;
    if (!id) return;
    const href = `trust-coins.php?trust_id=${encodeURIComponent(id)}`;
    ['addCoinsLinkLegacy', 'addCoinsLinkMobile', 'addCoinsLinkDesktop'].forEach((linkId) => {
        const el = document.getElementById(linkId);
        if (!el) return;
        el.href = href;
        el.classList.remove('hidden');
    });
}

function scrollToCryptoPortfolio() {
    const isDesktop = typeof window.matchMedia === 'function' && window.matchMedia('(min-width: 768px)').matches;
    const target = isDesktop
        ? (document.getElementById('cryptoPortfolioDesktopBlock') || document.getElementById('cryptoPortfolioSection'))
        : (document.getElementById('cryptoPortfolioMobileBlock') || document.getElementById('cryptoPortfolioSection'));
    if (target && typeof target.scrollIntoView === 'function') {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

const US_JURISDICTIONS = [
    { code: 'AL', name: 'Alabama' }, { code: 'AK', name: 'Alaska' }, { code: 'AZ', name: 'Arizona' },
    { code: 'AR', name: 'Arkansas' }, { code: 'CA', name: 'California' }, { code: 'CO', name: 'Colorado' },
    { code: 'CT', name: 'Connecticut' }, { code: 'DE', name: 'Delaware' }, { code: 'FL', name: 'Florida' },
    { code: 'GA', name: 'Georgia' }, { code: 'HI', name: 'Hawaii' }, { code: 'ID', name: 'Idaho' },
    { code: 'IL', name: 'Illinois' }, { code: 'IN', name: 'Indiana' }, { code: 'IA', name: 'Iowa' },
    { code: 'KS', name: 'Kansas' }, { code: 'KY', name: 'Kentucky' }, { code: 'LA', name: 'Louisiana' },
    { code: 'ME', name: 'Maine' }, { code: 'MD', name: 'Maryland' }, { code: 'MA', name: 'Massachusetts' },
    { code: 'MI', name: 'Michigan' }, { code: 'MN', name: 'Minnesota' }, { code: 'MS', name: 'Mississippi' },
    { code: 'MO', name: 'Missouri' }, { code: 'MT', name: 'Montana' }, { code: 'NE', name: 'Nebraska' },
    { code: 'NV', name: 'Nevada' }, { code: 'NH', name: 'New Hampshire' }, { code: 'NJ', name: 'New Jersey' },
    { code: 'NM', name: 'New Mexico' }, { code: 'NY', name: 'New York' }, { code: 'NC', name: 'North Carolina' },
    { code: 'ND', name: 'North Dakota' }, { code: 'OH', name: 'Ohio' }, { code: 'OK', name: 'Oklahoma' },
    { code: 'OR', name: 'Oregon' }, { code: 'PA', name: 'Pennsylvania' }, { code: 'RI', name: 'Rhode Island' },
    { code: 'SC', name: 'South Carolina' }, { code: 'SD', name: 'South Dakota' }, { code: 'TN', name: 'Tennessee' },
    { code: 'TX', name: 'Texas' }, { code: 'UT', name: 'Utah' }, { code: 'VT', name: 'Vermont' },
    { code: 'VA', name: 'Virginia' }, { code: 'WA', name: 'Washington' }, { code: 'WV', name: 'West Virginia' },
    { code: 'WI', name: 'Wisconsin' }, { code: 'WY', name: 'Wyoming' },
    { code: 'DC', name: 'District of Columbia' },
    { code: 'PR', name: 'Puerto Rico' },
    { code: 'GU', name: 'Guam' },
    { code: 'VI', name: 'U.S. Virgin Islands' }
];

const BUSINESS_ENDING_OPTIONS = [
    { value: 'none', label: 'Prefer no ending' },
    { value: 'llc', label: 'LLC' },
    { value: 'limited_liability_company', label: 'Limited Liability Company' },
    { value: 'corp', label: 'Corp' },
    { value: 'corporation', label: 'Corporation' },
    { value: 'inc', label: 'Inc' },
    { value: 'incorporated', label: 'Incorporated' }
];

function getBusinessEndingLabel(value) {
    const match = BUSINESS_ENDING_OPTIONS.find(o => o.value === value);
    return match ? match.label : (value || '');
}

function getFormationLabel(code) {
    const match = US_JURISDICTIONS.find(j => j.code === code);
    return match ? match.name : (code || '');
}

function formatCompanyDisplayName(bi) {
    const info = bi || {};
    const company = (info.company_name || '').trim();
    if (!company) return '';
    const ending = info.business_ending;
    if (!ending || ending === 'none') return company;
    const label = getBusinessEndingLabel(ending);
    return label ? `${company} ${label}` : company;
}

function syncBusinessInfoUI(trust) {
    const bi = trust?.business_info || trust?.trust_data?.business_info || {};
    const company = (bi.company_name || '').trim();
    const ending = (bi.business_ending || '').trim();
    const formation = (bi.formation_state || '').trim();
    const displayName = formatCompanyDisplayName(bi);
    const hasBusiness = !!(company || ending || formation || displayName);

    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text || '—';
    };

    const companyText = company || '—';
    const endingText = ending ? getBusinessEndingLabel(ending) : '—';
    const formationText = formation ? getFormationLabel(formation) : '—';
    const displayText = displayName || '—';

    setText('bizCompanyName', companyText);
    setText('bizBusinessEnding', endingText);
    setText('bizDisplayName', displayText);
    setText('bizFormationState', formationText);
    setText('cryptoBizCompanyName', companyText);
    setText('cryptoBizBusinessEnding', endingText);
    setText('cryptoBizDisplayName', displayText);
    setText('cryptoBizFormationState', formationText);

    const assetVal = trust?.total_estimated_value;
    const showAsset = assetVal != null && !Number.isNaN(Number(assetVal)) && Number(assetVal) > 0;
    const assetText = showAsset
        ? new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(Number(assetVal))
        : '—';
    setText('bizAssetValue', assetText);
    setText('cryptoBizAssetValue', assetText);
    ['bizAssetValueWrap', 'cryptoBizAssetValueWrap'].forEach((id) => {
        const wrap = document.getElementById(id);
        if (wrap) wrap.style.display = showAsset ? '' : 'none';
    });

    ['businessInfoCard', 'cryptoBusinessInfoCard'].forEach((id) => {
        const card = document.getElementById(id);
        if (!card) return;
        card.classList.toggle('hidden', !hasBusiness && !showAsset);
    });
}

var modalResolve = null;
var modalReject = null;

function showConfirmModal(title, message, confirmText = 'Confirm', cancelText = 'Cancel', type = 'warning') {
    return new Promise((resolve, reject) => {
        modalResolve = resolve;
        modalReject = reject;
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');

        inputDiv.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        if (type === 'danger') {
            setModalIcon('warning', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-error text-base font-bold text-on-primary hover:bg-error/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm';
        } else {
            setModalIcon('help', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-bold text-on-primary hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm';
        }

        confirmBtn.onclick = () => {
            closeModal({ reject: false });
            resolve(true);
        };

        modal.classList.remove('hidden');
    });
}

function showAlertModal(title, message, type = 'info') {
    return new Promise((resolve) => {
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');

        inputDiv.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = 'OK';

        if (type === 'success') {
            setModalIcon('check-circle', 'text-deep-forest text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-deep-forest/10 sm:mx-0 sm:h-10 sm:w-10';
        } else if (type === 'error') {
            setModalIcon('error', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
        } else {
            setModalIcon('info', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
        }

        confirmBtn.onclick = () => {
            closeModal({ reject: false });
            resolve();
        };

        modal.classList.remove('hidden');
    });
}

function showInputModal(title, message, initialValue = '', confirmText = 'Confirm') {
    return new Promise((resolve, reject) => {
        modalResolve = resolve;
        modalReject = reject;
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');
        const inputField = document.getElementById('modalInputField');

        inputDiv.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        inputField.placeholder = 'Enter LLC name';
        inputField.value = initialValue === 'Untitled LLC' ? '' : (initialValue || '');
        confirmBtn.textContent = confirmText;

        setModalIcon('edit', 'text-secondary text-xl');
        iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
        confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-bold text-on-primary hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm';

        const handleConfirm = () => {
            const value = inputField.value.trim();
            if (value) {
                closeModal({ reject: false });
                resolve(value);
            }
        };

        confirmBtn.onclick = handleConfirm;
        inputField.onkeypress = (e) => {
            if (e.key === 'Enter') handleConfirm();
        };

        inputField.focus();
        modal.classList.remove('hidden');
    });
}

function closeModal(options = {}) {
    const reject = options.reject !== false;
    const modal = document.getElementById('customModal');
    const cancelBtn = document.getElementById('modalCancelBtn');
    modal.classList.add('hidden');
    cancelBtn.classList.remove('hidden');
    if (reject && modalReject) {
        modalReject(false);
    }
    modalReject = null;
    modalResolve = null;
}

async function loadTrustData() {
    if (!trustId) {
        await showAlertModal('Error', 'Invalid LLC ID', 'error');
        window.location.href = 'dashboard.php';
        return;
    }

    try {
        const response = await fetch(`../../api/user/trusts.php?id=${trustId}`);
        const data = await response.json();

        if (data.success && data.trust) {
            const trust = data.trust;
            currentTrust = trust;
            applyTrustLayout(trust);

            const trustName = trust.trust_name || 'Untitled LLC';
            document.getElementById('trustName').textContent = trustName;
            document.getElementById('trustId').textContent = `ID: ${trust.id || 'N/A'}`;
            document.getElementById('trustTypeBadge').textContent = trust.service_meta?.is_irrevocable ? 'Irrevocable Trust' : (trust.service_meta?.is_revocable ? 'Revocable Living Trust' : (trust.service_meta?.is_crypto ? 'Smart Contract Trust' : (trust.trust_type || 'Standard')));
            if (isCryptoLayout) syncCryptoHeader(trust);

            updateStatusUI(trust);
            updatePendingRegistrationBanner(trust);
            updateTrustPermissionsUI(trust);
            syncBusinessInfoUI(trust);
            await updateTrustMetrics(trust);

            beneficiariesState = Array.isArray(trust.beneficiaries) ? trust.beneficiaries : [];
            originalBeneficiariesState = JSON.parse(JSON.stringify(beneficiariesState));
            hasBeneficiaryChanges = false;
            cryptoBenEditing = null;
            renderBeneficiaries(beneficiariesState);
            const benCount = beneficiariesState.length || 0;
            document.getElementById('beneficiaryCount').textContent = benCount;
            const cryptoBenCount = document.getElementById('cryptoBeneficiaryCount');
            if (cryptoBenCount) cryptoBenCount.textContent = benCount;
            updateSaveButtonVisibility();

            if (trust.service_meta?.supports_assets) {
                document.getElementById('trustAssetsSection').style.display = '';
                document.getElementById('cryptoTrustSection').style.display = 'none';
                loadTrustAssetsUI(trust);
            } else if (trust.service_meta?.is_crypto) {
                document.getElementById('trustAssetsSection').style.display = 'none';
                document.getElementById('cryptoTrustSection').style.display = 'none';
                syncAddCoinsLinks(trust);
                await renderCryptoPortfolioTable(trust);
            } else {
                document.getElementById('trustAssetsSection').style.display = 'none';
                document.getElementById('cryptoTrustSection').style.display = 'none';
            }

            if ((window.location.hash || '') === '#cryptoPortfolioSection') {
                setTimeout(() => scrollToCryptoPortfolio(), 150);
            }
        } else {
            await showAlertModal('Error', 'LLC not found', 'error');
            window.location.href = 'dashboard.php';
        }
    } catch (error) {
        console.error('Error loading trust:', error);
        await showAlertModal('Error', 'Error loading LLC data', 'error');
    }
}

function renderBeneficiaries(beneficiaries) {
    if (isCryptoLayout) {
        renderCryptoBeneficiaries(beneficiaries);
        return;
    }
    const container = document.getElementById('beneficiariesContainer');
    if (!beneficiaries || beneficiaries.length === 0) {
        container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No share holders added yet. Click "Add Share Holder".</div>';
        return;
    }

    const html = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            ${beneficiaries.map((ben, idx) => `
                <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold flex-shrink-0">${escapeHtml((ben.name || 'B').charAt(0).toUpperCase())}</div>
                            <div class="min-w-0">
                                <p class="text-primary font-black truncate">Share Holder #${idx + 1}${ben.is_myself ? ' (Myself)' : ''}</p>
                                <p class="text-on-surface-variant text-xs truncate">${escapeHtml(ben.relationship || '')}${ben.email ? ' · ' + escapeHtml(ben.email) : ''}</p>
                            </div>
                        </div>
                        <button onclick="removeBeneficiary(${idx})" class="text-error text-xs font-bold hover:underline">Remove</button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Name</label>
                            <input value="${escapeHtml(ben.name || '')}" oninput="updateBeneficiary(${idx}, 'name', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Relationship</label>
                            <input value="${escapeHtml(ben.relationship || '')}" oninput="updateBeneficiary(${idx}, 'relationship', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Email</label>
                            <input value="${escapeHtml(ben.email || '')}" oninput="updateBeneficiary(${idx}, 'email', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Allocation %</label>
                            <input type="number" min="0" max="100" step="0.01" value="${ben.allocation ?? 0}" oninput="updateBeneficiary(${idx}, 'allocation', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface"/>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Wallet Address (optional)</label>
                            <input value="${escapeHtml(ben.wallet_address || '')}" oninput="updateBeneficiary(${idx}, 'wallet_address', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface"/>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="mt-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-4">
            <p class="text-sm font-bold text-primary">Total Allocation: <span id="allocationTotal">0</span>%</p>
            <p class="text-xs text-on-surface-variant mt-1">Must equal 100% before saving.</p>
        </div>
    `;
    container.innerHTML = html;
    updateAllocationTotal();
}

const CRYPTO_BEN_ACCENT = ['bg-secondary', 'bg-sky-accent'];

function renderCryptoBeneficiaries(beneficiaries) {
    const container = document.getElementById('cryptoBeneficiariesContainer');
    if (!container) return;

    if (!beneficiaries || beneficiaries.length === 0) {
        container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No share holders added yet. Click "Add Share Holder".</div>';
        return;
    }

    const total = beneficiaries.reduce((sum, b) => sum + (parseFloat(b.allocation) || 0), 0);
    const totalValid = Math.abs(total - 100) < 0.01;

    container.innerHTML = beneficiaries.map((ben, idx) => {
        const accent = CRYPTO_BEN_ACCENT[idx % CRYPTO_BEN_ACCENT.length];
        const displayName = ben.is_myself ? (ben.name || 'Myself') : (ben.name || `Share Holder #${idx + 1}`);
        const editing = cryptoBenEditing === idx;

        if (editing) {
            return `
                <div class="crypto-beneficiary-card p-4 sm:p-5 rounded-lg border border-secondary bg-background relative min-w-0">
                    <div class="absolute top-0 left-0 w-1 h-full ${accent}"></div>
                    <div class="flex justify-between items-start mb-4 pl-2">
                        <p class="font-bold text-body-lg text-primary">Edit Share Holder</p>
                        ${ben.is_myself ? '' : `<button type="button" onclick="removeBeneficiary(${idx})" class="text-error text-xs font-bold hover:underline">Remove</button>`}
                    </div>
                    <div class="grid grid-cols-1 gap-3 pl-2 text-sm">
                        <div>
                            <label class="text-on-surface-variant text-xs mb-1 block">Name</label>
                            <input value="${escapeHtml(ben.name || '')}" oninput="updateBeneficiary(${idx}, 'name', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low"/>
                        </div>
                        <div>
                            <label class="text-on-surface-variant text-xs mb-1 block">Relationship</label>
                            <input value="${escapeHtml(ben.relationship || '')}" oninput="updateBeneficiary(${idx}, 'relationship', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low"/>
                        </div>
                        <div>
                            <label class="text-on-surface-variant text-xs mb-1 block">Email</label>
                            <input value="${escapeHtml(ben.email || '')}" oninput="updateBeneficiary(${idx}, 'email', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low"/>
                        </div>
                        <div>
                            <label class="text-on-surface-variant text-xs mb-1 block">Allocation %</label>
                            <input type="number" min="0" max="100" step="0.01" value="${ben.allocation ?? 0}" oninput="updateBeneficiary(${idx}, 'allocation', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low"/>
                        </div>
                        <div>
                            <label class="text-on-surface-variant text-xs mb-1 block">Wallet Address</label>
                            <input value="${escapeHtml(ben.wallet_address || '')}" oninput="updateBeneficiary(${idx}, 'wallet_address', this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low font-mono text-xs"/>
                        </div>
                        <button type="button" onclick="cryptoBenEditing=null; renderBeneficiaries(beneficiariesState);" class="text-secondary font-label-md text-label-md hover:underline text-left">Done editing</button>
                    </div>
                </div>
            `;
        }

        return `
            <div class="crypto-beneficiary-card p-4 sm:p-5 rounded-lg border border-surface-container bg-background relative min-w-0 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full ${accent}"></div>
                <div class="mb-4 pl-3 pr-1">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-sm sm:text-base text-primary break-words leading-snug">${escapeHtml(displayName)}</p>
                            <p class="text-on-surface-variant text-xs sm:text-sm break-all mt-0.5">${escapeHtml(ben.email || 'No email')}</p>
                        </div>
                        <div class="flex items-center gap-2 sm:flex-col sm:items-end sm:text-right shrink-0 bg-surface-container-low sm:bg-transparent rounded-lg px-3 py-2 sm:p-0">
                            <p class="text-[10px] uppercase font-bold text-secondary tracking-wide">Allocation</p>
                            <p class="text-base sm:text-lg font-bold text-primary leading-none">${parseFloat(ben.allocation || 0).toFixed(2)}%</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 text-sm pl-3 pr-1">
                    <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between sm:items-start border-t border-surface-container-high pt-2">
                        <span class="text-on-surface-variant text-xs shrink-0">Relationship</span>
                        <span class="font-medium text-sm break-words sm:text-right">${escapeHtml(ben.relationship || '—')}</span>
                    </div>
                    ${ben.wallet_address ? `
                    <div class="flex flex-col pt-2">
                        <span class="text-on-surface-variant text-xs mb-1">Wallet Address:</span>
                        <span class="font-mono text-xs break-all bg-surface p-2 rounded border border-outline-variant">${escapeHtml(ben.wallet_address)}</span>
                    </div>
                    ` : ''}
                    <button type="button" onclick="cryptoBenEditing=${idx}; renderBeneficiaries(beneficiariesState);" class="text-secondary font-label-md text-label-md hover:underline text-left pt-1">Edit details</button>
                </div>
            </div>
        `;
    }).join('') + `
        <div class="p-3 ${totalValid ? 'bg-deep-forest/5 border-deep-forest/20' : 'bg-error-container/30 border-error/20'} border rounded-lg flex items-center justify-center gap-2">
            <p class="font-label-md text-label-md ${totalValid ? 'text-deep-forest' : 'text-error'} font-bold">Total Allocation: <span id="allocationTotal">${total.toFixed(2)}</span>%</p>
        </div>
        ${!totalValid ? '<p class="text-xs text-on-surface-variant text-center">Must equal 100% before saving.</p>' : ''}
    `;
}

async function editTrustName() {
    const displayName = currentTrust?.trust_name || document.getElementById('trustName').textContent;
    const storedName = (currentTrust?.trust_name || '').trim();
    try {
        const newName = await showInputModal('Edit LLC Name', 'Enter a new name for this LLC:', displayName, 'Save');
        if (newName && newName.trim() !== storedName) {
            await updateTrustName(newName.trim());
        }
    } catch (e) {
        // User cancelled
    }
}

async function changeStatus() {
    const currentStatus = (currentTrust?.status || 'active').toString().toLowerCase();
    if (currentStatus !== 'active') {
        await showAlertModal('Not Available', 'LLC activation requires approval.', 'warning');
        return;
    }
    const newStatus = 'inactive';
    const confirmed = await showConfirmModal(
        'Change LLC Status',
        `Are you sure you want to change the LLC status from "${currentStatus}" to "${newStatus}"?`,
        'Change Status',
        'Cancel'
    );
    if (confirmed) {
        await updateTrustStatus(newStatus);
    }
}

function addBeneficiary() {
    beneficiariesState.push({
        name: '',
        relationship: '',
        email: '',
        allocation: 0,
        wallet_address: '',
        is_myself: false
    });
    hasBeneficiaryChanges = true;
    if (isCryptoLayout) {
        cryptoBenEditing = beneficiariesState.length - 1;
    }
    renderBeneficiaries(beneficiariesState);
    const benCount = beneficiariesState.length || 0;
    document.getElementById('beneficiaryCount').textContent = benCount;
    const cryptoBenCount = document.getElementById('cryptoBeneficiaryCount');
    if (cryptoBenCount) cryptoBenCount.textContent = benCount;
    updateSaveButtonVisibility();
}

async function suspendTrust() {
    const currentStatus = (currentTrust?.status || '').toString().toLowerCase();
    if (currentStatus !== 'active') {
        await showAlertModal('Not Available', 'Only active LLCs can be suspended.', 'warning');
        return;
    }
    const confirmed = await showConfirmModal(
        'Suspend LLC',
        'Are you sure you want to suspend this LLC? The LLC status will be changed to inactive. This action may be reversible.',
        'Suspend LLC',
        'Cancel',
        'warning'
    );
    if (confirmed) {
        await updateTrustStatus('inactive');
    }
}

async function archiveTrust() {
    const meta = currentTrust?.service_meta || {};
    if (meta.is_irrevocable) {
        await showAlertModal('Not Allowed', 'Irrevocable LLCs cannot be deleted or liquidated.', 'error');
        return;
    }
    const currentStatus = (currentTrust?.status || '').toString().toLowerCase();
    if (currentStatus === 'pending') {
        await showAlertModal('Not Available', 'LLC registration is pending approval. Liquidation is not available yet.', 'warning');
        return;
    }
    if (currentStatus !== 'active') {
        await showAlertModal('Not Available', 'Only active LLCs can be liquidated.', 'warning');
        return;
    }
    const fee = parseFloat(meta.liquidation_fee || 0);
    const feeMsg = fee > 0 ? ` You will be taken to checkout to pay the $${fee.toFixed(2)} liquidation fee.` : '';
    const confirmed = await showConfirmModal(
        'Liquidate LLC',
        `Are you sure you want to liquidate this LLC?${feeMsg} This begins the formal wind-down process.`,
        fee > 0 ? 'Continue to Checkout' : 'Liquidate LLC',
        'Cancel',
        'danger'
    );
    if (!confirmed) return;
    if (await requiresTrustLiquidationCheckout(trustId, fee)) return;
    try {
        const data = await submitTrustLiquidation(trustId);
        if (!data) return;
        if (data.success) {
            await showAlertModal('Liquidation Started', 'Your liquidation request has been submitted and is pending processing.', 'success');
            await loadTrustData();
        } else if (data.payment_pending) {
            await showAlertModal('Payment Pending', data.message || 'Liquidation fee payment is pending approval.', 'warning');
        } else {
            await showAlertModal('Error', data.message || 'Failed to liquidate LLC', 'error');
        }
    } catch (e) {
        console.error(e);
        await showAlertModal('Error', 'Error processing liquidation', 'error');
    }
}

function updateTrustPermissionsUI(trust) {
    const meta = trust.service_meta || {};
    const danger = document.getElementById(isCryptoLayout ? 'cryptoDangerZoneSection' : 'dangerZoneSection');
    const notice = document.getElementById(isCryptoLayout ? 'irrevocableNoticeCrypto' : 'irrevocableNotice');
    const liqBtn = document.getElementById(isCryptoLayout ? 'cryptoLiquidateTrustBtn' : 'liquidateTrustBtn');
    if (meta.is_irrevocable) {
        if (danger) danger.classList.add('hidden');
        if (notice) notice.classList.remove('hidden');
        return;
    }
    if (danger) danger.classList.remove('hidden');
    if (notice) notice.classList.add('hidden');
    if (liqBtn) {
        const fee = parseFloat(meta.liquidation_fee || 0);
        if (isCryptoLayout) {
            liqBtn.textContent = fee > 0 ? `Liquidate LLC & Withdraw ($${fee.toFixed(2)} fee)` : 'Liquidate LLC & Withdraw';
        } else {
            liqBtn.textContent = fee > 0 ? `Liquidate LLC ($${fee.toFixed(2)} fee)` : 'Liquidate LLC';
        }
    }
}

function formatUsd(value) {
    const v = parseFloat(value) || 0;
    return '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function updateTrustMetrics(trust) {
    const meta = trust.service_meta || {};
    const portfolioCard = document.getElementById('portfolioAssetsCard');
    const metricsSection = document.getElementById('trustMetricsSection');
    const valueEl = document.getElementById('totalValue');
    const totalValueCard = document.getElementById('totalValueCard');
    const declaredCard = document.getElementById('declaredValueCard');
    const verifiedCard = document.getElementById('verifiedFundedCard');

    if (meta.is_crypto) {
        if (portfolioCard) portfolioCard.classList.remove('hidden');
        if (totalValueCard) totalValueCard.classList.remove('hidden');
        if (declaredCard) declaredCard.classList.add('hidden');
        if (verifiedCard) verifiedCard.classList.add('hidden');
        if (metricsSection) {
            metricsSection.classList.remove('lg:grid-cols-3', 'lg:grid-cols-5');
            metricsSection.classList.add('lg:grid-cols-4');
        }
        await updateCryptoMetrics(trust, valueEl);
        return;
    }

    if (portfolioCard) portfolioCard.classList.add('hidden');

    if (meta.supports_assets) {
        if (totalValueCard) totalValueCard.classList.add('hidden');
        if (declaredCard) declaredCard.classList.remove('hidden');
        if (verifiedCard) verifiedCard.classList.remove('hidden');
        if (metricsSection) {
            metricsSection.classList.remove('lg:grid-cols-3', 'lg:grid-cols-4');
            metricsSection.classList.add('lg:grid-cols-5');
        }
        updateCatalogMetrics(trust);
        return;
    }

    if (totalValueCard) totalValueCard.classList.remove('hidden');
    if (declaredCard) declaredCard.classList.add('hidden');
    if (verifiedCard) verifiedCard.classList.add('hidden');
    if (metricsSection) {
        metricsSection.classList.remove('lg:grid-cols-4', 'lg:grid-cols-5');
        metricsSection.classList.add('lg:grid-cols-3');
    }
    if (valueEl) valueEl.textContent = formatUsd(0);
}

function updateCatalogMetrics(trust) {
    const unverified = parseFloat(trust.declared_unverified_value ?? 0) || 0;
    const verified = parseFloat(trust.verified_funded_value ?? 0) || 0;
    const declaredEl = document.getElementById('declaredUnverifiedValue');
    const verifiedEl = document.getElementById('verifiedFundedValue');
    const declaredCard = document.getElementById('declaredValueCard');
    const hintEl = document.getElementById('declaredUnverifiedHint');

    if (declaredEl) declaredEl.textContent = formatUsd(unverified);
    if (verifiedEl) verifiedEl.textContent = formatUsd(verified);

    if (declaredCard) {
        if (unverified <= 0 && verified > 0) {
            declaredCard.classList.add('hidden');
        } else {
            declaredCard.classList.remove('hidden');
        }
    }

    if (hintEl) {
        const fundingStatus = (trust.declared_value_funding?.status || 'unfunded').toString();
        if (fundingStatus === 'pending') {
            hintEl.textContent = 'Deposit pending approval';
        } else if (fundingStatus === 'rejected') {
            hintEl.textContent = 'Unverified — deposit rejected, please resubmit';
        } else {
            hintEl.textContent = 'Unverified — not yet deposited';
        }
    }

    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

async function updateCryptoMetrics(trust, valueEl) {
    const assetsEl = document.getElementById(isCryptoLayout ? 'cryptoPortfolioAssets' : 'portfolioAssets');
    const allocEl = document.getElementById('portfolioAllocation');
    const cryptoValueEl = document.getElementById('cryptoTotalValue');
    const depositHint = document.getElementById('cryptoDepositHint');
    const entrusted = Array.isArray(trust.entrusted_coins)
        ? trust.entrusted_coins
        : (trust.trust_data?.entrusted_coins || []);
    const entrustedSet = new Set(entrusted.map((k) => String(k).toLowerCase()));
    const totalSlots = entrusted.length;

    try {
        const res = await fetch('../../api/user/assets.php');
        const data = await res.json();
        const allAssets = data.success && Array.isArray(data.assets) ? data.assets : [];
        // Funded Total Value = deposit balances for entrusted coins only (never declared onboarding value)
        const relevant = totalSlots
            ? allAssets.filter((a) => entrustedSet.has(String(a.coin_key).toLowerCase()))
            : [];
        const funded = relevant.filter((a) => parseFloat(a.balance) > 0);
        const entrustedUsd = relevant.reduce((sum, a) => sum + (parseFloat(a.value_usd) || 0), 0);
        const walletUsd = allAssets.reduce((sum, a) => sum + (parseFloat(a.value_usd) || 0), 0);
        const allocationPct = walletUsd > 0 ? (entrustedUsd / walletUsd) * 100 : 0;
        const fundedCount = funded.length;
        // Portfolio Assets = selected coins (even at $0 balance); legacy layout keeps funded/selected
        const displayCount = isCryptoLayout
            ? String(totalSlots)
            : `${fundedCount}/${totalSlots || 0}`;

        if (assetsEl) assetsEl.textContent = displayCount;
        if (allocEl) allocEl.textContent = `${allocationPct.toFixed(0)}% allocation`;
        const formatted = formatUsd(entrustedUsd);
        if (valueEl) valueEl.textContent = formatted;
        if (cryptoValueEl) cryptoValueEl.textContent = formatted;
        if (depositHint) {
            depositHint.classList.toggle('hidden', entrustedUsd > 0);
        }
    } catch (error) {
        console.error('Error loading crypto metrics:', error);
        if (assetsEl) assetsEl.textContent = isCryptoLayout ? String(totalSlots) : `0/${totalSlots}`;
        if (allocEl) allocEl.textContent = '0% allocation';
        if (valueEl) valueEl.textContent = formatUsd(0);
        if (cryptoValueEl) cryptoValueEl.textContent = formatUsd(0);
        if (depositHint) depositHint.classList.remove('hidden');
    }
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

async function renderCryptoPortfolioTable(trust) {
    const tbody = document.getElementById('cryptoPortfolioTableBody');
    const mobileList = document.getElementById('cryptoPortfolioMobileList');
    if (!tbody) return;

    const entrusted = Array.isArray(trust.entrusted_coins) ? trust.entrusted_coins : (trust.trust_data?.entrusted_coins || []);
    const entrustedSet = new Set(entrusted.map((k) => String(k).toLowerCase()));

    const emptyMsg = '<div class="py-8 text-center text-on-surface-variant text-sm">No cryptocurrencies selected for this LLC yet. Use Add Assets to choose deposit-ready coins.</div>';

    try {
        const res = await fetch('../../api/user/assets.php');
        const data = await res.json();
        const allAssets = data.success && Array.isArray(data.assets) ? data.assets : [];
        // Only entrusted_coins — never fall back to the full wallet
        let rows = [];
        if (entrusted.length) {
            const byKey = new Map(allAssets.map((a) => [String(a.coin_key).toLowerCase(), a]));
            rows = entrusted.map((key) => {
                const k = String(key).toLowerCase();
                const asset = byKey.get(k);
                if (asset) return asset;
                return {
                    coin_key: key,
                    display_name: String(key).replace(/_/g, ' '),
                    symbol: String(key).split('_')[0].toUpperCase(),
                    balance: 0,
                    logo: null,
                    value_usd: 0,
                };
            });
        }

        const totalUsd = rows.reduce((sum, a) => sum + (parseFloat(a.value_usd) || 0), 0);

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-on-surface-variant text-sm">${emptyMsg.replace(/<\/?div[^>]*>/g, '')}</td></tr>`;
            if (mobileList) mobileList.innerHTML = emptyMsg;
            return;
        }

        const mobileCards = [];

        tbody.innerHTML = rows.map((asset) => {
            const balance = parseFloat(asset.balance) || 0;
            const symbol = asset.symbol || asset.coin_key?.toUpperCase() || '';
            const name = asset.display_name || asset.coin_key || 'Unknown';
            const valueUsd = parseFloat(asset.value_usd) || 0;
            const allocPct = totalUsd > 0 ? (valueUsd / totalUsd) * 100 : 0;
            const status = balance > 0 ? 'Active' : 'Selected';
            const statusClass = balance > 0
                ? 'bg-green-100 text-green-800'
                : 'bg-secondary/10 text-secondary';
            const logo = asset.logo
                ? `<img src="${escapeHtml(asset.logo)}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">`
                : `<div class="w-10 h-10 rounded-full bg-secondary/15 flex items-center justify-center text-secondary font-bold text-xs shrink-0">${escapeHtml(symbol.slice(0, 3))}</div>`;

            const coinKey = asset.coin_key || '';
            if (!coinKey) {
                return `
                <tr class="group hover:bg-surface-container-low transition-colors">
                    <td class="py-5" colspan="4">
                        <div class="flex items-center gap-3">
                            ${logo}
                            <div>
                                <p class="font-bold text-primary">${escapeHtml(name)}</p>
                                <p class="text-xs text-on-surface-variant">${escapeHtml(symbol)}</p>
                            </div>
                        </div>
                    </td>
                </tr>`;
            }
            const detailUrl = `asset-detail.php?coin_key=${encodeURIComponent(coinKey)}&trust_id=${trustId}`;

            mobileCards.push(`
                <div class="p-4 rounded-xl border border-surface-container-high bg-background cursor-pointer active:bg-surface-container-low transition-colors" onclick="window.location.href='${detailUrl}'" role="link" tabindex="0">
                    <div class="flex items-start gap-3">
                        ${logo}
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-primary text-sm truncate">${escapeHtml(name)}</p>
                            <p class="text-xs text-on-surface-variant mb-1">${escapeHtml(symbol)}</p>
                            <span class="inline-block px-2 py-0.5 ${statusClass} text-[9px] uppercase font-bold rounded">${status}</span>
                            <div class="mt-3 flex justify-between text-xs gap-2">
                                <span class="text-on-surface-variant shrink-0">Balance</span>
                                <span class="font-medium text-primary text-right break-all">${balance.toLocaleString('en-US', { maximumFractionDigits: 8 })} ${escapeHtml(symbol)}</span>
                            </div>
                            <div class="mt-1 flex justify-between text-xs">
                                <span class="text-on-surface-variant">Allocation</span>
                                <span class="font-medium">${allocPct.toFixed(0)}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            return `
                <tr class="group hover:bg-surface-container-low transition-colors cursor-pointer" onclick="window.location.href='${detailUrl}'" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href='${detailUrl}'}">
                    <td class="py-5">
                        <div class="flex items-center gap-3">
                            ${logo}
                            <div>
                                <p class="font-bold text-primary">${escapeHtml(name)}</p>
                                <p class="text-xs text-on-surface-variant">${escapeHtml(symbol)}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-5 text-right font-medium">${balance.toLocaleString('en-US', { maximumFractionDigits: 8 })} ${escapeHtml(symbol)}</td>
                    <td class="py-5 text-right">${allocPct.toFixed(0)}%</td>
                    <td class="py-5 text-right">
                        <span class="px-2 py-1 ${statusClass} text-[10px] uppercase font-bold rounded">${status}</span>
                    </td>
                </tr>
            `;
        }).join('');

        if (mobileList) mobileList.innerHTML = mobileCards.join('');
    } catch (error) {
        console.error('Error loading crypto portfolio:', error);
        tbody.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-error text-sm">Failed to load portfolio data.</td></tr>';
        if (mobileList) mobileList.innerHTML = '<div class="py-8 text-center text-error text-sm">Failed to load portfolio data.</div>';
    }
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function loadTrustAssetsUI(trust) {
    const assets = Array.isArray(trust.assets) ? trust.assets : [];
    const categories = trust.service_meta?.asset_categories || [];
    const countLabel = document.getElementById('assetsCountLabel');
    if (countLabel) countLabel.textContent = assets.length ? `(${assets.length})` : '';
    if (typeof TrustAssetUI !== 'undefined') {
        TrustAssetUI.renderAssetList(assets, categories, 'trustAssetsList', removeTrustAsset, trustId);
    }
    renderDeclaredValueFundingBanner(trust);
}

function renderDeclaredValueFundingBanner(trust) {
    const section = document.getElementById('trustAssetsSection');
    if (!section) return;
    let banner = document.getElementById('declaredValueFundingBanner');
    const funding = trust.declared_value_funding || {};
    const assets = Array.isArray(trust.assets) ? trust.assets : [];
    const amount = parseFloat(funding.amount_usd || trust.total_estimated_value || 0);
    const status = funding.status || 'unfunded';

    if (assets.length > 0 || amount <= 0 || status === 'funded') {
        if (banner) banner.remove();
        return;
    }

    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'declaredValueFundingBanner';
        banner.className = 'mb-4 rounded-xl border border-secondary/20 bg-secondary/10 p-4 flex flex-wrap items-center justify-between gap-3';
        section.insertBefore(banner, section.querySelector('#trustAssetsList'));
    }

    const label = status === 'pending'
        ? `Declared LLC value deposit of ${formatUsd(amount)} is pending approval.`
        : status === 'rejected'
            ? `Declared LLC value deposit of ${formatUsd(amount)} was rejected. Please submit payment again.`
            : `Deposit ${formatUsd(amount)} to verify your declared total asset value.`;

    banner.innerHTML = `
        <p class="text-sm text-on-surface">${escapeHtml(label)}</p>
        ${status === 'unfunded' || status === 'rejected' ? `
            <a href="checkout.php?type=trust_value&trust_id=${trustId}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-bold hover:bg-primary/90">
                Deposit Trust Value
            </a>
        ` : '<span class="text-xs font-bold text-secondary uppercase tracking-wide">Pending</span>'}
    `;
}

function openAddAssetModal() {
    if (!currentTrust?.service_meta?.supports_assets) return;
    TrustAssetUI.showAddAssetModal(currentTrust.service_meta.asset_categories || [], trustId, async (data) => {
        if (data.requires_funding && data.asset?.id) {
            window.location.href = `checkout.php?type=asset_funding&trust_id=${trustId}&asset_id=${encodeURIComponent(data.asset.id)}`;
            return;
        }
        await loadTrustData();
    });
}

async function removeTrustAsset(assetId) {
    const confirmed = await showConfirmModal('Remove Asset', 'Remove this asset from the trust?', 'Remove', 'Cancel', 'danger');
    if (!confirmed) return;
    const res = await fetch(`../../api/user/trust-assets.php?trust_id=${trustId}&asset_id=${encodeURIComponent(assetId)}`, { method: 'DELETE', credentials: 'same-origin' });
    const data = await res.json();
    if (data.success) {
        await loadTrustData();
    } else {
        await showAlertModal('Error', data.message || 'Failed to remove asset', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateBeneficiary(index, field, value) {
    if (!beneficiariesState[index]) return;
    if (field === 'allocation') {
        beneficiariesState[index][field] = parseFloat(value) || 0;
    } else {
        beneficiariesState[index][field] = value;
    }
    hasBeneficiaryChanges = true;
    updateAllocationTotal();
    updateSaveButtonVisibility();
}

function updateSaveButtonVisibility() {
    const saveBtn = document.getElementById('saveChangesBtn');
    const cryptoSaveBtn = document.getElementById('cryptoSaveChangesBtn');
    [saveBtn, cryptoSaveBtn].forEach((btn) => {
        if (!btn) return;
        btn.classList.toggle('hidden', !hasBeneficiaryChanges);
    });
}

function removeBeneficiary(index) {
    beneficiariesState.splice(index, 1);
    hasBeneficiaryChanges = true;
    cryptoBenEditing = null;
    renderBeneficiaries(beneficiariesState);
    const benCount = beneficiariesState.length || 0;
    document.getElementById('beneficiaryCount').textContent = benCount;
    const cryptoBenCount = document.getElementById('cryptoBeneficiaryCount');
    if (cryptoBenCount) cryptoBenCount.textContent = benCount;
    updateSaveButtonVisibility();
}

function updateAllocationTotal() {
    const total = beneficiariesState.reduce((sum, b) => sum + (parseFloat(b.allocation) || 0), 0);
    const el = document.getElementById('allocationTotal');
    if (el) el.textContent = total.toFixed(2);
}

async function saveBeneficiaries() {
    const total = beneficiariesState.reduce((sum, b) => sum + (parseFloat(b.allocation) || 0), 0);
    if (Math.abs(total - 100) > 0.01) {
        await showAlertModal('Validation Error', `Total allocation must equal 100%. Current total: ${total.toFixed(2)}%`, 'error');
        return;
    }
    try {
        const token = await getCsrfToken();
        const res = await fetch('../../api/user/trusts.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: trustId, beneficiaries: beneficiariesState, csrf_token: token }),
        });
        const data = await res.json();
        if (data.success && data.trust) {
            beneficiariesState = Array.isArray(data.trust.beneficiaries) ? data.trust.beneficiaries : beneficiariesState;
            originalBeneficiariesState = JSON.parse(JSON.stringify(beneficiariesState));
            hasBeneficiaryChanges = false;
            renderBeneficiaries(beneficiariesState);
            const benCount = beneficiariesState.length || 0;
            document.getElementById('beneficiaryCount').textContent = benCount;
            const cryptoBenCount = document.getElementById('cryptoBeneficiaryCount');
            if (cryptoBenCount) cryptoBenCount.textContent = benCount;
            updateSaveButtonVisibility();
            await showAlertModal('Success', 'Share Holders saved successfully.', 'success');
        } else {
            await showAlertModal('Error', data.message || 'Failed to save beneficiaries', 'error');
        }
    } catch (e) {
        console.error(e);
        await showAlertModal('Error', 'Error saving beneficiaries', 'error');
    }
}

async function updateTrustName(newName) {
    try {
        const token = await getCsrfToken();
        const res = await fetch('../../api/user/trusts.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: trustId, trust_name: newName, csrf_token: token }),
        });
        const data = await res.json();
        if (data.success && data.trust) {
            document.getElementById('trustName').textContent = data.trust.trust_name || newName;
            currentTrust.trust_name = data.trust.trust_name || newName;
            if (isCryptoLayout) syncCryptoHeader(currentTrust);
            await showAlertModal('Success', 'LLC name updated successfully.', 'success');
        } else {
            await showAlertModal('Error', data.message || 'Failed to update LLC name', 'error');
        }
    } catch (e) {
        console.error(e);
        await showAlertModal('Error', 'Error updating LLC name', 'error');
    }
}

function updatePendingRegistrationBanner(trust) {
    const banner = document.getElementById('pendingRegistrationBanner');
    if (!banner) return;
    const statusRaw = (trust?.status || '').toString().toLowerCase();
    banner.classList.toggle('hidden', statusRaw !== 'pending');
}

function updateStatusUI(trust) {
    const statusRaw = (trust?.status || 'active').toString().toLowerCase();
    const paymentStatusRaw = (trust?.payment_status || '').toString().toLowerCase();

    const trustStatusEl = document.getElementById('trustStatus');
    const badgeEl = document.getElementById('statusBadge');
    const dotEl = document.getElementById('statusDot');

    if (!trustStatusEl || !badgeEl || !dotEl) return;

    let label = statusRaw;
    let badgeClass = 'font-bold';
    let dotClass = 'size-2 rounded-full';

    if (paymentStatusRaw === 'rejected') {
        label = 'payment rejected';
        badgeClass += ' text-error';
        dotClass += ' bg-error';
    } else if (statusRaw === 'pending') {
        label = 'pending';
        badgeClass += ' text-secondary';
        dotClass += ' bg-secondary animate-pulse';
    } else if (statusRaw === 'active') {
        label = 'active';
        badgeClass += ' text-deep-forest';
        dotClass += ' bg-deep-forest';
    } else if (statusRaw === 'inactive') {
        label = 'inactive';
        badgeClass += ' text-on-surface-variant';
        dotClass += ' bg-outline-variant';
    } else if (statusRaw === 'suspended') {
        label = 'suspended';
        badgeClass += ' text-error';
        dotClass += ' bg-error';
    } else {
        label = statusRaw;
        badgeClass += ' text-on-surface';
        dotClass += ' bg-outline-variant';
    }

    const pretty = label.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    trustStatusEl.textContent = pretty;
    badgeEl.textContent = pretty;
    badgeEl.className = badgeClass;
    dotEl.className = dotClass;

    const cryptoStatusEl = document.getElementById('cryptoTrustStatus');
    const cryptoBadgeEl = document.getElementById('cryptoStatusBadge');
    const cryptoDotEl = document.getElementById('cryptoStatusDot');
    if (cryptoStatusEl) {
        cryptoStatusEl.textContent = pretty;
        cryptoStatusEl.className = 'font-headline-lg text-headline-lg ' + (statusRaw === 'active' ? 'text-deep-forest' : statusRaw === 'pending' ? 'text-amber-700' : 'text-primary');
    }
    if (cryptoBadgeEl) cryptoBadgeEl.textContent = pretty;
    if (cryptoDotEl) {
        cryptoDotEl.className = 'w-3 h-3 rounded-full ' + (statusRaw === 'active' ? 'bg-deep-forest animate-pulse' : statusRaw === 'pending' ? 'bg-amber-500 animate-pulse' : 'bg-outline-variant');
    }

    const canChangeStatus = statusRaw === 'active';
    ['changeStatusBtn', 'cryptoChangeStatusBtn'].forEach((id) => {
        const btn = document.getElementById(id);
        if (btn) btn.classList.toggle('hidden', !canChangeStatus);
    });

    const isPending = statusRaw === 'pending';
    ['liquidateTrustBtn', 'cryptoLiquidateTrustBtn'].forEach((id) => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = isPending;
    });
    ['suspendTrustBtn', 'cryptoSuspendTrustBtn'].forEach((id) => {
        const btn = document.getElementById(id);
        if (btn) btn.classList.toggle('hidden', statusRaw !== 'active');
    });
}

async function updateTrustStatus(newStatus) {
    try {
        const token = await getCsrfToken();
        const res = await fetch('../../api/user/trusts.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: trustId, status: newStatus, csrf_token: token }),
        });
        const data = await res.json();
        if (data.success && data.trust) {
            currentTrust = data.trust;
            updateStatusUI(currentTrust);
            await showAlertModal('Success', `LLC status updated to ${newStatus}.`, 'success');
            await loadTrustData();
        } else {
            await showAlertModal('Error', data.message || 'Failed to update LLC status', 'error');
        }
    } catch (e) {
        console.error(e);
        await showAlertModal('Error', 'Error updating LLC status', 'error');
    }
}

function exportTrustReport() {
    if (!currentTrust) {
        showAlertModal('Error', 'Trust data not loaded yet', 'error');
        return;
    }

    const bi = currentTrust.business_info || currentTrust.trust_data?.business_info || {};
    const csv = [
        ['Trust Report', ''],
        ['LLC Name', currentTrust.trust_name || 'Untitled LLC'],
        ['Trust Type', currentTrust.trust_type || 'Standard'],
        ['Status', currentTrust.status || 'Active'],
        ['Created', currentTrust.created_at ? new Date(currentTrust.created_at).toLocaleDateString() : 'N/A'],
        ['Company Name', (bi.company_name || '').trim() || 'N/A'],
        ['Business Ending', bi.business_ending ? getBusinessEndingLabel(bi.business_ending) : 'N/A'],
        ['Company Display Name', formatCompanyDisplayName(bi) || 'N/A'],
        ['Formation State', bi.formation_state ? getFormationLabel(bi.formation_state) : 'N/A'],
        [''],
        ['Share Holders', ''],
        ['Name', 'Relationship', 'Email', 'Allocation %', 'Wallet Address']
    ];

    if (Array.isArray(currentTrust.beneficiaries)) {
        currentTrust.beneficiaries.forEach(ben => {
            csv.push([
                ben.name || '',
                ben.relationship || '',
                ben.email || '',
                ben.allocation || 0,
                ben.wallet_address || ''
            ]);
        });
    }

    const csvContent = csv.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `trust-report-${currentTrust.id}-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printTrustDetails() {
    window.print();
}

async function shareWithAdvisor() {
    if (!currentTrust) {
        await showAlertModal('Error', 'Trust data not loaded yet', 'error');
        return;
    }

    try {
        const email = await showInputModal(
            'Share with Advisor',
            'Enter the email address of your advisor:',
            'advisor@example.com',
            'Send'
        );

        if (email && email.includes('@')) {
            const subject = encodeURIComponent(`LLC Details: ${currentTrust.trust_name || 'Untitled LLC'}`);
            const body = encodeURIComponent(`Please review the details of my LLC.\n\nLLC ID: ${currentTrust.id}\nLLC Name: ${currentTrust.trust_name || 'Untitled LLC'}\nStatus: ${currentTrust.status || 'Active'}`);
            window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
            await showAlertModal('Success', `Share link prepared for ${email}. Your email client should open.`, 'success');
        } else if (email) {
            await showAlertModal('Error', 'Please enter a valid email address', 'error');
        }
    } catch (e) {
        // User cancelled
    }
}

document.addEventListener('DOMContentLoaded', loadTrustData);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
