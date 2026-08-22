<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$checkoutType = sanitize_text($_GET['type'] ?? '');
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$assetIdParam = isset($_GET['asset_id']) ? sanitize_text($_GET['asset_id']) : '';
$page_title = 'Checkout | WyomingTrust';
$active_nav = $trustIdParam > 0 ? 'trusts' : '';

$allowedTypes = ['liquidation', 'asset_funding', 'trust_value', 'trust_liquidation'];
if (!in_array($checkoutType, $allowedTypes, true)) {
    header('Location: dashboard.php');
    exit;
}
if ($checkoutType === 'liquidation' && $coinKeyParam === '') {
    header('Location: dashboard.php');
    exit;
}
if (in_array($checkoutType, ['asset_funding', 'trust_value', 'trust_liquidation'], true) && $trustIdParam <= 0) {
    header('Location: dashboard.php');
    exit;
}
if ($checkoutType === 'asset_funding' && $assetIdParam === '') {
    header('Location: dashboard.php');
    exit;
}

include __DIR__ . '/includes/layout.php';
?>

<section class="w-full min-w-0 max-w-6xl mx-auto">
<?php if ($trustIdParam > 0): ?>
<a href="<?php echo $checkoutType === 'liquidation' && $coinKeyParam !== '' ? 'asset-detail.php?coin_key=' . escape_html($coinKeyParam) . '&trust_id=' . $trustIdParam : 'manage-trust.php?id=' . $trustIdParam; ?>" class="inline-flex items-center gap-1 text-secondary font-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back
</a>
<?php endif; ?>

<h1 class="font-headline-lg text-headline-lg text-primary mb-2" id="checkoutTitle">Checkout</h1>
<p class="font-body-md text-on-surface-variant mb-6" id="checkoutDescription">Complete your payment using an available payment method.</p>

<div id="checkoutLoading" class="text-center py-16">
<div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent mb-4"></div>
<p class="text-on-surface-variant text-sm">Loading checkout...</p>
</div>

<div id="checkoutError" class="hidden bg-error-container/30 border border-error/20 rounded-2xl p-6 text-center">
<p class="text-on-error-container font-semibold mb-2" id="checkoutErrorMessage">Unable to load checkout.</p>
<a href="dashboard.php" class="text-secondary font-semibold hover:underline">Return to Dashboard</a>
</div>

<div id="checkoutContent" class="hidden">
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start">
<div class="lg:col-span-3 space-y-6">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6">
<h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center gap-2">
<?php echo wt_icon('payments', 'text-secondary'); ?>
Order Details
</h2>
<div class="space-y-3 text-sm">
<div class="flex justify-between gap-4">
<span class="text-on-surface-variant" id="checkoutItemLabel">Item</span>
<span class="font-semibold text-primary" id="checkoutAssetName">—</span>
</div>
<div class="flex justify-between gap-4">
<span class="text-on-surface-variant">Purpose</span>
<span class="font-semibold text-primary" id="checkoutPurposeLabel">Payment</span>
</div>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
<div class="p-6 border-b border-outline-variant">
<h2 class="font-headline-md text-headline-md text-primary flex items-center gap-2">
<?php echo wt_icon('payments', 'text-secondary'); ?>
Payment
</h2>
<p class="text-on-surface-variant text-sm mt-1">Select a payment method, review the details, then confirm.</p>
</div>
<div class="p-6" id="paymentFlowContainer"></div>
</div>

<div class="bg-secondary/10 rounded-2xl border border-secondary/20 p-5 flex items-start gap-4">
<?php echo wt_icon('lock', 'text-secondary flex-shrink-0'); ?>
<div>
<p class="font-semibold text-primary">Secure checkout</p>
<p class="text-sm text-on-surface-variant mt-1" id="checkoutSecureNote">Your payment will be reviewed by an administrator before the value is credited.</p>
</div>
</div>
</div>

<aside class="lg:col-span-2">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden sticky top-24">
<div class="p-6 border-b border-outline-variant">
<h2 class="font-headline-md text-headline-md text-primary">Order Summary</h2>
</div>
<div class="p-6 space-y-4">
<div class="flex justify-between text-sm">
<span class="text-on-surface-variant" id="summaryLineLabel">Amount</span>
<span class="text-primary font-medium" id="summaryFeeAmount">$0.00</span>
</div>
<div class="pt-4 border-t border-outline-variant flex justify-between items-center">
<span class="text-primary font-bold text-lg">Total</span>
<span class="text-secondary font-black text-2xl" id="summaryTotalAmount">$0.00</span>
</div>
</div>
<div class="px-6 pb-6" id="summaryActions">
<p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider mb-3">Select Payment Method</p>
<div id="paymentMethodsContainer" class="space-y-3 mb-6">
<div class="text-center py-4 text-on-surface-variant text-sm">Loading payment methods...</div>
</div>
<button type="button" id="continueToPaymentDetailsBtn" disabled class="w-full bg-primary text-on-primary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
<?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
Continue to Payment Details
</button>
</div>
</div>
</aside>
</div>
</div>
</section>

<script>
const checkoutType = <?php echo json_encode($checkoutType); ?>;
const coinKey = <?php echo json_encode($coinKeyParam); ?>;
const trustId = <?php echo (int) $trustIdParam; ?>;
const assetId = <?php echo json_encode($assetIdParam); ?>;

let checkoutData = null;
let paymentMethods = [];
let paymentStage = 'select';
let selectedPaymentMethodId = null;
let csrfToken = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    const res = await fetch('../../api/session.php', { credentials: 'same-origin' });
    const data = await res.json();
    csrfToken = data.csrf_token || null;
    return csrfToken;
}

function showError(message) {
    document.getElementById('checkoutLoading').classList.add('hidden');
    document.getElementById('checkoutContent').classList.add('hidden');
    document.getElementById('checkoutError').classList.remove('hidden');
    document.getElementById('checkoutErrorMessage').textContent = message;
}

function buildContinueUrl() {
    if (checkoutData?.continue_url) return checkoutData.continue_url;
    if (checkoutType === 'liquidation') {
        const params = new URLSearchParams({ coin_key: coinKey, mode: 'liquidate' });
        if (trustId > 0) params.set('trust_id', String(trustId));
        return `send.php?${params.toString()}`;
    }
    if (trustId > 0) return `manage-trust.php?id=${trustId}`;
    return 'dashboard.php';
}

function buildCheckoutQuery() {
    const params = new URLSearchParams({ type: checkoutType });
    if (coinKey) params.set('coin_key', coinKey);
    if (trustId > 0) params.set('trust_id', String(trustId));
    if (assetId) params.set('asset_id', assetId);
    return params.toString();
}

function formatUsd(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

function shouldSkipCheckout(data) {
    if (checkoutType === 'liquidation' || checkoutType === 'trust_liquidation') {
        if (!data.has_fee) return true;
        return !!(data.payment_satisfied || data.fee_paid);
    }
    if (checkoutType === 'asset_funding' || checkoutType === 'trust_value') {
        return data.funding_status === 'funded' || data.payment_satisfied === true;
    }
    return false;
}

function isPaymentPendingApproval(data) {
    if (data.payment_satisfied || data.fee_paid || data.funding_status === 'funded') return false;
    if (data.already_submitted && data.payment_status === 'pending') return true;
    if ((checkoutType === 'asset_funding' || checkoutType === 'trust_value') && data.funding_status === 'pending') {
        return true;
    }
    return false;
}

function showPendingApprovalState(data) {
    document.getElementById('checkoutTitle').textContent = data.title || 'Checkout';
    document.getElementById('checkoutDescription').textContent = 'Your payment has been submitted and is awaiting administrator approval.';
    document.getElementById('checkoutItemLabel').textContent = checkoutType === 'trust_value' ? 'Trust' : 'Asset';
    document.getElementById('checkoutAssetName').textContent = data.item_label || data.asset_name || '—';
    document.getElementById('checkoutPurposeLabel').textContent = data.purpose_label || 'Payment';
    document.getElementById('summaryLineLabel').textContent = data.purpose_label || 'Amount';
    const amount = parseFloat(data.amount ?? data.fee ?? 0);
    document.getElementById('summaryFeeAmount').textContent = formatUsd(amount);
    document.getElementById('summaryTotalAmount').textContent = formatUsd(amount);

    const flow = document.getElementById('paymentFlowContainer');
    if (flow) {
        flow.innerHTML = `
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <p class="font-bold text-primary">Payment pending approval</p>
                <p class="text-sm text-on-surface-variant mt-1">An administrator must approve your payment before you can continue.</p>
                <a href="${escapeHtml(buildContinueUrl())}" class="inline-flex mt-4 text-sm font-semibold text-secondary hover:underline">Return without paying again</a>
            </div>
        `;
    }
    const summaryActions = document.getElementById('summaryActions');
    if (summaryActions) summaryActions.classList.add('hidden');

    document.getElementById('checkoutLoading').classList.add('hidden');
    document.getElementById('checkoutContent').classList.remove('hidden');
}

async function loadCheckout() {
    try {
        const res = await fetch(`../../api/user/checkout.php?${buildCheckoutQuery()}`, { credentials: 'same-origin' });
        const data = await res.json();

        if (!data.success) {
            showError(data.message || 'Failed to load checkout');
            return;
        }

        checkoutData = data;

        if (shouldSkipCheckout(data)) {
            window.location.href = buildContinueUrl();
            return;
        }

        if (isPaymentPendingApproval(data)) {
            showPendingApprovalState(data);
            return;
        }

        const amount = parseFloat(data.amount ?? data.fee ?? 0);
        document.getElementById('checkoutDescription').textContent = data.description || '';
        document.getElementById('checkoutItemLabel').textContent = checkoutType === 'trust_value' ? 'Trust' : 'Asset';
        document.getElementById('checkoutAssetName').textContent = data.item_label || data.asset_name || '—';
        document.getElementById('checkoutPurposeLabel').textContent = data.purpose_label || 'Payment';
        document.getElementById('summaryLineLabel').textContent = data.purpose_label || 'Amount';
        document.getElementById('summaryFeeAmount').textContent = formatUsd(amount);
        document.getElementById('summaryTotalAmount').textContent = formatUsd(amount);

        document.getElementById('checkoutLoading').classList.add('hidden');
        document.getElementById('checkoutContent').classList.remove('hidden');

        renderPaymentFlow();
        await loadPaymentMethods();
    } catch (error) {
        console.error(error);
        showError('Error loading checkout');
    }
}

async function loadPaymentMethods() {
    try {
        const res = await fetch('../../api/payment-methods.php');
        const data = await res.json();
        if (data.success && data.methods) {
            paymentMethods = data.methods;
            renderPaymentMethods(data.methods);
        } else {
            document.getElementById('paymentMethodsContainer').innerHTML =
                '<div class="text-center py-4 text-error text-sm">Failed to load payment methods</div>';
        }
    } catch (error) {
        console.error(error);
        document.getElementById('paymentMethodsContainer').innerHTML =
            '<div class="text-center py-4 text-error text-sm">Error loading payment methods</div>';
    }
}

function renderPaymentMethods(methods) {
    const container = document.getElementById('paymentMethodsContainer');
    if (!container) return;

    if (!methods || methods.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-on-surface-variant text-sm">No payment methods available</div>';
        return;
    }

    const iconFor = (type) => {
        if (type === 'crypto') return 'currency_bitcoin';
        if (type === 'bank_transfer') return 'account_balance';
        if (type === 'paypal') return 'payments';
        return 'credit_card';
    };

    container.innerHTML = methods.map(m => `
        <button type="button" onclick="selectPaymentMethod(${m.id})"
            class="payment-method-card w-full rounded-xl border p-4 text-left transition-all hover:shadow-sm ${
                selectedPaymentMethodId == m.id
                    ? 'border-secondary ring-2 ring-secondary/30 bg-secondary/5'
                    : 'border-outline-variant bg-surface-container-low'
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
                <input type="radio" name="selected_payment_method" value="${m.id}" ${selectedPaymentMethodId == m.id ? 'checked' : ''} />
            </div>
        </button>
    `).join('');
}

function selectPaymentMethod(methodId) {
    selectedPaymentMethodId = methodId;
    paymentStage = 'select';
    renderPaymentMethods(paymentMethods);
    const btn = document.getElementById('continueToPaymentDetailsBtn');
    if (btn) btn.disabled = false;
}

function getSelectedPaymentMethod() {
    return paymentMethods.find(m => m.id == selectedPaymentMethodId) || null;
}

function renderPaymentDetails(method, amount) {
    const config = method.config_data || {};
    let detailsHtml = '';

    if (method.method_type === 'crypto') {
        const walletAddress = config.wallet_address || '';
        const qrCode = config.qr_code || '';
        const coinName = config.coin_name || method.method_name;
        const networkType = config.network_type || '';
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant p-4">
                <p class="font-bold text-primary mb-2">Crypto payment details</p>
                <p class="text-sm text-on-surface-variant">Send <strong class="text-primary">${formatUsd(amount)}</strong> using ${escapeHtml(coinName)}${networkType ? ` (${escapeHtml(networkType)})` : ''}.</p>
                ${walletAddress ? `
                    <div class="mt-4 bg-surface-container rounded-lg p-3">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide">Wallet Address</p>
                            <button type="button" onclick="copyToClipboard('${escapeHtml(walletAddress)}')" class="text-secondary text-xs font-semibold">Copy</button>
                        </div>
                        <p class="text-xs font-mono text-primary break-all">${escapeHtml(walletAddress)}</p>
                    </div>
                ` : ''}
                ${qrCode ? `
                    <div class="mt-4 flex flex-col sm:flex-row items-center gap-4">
                        <img src="../../${qrCode}" alt="QR Code" class="max-w-40 max-h-40 border border-outline-variant rounded-lg p-2 bg-white">
                        <p class="text-xs text-on-surface-variant">Scan to pay.</p>
                    </div>
                ` : ''}
            </div>
        `;
    } else if (method.method_type === 'bank_transfer') {
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant p-4">
                <p class="font-bold text-primary mb-2">Bank transfer details</p>
                <p class="text-sm text-on-surface-variant mb-3">Transfer <strong class="text-primary">${formatUsd(amount)}</strong> using the details below.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><p class="text-xs text-on-surface-variant uppercase font-bold">Bank</p><p class="text-primary">${escapeHtml(config.bank_name || '')}</p></div>
                    <div><p class="text-xs text-on-surface-variant uppercase font-bold">Account Name</p><p class="text-primary">${escapeHtml(config.account_name || '')}</p></div>
                    ${config.account_number_masked ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold">Account #</p><p class="text-primary font-mono">${escapeHtml(config.account_number_masked)}</p></div>` : ''}
                    ${config.routing_number ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold">Routing</p><p class="text-primary font-mono">${escapeHtml(config.routing_number)}</p></div>` : ''}
                    ${config.swift_code ? `<div><p class="text-xs text-on-surface-variant uppercase font-bold">SWIFT/BIC</p><p class="text-primary font-mono">${escapeHtml(config.swift_code)}</p></div>` : ''}
                </div>
                ${config.additional_details ? `<div class="mt-3 text-sm"><p class="text-xs text-on-surface-variant uppercase font-bold">Notes</p><p class="text-primary whitespace-pre-wrap">${escapeHtml(config.additional_details)}</p></div>` : ''}
            </div>
        `;
    } else if (method.method_type === 'paypal') {
        const paypalEmail = config.paypal_email || config.paypal_tag || '';
        detailsHtml = `
            <div class="rounded-xl border border-outline-variant p-4">
                <p class="font-bold text-primary mb-2">PayPal details</p>
                <p class="text-sm text-on-surface-variant">Send <strong class="text-primary">${formatUsd(amount)}</strong> to:</p>
                <p class="mt-2 font-mono text-primary">${escapeHtml(paypalEmail || 'Not configured')}</p>
            </div>
        `;
    } else {
        detailsHtml = '<div class="rounded-xl border border-outline-variant p-4 text-sm text-on-surface-variant">Payment method details not available.</div>';
    }

    return `
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-primary truncate">${escapeHtml(method.method_name || 'Payment Method')}</p>
                    <p class="text-xs text-on-surface-variant">Amount due: <strong class="text-primary">${formatUsd(amount)}</strong></p>
                </div>
                <button type="button" onclick="backToPaymentSelection()" class="text-sm font-semibold text-on-surface-variant hover:text-primary">Change</button>
            </div>
            ${detailsHtml}
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-on-surface"><strong>Note:</strong> Payment will be approved once received by the admin.</p>
            </div>
        </div>
    `;
}

function renderPaymentFlow() {
    const flow = document.getElementById('paymentFlowContainer');
    const summaryActions = document.getElementById('summaryActions');
    const amount = checkoutData ? (checkoutData.amount ?? checkoutData.fee ?? 0) : 0;

    if (paymentStage === 'confirmed') {
        flow.innerHTML = `
            <div class="rounded-xl border border-green-200 bg-green-50 p-5">
                <p class="font-bold text-primary">Payment submitted</p>
                <p class="text-sm text-on-surface-variant mt-1">Your payment is pending admin approval.</p>
            </div>
        `;
        summaryActions.innerHTML = `
            <button type="button" onclick="continueAfterCheckout()" class="w-full bg-primary text-on-primary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3">
                ${wtIcon('arrow-forward', 'w-5 h-5')}
                Continue
            </button>
        `;
        return;
    }

    if (paymentStage === 'details') {
        const method = getSelectedPaymentMethod();
        flow.innerHTML = method
            ? renderPaymentDetails(method, amount)
            : '<p class="text-sm text-on-surface-variant">Select a payment method to continue.</p>';
        summaryActions.innerHTML = `
            <button type="button" id="confirmPaymentBtn" onclick="confirmPayment()" class="w-full bg-primary text-on-primary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3">
                ${wtIcon('shield', 'w-5 h-5')}
                I've made this payment
            </button>
            <button type="button" onclick="backToPaymentSelection()" class="w-full mt-3 border border-outline-variant hover:bg-surface-container-low text-primary font-semibold py-3 px-6 rounded-xl">
                Change payment method
            </button>
        `;
        return;
    }

    flow.innerHTML = `
        <div class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
            <p class="font-bold text-primary">Select a payment method</p>
            <p class="text-sm text-on-surface-variant mt-1">Choose a payment method in the <strong>Order Summary</strong> panel, then click <strong>Continue to Payment Details</strong>.</p>
        </div>
    `;
    summaryActions.innerHTML = `
        <p class="text-on-surface-variant text-xs uppercase font-bold tracking-wider mb-3">Select Payment Method</p>
        <div id="paymentMethodsContainer" class="space-y-3 mb-6">
            <div class="text-center py-4 text-on-surface-variant text-sm">Loading payment methods...</div>
        </div>
        <button type="button" id="continueToPaymentDetailsBtn" onclick="goToPaymentDetails()" ${selectedPaymentMethodId ? '' : 'disabled'} class="w-full bg-primary text-on-primary hover:opacity-90 font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
            ${wtIcon('arrow-forward', 'w-5 h-5')}
            Continue to Payment Details
        </button>
    `;
    if (paymentMethods.length > 0) {
        renderPaymentMethods(paymentMethods);
    }
}

function goToPaymentDetails() {
    if (!selectedPaymentMethodId) return;
    paymentStage = 'details';
    renderPaymentFlow();
}

function backToPaymentSelection() {
    paymentStage = 'select';
    renderPaymentFlow();
}

async function confirmPayment() {
    const btn = document.getElementById('confirmPaymentBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Submitting...';
    }

    try {
        const token = await getCsrfToken();
        const res = await fetch('../../api/user/checkout.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token || '' },
            body: JSON.stringify({
                type: checkoutType,
                coin_key: coinKey || undefined,
                trust_id: trustId > 0 ? trustId : null,
                asset_id: assetId || undefined,
                payment_method_id: selectedPaymentMethodId,
                csrf_token: token,
            }),
        });
        const data = await res.json();

        if (!data.success) {
            alert(data.message || 'Failed to submit payment');
            if (btn) {
                btn.disabled = false;
                btn.textContent = "I've made this payment";
            }
            return;
        }

        paymentStage = 'confirmed';
        renderPaymentFlow();
    } catch (error) {
        console.error(error);
        alert('Error submitting payment');
        if (btn) {
            btn.disabled = false;
            btn.textContent = "I've made this payment";
        }
    }
}

function continueAfterCheckout() {
    window.location.href = buildContinueUrl();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard'));
}

document.addEventListener('DOMContentLoaded', loadCheckout);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
