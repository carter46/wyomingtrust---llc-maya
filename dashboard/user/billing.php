<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'Billing | WyomingTrust';
$active_nav = 'billing';

include __DIR__ . '/includes/layout.php';
?>

<section>
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Billing</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">Review your trust service payments, crypto deposits, and billing history.</p>
</section>

<section id="lastPaymentSection" class="hidden">
<div class="metric-card-gradient p-8 md:p-10 rounded-2xl text-on-primary shadow-lg dashboard-metric-card">
<p class="text-sm md:text-base uppercase tracking-widest text-on-primary/70 font-bold mb-3">Last Payment</p>
<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 min-w-0">
<div class="min-w-0 flex-1">
<div class="dashboard-metric-value-wrap">
<p class="dashboard-metric-value text-on-primary leading-tight" id="lastPaymentAmount" data-fit-max="48" data-fit-min="14">—</p>
</div>
<p class="text-lg md:text-xl text-on-primary/80 mt-2 break-words" id="lastPaymentService">—</p>
</div>
<div class="text-left md:text-right">
<p class="text-base text-on-primary/70" id="lastPaymentDate">—</p>
<p class="text-sm font-medium mt-1" id="lastPaymentStatus">—</p>
<p class="text-sm text-on-primary/70 mt-1" id="lastPaymentMethod">—</p>
</div>
</div>
</div>
</section>

<section class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm">
<div class="px-6 md:px-8 py-6 border-b border-outline-variant">
<h2 class="font-headline-md text-headline-md text-primary">Payment History</h2>
</div>
<div id="billingContainer" class="p-10 text-center text-on-surface-variant">Loading billing history...</div>
</section>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateSafe(value) {
    if (!value) return 'N/A';
    try {
        const s = String(value).trim();
        if (s === '' || s === '0000-00-00 00:00:00' || s === '0000-00-00') return 'N/A';
        const isoish = s.includes(' ') && !s.includes('T') ? s.replace(' ', 'T') : s;
        const d = new Date(isoish);
        if (Number.isNaN(d.getTime())) return 'N/A';
        return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch (e) {
        return 'N/A';
    }
}

function formatAmount(amount, isFree) {
    if (isFree) return 'Free';
    return '$' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatCoinAmountDisplay(amount) {
    const amt = parseFloat(amount) || 0;
    if (amt >= 1) return amt.toFixed(4);
    if (amt >= 0.01) return amt.toFixed(6);
    return amt.toFixed(8);
}

function formatPaymentAmount(payment) {
    if (payment.record_type === 'crypto_deposit' || payment.record_type === 'crypto_liquidation') {
        const sym = payment.coin_symbol || '';
        const coinStr = formatCoinAmountDisplay(payment.coin_amount);
        const usd = parseFloat(payment.amount_usd) || 0;
        if (usd > 0) {
            return `${coinStr} ${sym} (≈ ${formatAmount(usd, false)})`;
        }
        return `${coinStr} ${sym}`;
    }
    return formatAmount(payment.amount, payment.is_free);
}

function paymentTrustLink(payment) {
    if (payment.trust_id) {
        return `<a href="manage-trust.php?id=${payment.trust_id}" class="text-secondary font-semibold hover:underline">View</a>`;
    }
    return '<span class="text-on-surface-variant">—</span>';
}

function statusLabel(status) {
    const s = String(status || '').toLowerCase();
    if (s === 'completed') return { text: 'Completed', class: 'text-deep-forest' };
    if (s === 'pending') return { text: 'Pending', class: 'text-secondary' };
    if (s === 'rejected') return { text: 'Rejected', class: 'text-error' };
    return { text: status || 'Unknown', class: 'text-on-surface-variant' };
}

function renderLastPayment(payment) {
    const section = document.getElementById('lastPaymentSection');
    if (!payment) {
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');
    document.getElementById('lastPaymentAmount').textContent = formatPaymentAmount(payment);
    document.getElementById('lastPaymentService').textContent = payment.service_name || 'LLC Service';
    document.getElementById('lastPaymentDate').textContent = formatDateSafe(payment.created_at);
    const status = statusLabel(payment.payment_status);
    document.getElementById('lastPaymentStatus').textContent = status.text;
    document.getElementById('lastPaymentStatus').className = 'text-sm font-medium mt-1 ' + status.class;
    const methodText = (payment.record_type === 'crypto_deposit' || payment.record_type === 'crypto_liquidation')
        ? 'Paid via Cryptocurrency'
        : (payment.payment_method_name
            ? 'Paid via ' + payment.payment_method_name
            : (payment.is_free ? 'No payment required' : 'Payment method not recorded'));
    document.getElementById('lastPaymentMethod').textContent = methodText;
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function renderBillingHistory(payments) {
    const container = document.getElementById('billingContainer');
    if (!payments.length) {
        container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No billing records yet. Trust payments, crypto deposits, and liquidations appear here.</div>';
        return;
    }

    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Date</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Description</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Amount</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Method</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Status</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest text-right">Trust</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    ${payments.map(payment => {
                        const status = statusLabel(payment.payment_status);
                        const method = (payment.record_type === 'crypto_deposit' || payment.record_type === 'crypto_liquidation')
                            ? 'Cryptocurrency'
                            : (payment.payment_method_name || (payment.is_free ? '—' : 'Not recorded'));
                        return `
                            <tr class="hover:bg-surface transition-colors">
                                <td class="px-6 md:px-8 py-6 text-on-surface">${formatDateSafe(payment.created_at)}</td>
                                <td class="px-6 md:px-8 py-6 font-medium text-primary">${escapeHtml(payment.service_name || 'LLC Service')}</td>
                                <td class="px-6 md:px-8 py-6 font-bold">${escapeHtml(formatPaymentAmount(payment))}</td>
                                <td class="px-6 md:px-8 py-6 text-on-surface-variant">${escapeHtml(method)}</td>
                                <td class="px-6 md:px-8 py-6"><span class="font-medium ${status.class}">${status.text}</span></td>
                                <td class="px-6 md:px-8 py-6 text-right">${paymentTrustLink(payment)}</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        </div>
    `;
}

async function loadBilling() {
    const container = document.getElementById('billingContainer');
    try {
        const response = await fetch('../../api/user/billing.php', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            container.innerHTML = `<div class="p-10 text-center text-error">Failed to load billing (HTTP ${response.status})</div>`;
            return;
        }

        const data = await response.json();
        if (data.success) {
            renderLastPayment(data.last_payment);
            renderBillingHistory(Array.isArray(data.payments) ? data.payments : []);
        } else {
            container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No billing records found.</div>';
        }
    } catch (error) {
        console.error(error);
        container.innerHTML = '<div class="p-10 text-center text-error">Error loading billing. Please refresh.</div>';
    }
}

document.addEventListener('DOMContentLoaded', loadBilling);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
