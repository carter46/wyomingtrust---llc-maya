<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'Dashboard | ' . ($userName !== 'User' ? $userName : 'WyomingTrust');
$active_nav = 'dashboard';

include __DIR__ . '/includes/layout.php';
?>

<!-- Welcome -->
<section>
<h1 class="text-xl sm:text-2xl font-semibold text-primary mb-1.5 font-headline-md">Welcome Back, <span id="userName"><?php echo escape_html($userName); ?></span>.</h1>
<p class="text-sm sm:text-base text-on-surface-variant">Manage your trusts, beneficiaries, and estate planning from one secure dashboard.</p>
</section>

<!-- Key Metrics (3 cards) -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
<div class="metric-card-gradient p-6 rounded-2xl card-hover flex flex-col justify-between min-h-[7.5rem] text-on-primary shadow-lg dashboard-metric-card">
<span class="text-xs md:text-sm uppercase tracking-widest text-on-primary/70 font-bold">Active Trusts</span>
<div class="min-w-0">
<div class="dashboard-metric-value-wrap">
<p class="dashboard-metric-value text-on-primary" id="trustCount" data-fit-max="36" data-fit-min="16">0</p>
</div>
<p class="text-sm md:text-base text-on-primary/80 mt-1 font-medium">Securely Managed</p>
</div>
</div>
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant card-hover flex flex-col justify-between min-h-[7.5rem]">
<span class="text-xs md:text-sm uppercase tracking-widest text-on-surface-variant font-bold">Beneficiaries</span>
<div class="min-w-0">
<div class="dashboard-metric-value-wrap">
<p class="dashboard-metric-value text-primary" id="beneficiaryCount" data-fit-max="36" data-fit-min="16">0</p>
</div>
<p class="text-sm md:text-base text-on-surface-variant mt-1 font-medium">Assigned protections</p>
</div>
</div>
<div class="dashboard-metric-card bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant card-hover flex flex-col justify-between min-h-[7.5rem]">
<span class="text-xs md:text-sm uppercase tracking-widest text-on-surface-variant font-bold">Last Updated</span>
<div class="min-w-0">
<div class="dashboard-metric-value-wrap">
<p class="dashboard-metric-value text-primary" id="lastUpdated" data-fit-max="28" data-fit-min="12">—</p>
</div>
<div class="flex items-center gap-2 text-deep-forest mt-1">
<span class="w-2 h-2 rounded-full bg-deep-forest animate-pulse"></span>
<p class="text-sm md:text-base font-medium">System Sync</p>
</div>
</div>
</div>
</section>

<!-- My Trusts (list) -->
<section>
<div class="flex items-center justify-between mb-6">
<h2 class="font-headline-md text-headline-md text-primary">My Trusts</h2>
<a class="font-label-md text-label-md text-secondary hover:underline underline-offset-4 inline-flex items-center gap-1" href="manage-trust.php">View All <?php echo wt_icon('arrow-forward', 'w-4 h-4'); ?></a>
</div>
<div id="trustsContainer" class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden divide-y divide-outline-variant/30">
<div class="p-10 text-center text-on-surface-variant">Loading trusts...</div>
</div>
</section>

<!-- Payment History -->
<section class="pb-20">
<div class="flex items-center justify-between mb-6">
<h2 class="font-headline-md text-headline-md text-primary">Payment History</h2>
<a class="font-label-md text-label-md text-secondary hover:underline underline-offset-4 inline-flex items-center gap-1" href="billing.php">View All <?php echo wt_icon('arrow-forward', 'w-4 h-4'); ?></a>
</div>
<div id="paymentsContainer" class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
<div class="p-10 text-center text-on-surface-variant">Loading payments...</div>
</div>
</section>

<script>
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    if (payment.record_type === 'crypto_deposit') {
        return '<span class="text-on-surface-variant">—</span>';
    }
    return '<span class="text-on-surface-variant">—</span>';
}

function paymentStatusClass(status) {
    const s = String(status || '').toLowerCase();
    if (s === 'completed') return 'text-deep-forest';
    if (s === 'pending') return 'text-secondary';
    if (s === 'rejected') return 'text-error';
    return 'text-on-surface-variant';
}

function updateLastSynced() {
    const el = document.getElementById('lastUpdated');
    if (el) el.textContent = 'Just Now';
}

function countUniqueBeneficiaries(trusts) {
    const seen = new Set();
    trusts.forEach(t => {
        const bens = Array.isArray(t.beneficiaries) ? t.beneficiaries :
            (Array.isArray(t.trust_data?.beneficiaries) ? t.trust_data.beneficiaries : []);
        bens.forEach(b => {
            const key = String(b.email || b.full_name || b.name || '').trim().toLowerCase();
            if (key) seen.add(key);
        });
    });
    return seen.size;
}

async function loadDashboardData() {
    const trustsContainer = document.getElementById('trustsContainer');
    const paymentsContainer = document.getElementById('paymentsContainer');
    const trustCountEl = document.getElementById('trustCount');
    const beneficiaryEl = document.getElementById('beneficiaryCount');

    const showTrustsError = (message) => {
        if (trustsContainer) {
            trustsContainer.innerHTML = `<div class="p-10 text-center text-error">${escapeHtml(message)}</div>`;
        }
        if (trustCountEl) trustCountEl.textContent = '0';
        if (beneficiaryEl) beneficiaryEl.textContent = '0';
    };

    try {
        try {
            const profileResponse = await fetch('../../api/user/profile.php', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (profileResponse.ok) {
                const profileData = await profileResponse.json();
                if (profileData.success && profileData.user?.full_name) {
                    const nameEl = document.getElementById('userName');
                    if (nameEl) nameEl.textContent = profileData.user.full_name;
                }
            }
        } catch (profileError) {
            console.error('Error loading profile:', profileError);
        }

        const trustsResponse = await fetch('../../api/user/trusts.php', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!trustsResponse.ok) {
            showTrustsError(`Failed to load trusts (HTTP ${trustsResponse.status})`);
        } else {
            let trustsData;
            try {
                trustsData = await trustsResponse.json();
            } catch (jsonError) {
                showTrustsError('Invalid response from server');
                trustsData = null;
            }

            if (trustsData && trustsData.success && Array.isArray(trustsData.trusts)) {
                const activeTrusts = trustsData.trusts.filter(t => (t.status || '').toLowerCase() === 'active');
                if (trustCountEl) trustCountEl.textContent = String(activeTrusts.length);

                const uniqueBeneficiaries = countUniqueBeneficiaries(trustsData.trusts);
                if (beneficiaryEl) beneficiaryEl.textContent = String(uniqueBeneficiaries);

                if (activeTrusts.length > 0) {
                    renderTrusts(activeTrusts);
                } else if (trustsData.trusts.length > 0) {
                    if (trustsContainer) {
                        trustsContainer.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No active trusts yet. <a href="manage-trust.php" class="text-secondary font-semibold hover:underline">View pending trusts</a></div>';
                    }
                } else {
                    renderTrusts([]);
                }
            } else {
                if (trustCountEl) trustCountEl.textContent = '0';
                if (beneficiaryEl) beneficiaryEl.textContent = '0';
                if (trustsContainer) {
                    trustsContainer.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No trusts yet. <a href="../../onboarding/onboarding.php" class="text-secondary font-semibold hover:underline">Create your first trust</a></div>';
                }
            }
        }

        try {
            const billingResponse = await fetch('../../api/user/billing.php', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (billingResponse.ok) {
                const billingData = await billingResponse.json();
                if (billingData.success && Array.isArray(billingData.payments)) {
                    renderPayments(billingData.payments.slice(0, 5));
                } else {
                    renderPayments([]);
                }
            } else {
                paymentsContainer.innerHTML = '<div class="p-10 text-center text-on-surface-variant">Unable to load payment history.</div>';
            }
        } catch (billingError) {
            console.error('Error loading payments:', billingError);
            paymentsContainer.innerHTML = '<div class="p-10 text-center text-on-surface-variant">Unable to load payment history.</div>';
        }

        updateLastSynced();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        showTrustsError('Error loading dashboard data. Please refresh the page.');
    }
}

function renderTrusts(trusts) {
    const container = document.getElementById('trustsContainer');
    if (!trusts || trusts.length === 0) {
        container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No trusts yet. <a href="../../onboarding/onboarding.php" class="text-secondary font-semibold hover:underline">Create your first trust</a></div>';
        return;
    }

    container.innerHTML = trusts.map(trust => {
        const trustName = trust.trust_name || trust.service_name || 'Untitled Trust';
        const serviceName = trust.service_name || '';
        const createdDate = formatDateSafe(trust.created_at);
        const trustId = trust.id || 0;
        const status = (trust.status || 'pending').toString();
        const showBadge = trust.trust_name && trust.trust_name !== serviceName;

        return `
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 md:p-6 hover:bg-surface transition-colors">
                <div class="flex items-center gap-4 min-w-0 flex-1">
                    <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center shrink-0">
                        ${typeof wtIcon === 'function' ? wtIcon('shield', 'w-6 h-6 text-primary') : ''}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-primary text-lg truncate">${escapeHtml(trustName)}</p>
                            ${showBadge ? `<span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-secondary/10 text-secondary">${escapeHtml(serviceName)}</span>` : ''}
                        </div>
                        <p class="text-sm text-on-surface-variant mt-0.5">Status: <span class="capitalize font-medium">${escapeHtml(status)}</span> · Created ${createdDate}</p>
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="button" onclick="window.location.href='manage-trust.php?id=${trustId}'" class="px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary/90 transition-colors">Manage</button>
                    <button type="button" onclick="window.location.href='manage-trust.php?id=${trustId}'" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors">Details</button>
                </div>
            </div>
        `;
    }).join('');
}

function renderPayments(payments) {
    const container = document.getElementById('paymentsContainer');
    if (!payments.length) {
        container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No payment or deposit records yet.</div>';
        return;
    }

    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="px-6 md:px-8 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Date</th>
                        <th class="px-6 md:px-8 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Description</th>
                        <th class="px-6 md:px-8 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Amount</th>
                        <th class="px-6 md:px-8 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Status</th>
                        <th class="px-6 md:px-8 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest text-right">Trust</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    ${payments.map(payment => `
                        <tr class="hover:bg-surface transition-colors">
                            <td class="px-6 md:px-8 py-5 text-on-surface">${formatDateSafe(payment.created_at)}</td>
                            <td class="px-6 md:px-8 py-5 font-medium text-primary">${escapeHtml(payment.service_name || 'Trust Service')}</td>
                            <td class="px-6 md:px-8 py-5 font-bold">${escapeHtml(formatPaymentAmount(payment))}</td>
                            <td class="px-6 md:px-8 py-5"><span class="font-medium capitalize ${paymentStatusClass(payment.payment_status)}">${escapeHtml(payment.payment_status || 'unknown')}</span></td>
                            <td class="px-6 md:px-8 py-5 text-right">${paymentTrustLink(payment)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
