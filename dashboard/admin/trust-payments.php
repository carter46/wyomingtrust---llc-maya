<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Trust Payment Approvals';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderTrustPaymentsContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Payment Approvals</h1>
    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-2">Review trust service payments and crypto deposit submissions</p>
</div>

<div class="flex gap-2 mb-4 border-b border-slate-200 dark:border-slate-700">
    <button type="button" id="tabTrustPayments" onclick="switchTab('trust')" class="px-4 py-2 text-sm font-semibold border-b-2 border-primary text-primary">Trust Service Payments</button>
    <button type="button" id="tabCryptoDeposits" onclick="switchTab('deposits')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-primary">Crypto Deposits</button>
    <button type="button" id="tabLiquidationFees" onclick="switchTab('liquidation_fees')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-primary">Liquidation Fees</button>
    <button type="button" id="tabAssetFundings" onclick="switchTab('asset_fundings')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-primary">Asset Deposits</button>
    <button type="button" id="tabCryptoLiquidations" onclick="switchTab('liquidations')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-primary">Crypto Liquidations</button>
</div>

<div id="messageContainer" class="mb-3 sm:mb-4"></div>

<div id="trustPaymentsPanel" class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="paymentsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading pending payments...</div>
    </div>
</div>

<div id="cryptoDepositsPanel" class="hidden bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="depositsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading crypto deposits...</div>
    </div>
</div>

<div id="cryptoLiquidationsPanel" class="hidden bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="liquidationsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading crypto liquidations...</div>
    </div>
</div>

<div id="liquidationFeesPanel" class="hidden bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="liquidationFeesContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading liquidation fee payments...</div>
    </div>
</div>

<div id="assetFundingsPanel" class="hidden bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="assetFundingsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading asset deposit payments...</div>
    </div>
</div>

<script src="includes/modal.js"></script>
<script>
let allPayments = [];
let allDeposits = [];
let allLiquidations = [];
let allLiquidationFees = [];
let allAssetFundings = [];
let activeTab = 'trust';

function switchTab(tab) {
    activeTab = tab;
    const tabClass = (name) => tab === name
        ? 'px-4 py-2 text-sm font-semibold border-b-2 border-primary text-primary'
        : 'px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-primary';
    document.getElementById('tabTrustPayments').className = tabClass('trust');
    document.getElementById('tabCryptoDeposits').className = tabClass('deposits');
    document.getElementById('tabLiquidationFees').className = tabClass('liquidation_fees');
    document.getElementById('tabAssetFundings').className = tabClass('asset_fundings');
    document.getElementById('tabCryptoLiquidations').className = tabClass('liquidations');
    document.getElementById('trustPaymentsPanel').classList.toggle('hidden', tab !== 'trust');
    document.getElementById('cryptoDepositsPanel').classList.toggle('hidden', tab !== 'deposits');
    document.getElementById('liquidationFeesPanel').classList.toggle('hidden', tab !== 'liquidation_fees');
    document.getElementById('assetFundingsPanel').classList.toggle('hidden', tab !== 'asset_fundings');
    document.getElementById('cryptoLiquidationsPanel').classList.toggle('hidden', tab !== 'liquidations');
}

async function loadPayments() {
    try {
        const response = await fetch('../../api/admin/trust-payments.php');
        const data = await response.json();
        
        if (data.success) {
            allPayments = data.payments || [];
            allDeposits = data.deposits || [];
            allLiquidations = data.liquidations || [];
            allLiquidationFees = data.liquidation_fees || [];
            allAssetFundings = data.asset_fundings || [];
            renderPayments(allPayments);
            renderDeposits(allDeposits);
            renderLiquidationFees(allLiquidationFees);
            renderAssetFundings(allAssetFundings);
            renderLiquidations(allLiquidations);
        } else {
            document.getElementById('paymentsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load payments</div>';
            document.getElementById('depositsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load deposits</div>';
            document.getElementById('liquidationsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load liquidations</div>';
            document.getElementById('liquidationFeesContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load liquidation fees</div>';
            document.getElementById('assetFundingsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load asset deposits</div>';
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        document.getElementById('paymentsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading payments</div>';
        document.getElementById('depositsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading deposits</div>';
        document.getElementById('liquidationsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading liquidations</div>';
        document.getElementById('liquidationFeesContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading liquidation fees</div>';
        document.getElementById('assetFundingsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading asset deposits</div>';
    }
}

function renderDeposits(deposits) {
    const container = document.getElementById('depositsContainer');
    if (!deposits || deposits.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pending crypto deposits</div>';
        return;
    }

    const txData = (d) => d.transaction_data || {};
    const html = `
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">User</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Coin</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">TX Hash</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Trust</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${deposits.map(d => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 py-3 text-sm font-mono">#${d.id}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">${escapeHtml(d.user_name || 'N/A')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(d.user_email || '')}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(d.coin_name || d.coin_key)} <span class="text-xs text-slate-500">${escapeHtml(d.coin_symbol || '')}</span></td>
                            <td class="px-4 py-3 text-sm font-semibold">${parseFloat(d.amount).toFixed(8)}</td>
                            <td class="px-4 py-3 text-xs font-mono break-all max-w-[140px]">${escapeHtml(txData(d).tx_hash || '—')}</td>
                            <td class="px-4 py-3 text-sm">${d.trust_id ? '#' + d.trust_id : '—'}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">${new Date(d.created_at).toLocaleString()}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="viewDepositDetails(${d.id})" class="text-blue-600 hover:underline text-xs">View</button>
                                    <button onclick="approveDeposit(${d.id})" class="text-green-600 hover:underline text-xs font-semibold">Approve</button>
                                    <button onclick="rejectDeposit(${d.id})" class="text-red-600 hover:underline text-xs">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-4">
            ${deposits.map(d => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex justify-between mb-2">
                        <span class="font-bold text-sm">#${d.id} · ${escapeHtml(d.coin_symbol || d.coin_key)}</span>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Pending</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-1">${escapeHtml(d.user_name || '')}</p>
                    <p class="text-sm font-semibold mb-1">${parseFloat(d.amount).toFixed(8)} ${escapeHtml(d.coin_symbol || '')}</p>
                    <p class="text-xs font-mono break-all text-slate-600 mb-3">${escapeHtml(txData(d).tx_hash || '')}</p>
                    <div class="flex gap-2">
                        <button onclick="viewDepositDetails(${d.id})" class="flex-1 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg">View</button>
                        <button onclick="approveDeposit(${d.id})" class="flex-1 py-2 text-xs bg-green-100 text-green-700 rounded-lg font-semibold">Approve</button>
                        <button onclick="rejectDeposit(${d.id})" class="flex-1 py-2 text-xs bg-red-100 text-red-700 rounded-lg">Reject</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function viewDepositDetails(depositId) {
    const d = allDeposits.find(p => p.id == depositId);
    if (!d) { showToast('Deposit not found', 'error'); return; }
    const td = d.transaction_data || {};
    const proofLink = td.proof_path
        ? `<a href="../../${escapeHtml(td.proof_path)}" target="_blank" class="text-primary hover:underline">View payment proof</a>`
        : '<span class="text-slate-500">No proof uploaded</span>';

    const detailsHtml = `
        <div class="space-y-3 text-sm">
            <div><span class="text-slate-500">Deposit ID:</span> <span class="font-mono">#${d.id}</span></div>
            <div><span class="text-slate-500">User:</span> ${escapeHtml(d.user_name || 'N/A')} (${escapeHtml(d.user_email || '')})</div>
            <div><span class="text-slate-500">Coin:</span> ${escapeHtml(d.coin_name || d.coin_key)} (${escapeHtml(d.coin_symbol || '')})</div>
            <div><span class="text-slate-500">Amount:</span> <span class="font-semibold">${parseFloat(d.amount).toFixed(8)}</span></div>
            <div><span class="text-slate-500">TX Hash:</span> <span class="font-mono text-xs break-all">${escapeHtml(td.tx_hash || '—')}</span></div>
            <div><span class="text-slate-500">Deposit Address:</span> <span class="font-mono text-xs break-all">${escapeHtml(td.deposit_address || '—')}</span></div>
            <div><span class="text-slate-500">Trust:</span> ${d.trust_id ? '#' + d.trust_id + (d.trust_service_name ? ' — ' + escapeHtml(d.trust_service_name) : '') : '—'}</div>
            <div><span class="text-slate-500">Submitted:</span> ${new Date(d.created_at).toLocaleString()}</div>
            <div><span class="text-slate-500">Proof:</span> ${proofLink}</div>
        </div>
    `;
    showModal('Crypto Deposit Details', detailsHtml, [
        { label: 'Close', onclick: 'closeModal()', class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white' }
    ]);
}

async function processDeposit(depositId, action) {
    const d = allDeposits.find(p => p.id == depositId);
    if (!d) { showToast('Deposit not found', 'error'); return; }

    const label = action === 'approve' ? 'Approve Deposit' : 'Reject Deposit';
    const msg = action === 'approve'
        ? `Approve this deposit?\n\nUser: ${d.user_name}\nAmount: ${parseFloat(d.amount).toFixed(8)} ${d.coin_symbol || ''}\n\nThis will credit the user's balance.`
        : `Reject this deposit?\n\nUser: ${d.user_name}\nAmount: ${parseFloat(d.amount).toFixed(8)} ${d.coin_symbol || ''}`;

    showConfirmModal(label, msg, async function() {
        try {
            const csrfResponse = await fetch('../../api/admin/session.php');
            const csrfData = await csrfResponse.json();
            const response = await fetch('../../api/admin/trust-payments.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    deposit_id: depositId,
                    action: action,
                    csrf_token: csrfData.csrf_token
                })
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message || (action === 'approve' ? 'Deposit approved' : 'Deposit rejected'), 'success');
                loadPayments();
            } else {
                showToast(data.message || 'Failed to process deposit', 'error');
            }
        } catch (error) {
            console.error('Error processing deposit:', error);
            showToast('Error processing deposit', 'error');
        }
    });
}

function approveDeposit(id) { processDeposit(id, 'approve'); }
function rejectDeposit(id) { processDeposit(id, 'reject'); }

function renderLiquidationFees(fees) {
    const container = document.getElementById('liquidationFeesContainer');
    if (!fees || fees.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pending liquidation fee payments</div>';
        return;
    }

    const txData = (f) => f.transaction_data || {};
    const feeLabel = (f) => {
        const purpose = txData(f).purpose || '';
        if (purpose === 'trust_liquidation') {
            return txData(f).trust_name || 'Trust Liquidation';
        }
        return f.coin_name || f.coin_key || '—';
    };
    const html = `
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">User</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Asset</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Fee (USD)</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Payment Method</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Trust</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${fees.map(f => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 py-3 text-sm font-mono">#${f.id}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">${escapeHtml(f.user_name || 'N/A')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(f.user_email || '')}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(feeLabel(f))} <span class="text-xs text-slate-500">${escapeHtml(f.coin_symbol || (txData(f).purpose === 'trust_liquidation' ? 'Trust' : ''))}</span></td>
                            <td class="px-4 py-3 text-sm font-semibold">$${parseFloat(f.amount).toFixed(2)}</td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(txData(f).payment_method_name || 'N/A')}</td>
                            <td class="px-4 py-3 text-sm">${f.trust_id ? '#' + f.trust_id : '—'}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">${new Date(f.created_at).toLocaleString()}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="approveLiquidationFee(${f.id})" class="text-green-600 hover:underline text-xs font-semibold">Approve</button>
                                    <button onclick="rejectLiquidationFee(${f.id})" class="text-red-600 hover:underline text-xs">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-4">
            ${fees.map(f => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex justify-between mb-2">
                        <span class="font-bold text-sm">#${f.id} · ${escapeHtml(feeLabel(f))}</span>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Pending</span>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">User</span><span>${escapeHtml(f.user_name || 'N/A')}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Fee</span><span class="font-semibold">$${parseFloat(f.amount).toFixed(2)}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Method</span><span>${escapeHtml(txData(f).payment_method_name || 'N/A')}</span></div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button onclick="approveLiquidationFee(${f.id})" class="flex-1 px-3 py-2 text-xs font-semibold bg-green-100 text-green-700 rounded-lg">Approve</button>
                        <button onclick="rejectLiquidationFee(${f.id})" class="flex-1 px-3 py-2 text-xs font-medium bg-red-100 text-red-700 rounded-lg">Reject</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

async function processLiquidationFee(feeId, action) {
    const fee = allLiquidationFees.find(f => f.id == feeId);
    if (!fee) {
        showToast('Liquidation fee payment not found', 'error');
        return;
    }

    const message = action === 'approve'
        ? `Approve liquidation fee payment?\n\nUser: ${fee.user_name}\nAsset: ${fee.coin_name || fee.coin_key}\nAmount: $${parseFloat(fee.amount).toFixed(2)}`
        : `Reject liquidation fee payment?\n\nUser: ${fee.user_name}\nAmount: $${parseFloat(fee.amount).toFixed(2)}`;

    showConfirmModal(action === 'approve' ? 'Approve Fee Payment' : 'Reject Fee Payment', message, async function() {
        try {
            const csrfResponse = await fetch('../../api/admin/session.php');
            const csrfData = await csrfResponse.json();
            const csrfToken = csrfData.csrf_token;

            const response = await fetch('../../api/admin/trust-payments.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    liquidation_fee_id: feeId,
                    action,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();
            if (data.success) {
                showToast(data.message || 'Liquidation fee payment processed', 'success');
                loadPayments();
            } else {
                showToast(data.message || 'Failed to process liquidation fee payment', 'error');
            }
        } catch (error) {
            console.error('Error processing liquidation fee payment:', error);
            showToast('Error processing liquidation fee payment', 'error');
        }
    });
}

function approveLiquidationFee(id) { processLiquidationFee(id, 'approve'); }
function rejectLiquidationFee(id) { processLiquidationFee(id, 'reject'); }

function renderAssetFundings(fundings) {
    const container = document.getElementById('assetFundingsContainer');
    if (!fundings || fundings.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pending asset deposit payments</div>';
        return;
    }

    const txData = (f) => f.transaction_data || {};
    const purposeLabel = (f) => {
        const purpose = txData(f).purpose || '';
        if (purpose === 'trust_declared_value') return 'Declared Trust Value';
        if (purpose === 'catalog_asset') return 'Catalog Asset';
        return 'Asset Deposit';
    };
    const itemLabel = (f) => {
        const td = txData(f);
        if (td.purpose === 'trust_declared_value') return td.trust_name || 'Trust Value';
        return td.asset_label || td.category_key || 'Asset';
    };

    const html = `
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">User</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Type</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Item</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Amount (USD)</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Trust</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${fundings.map(f => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 py-3 text-sm font-mono">#${f.id}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">${escapeHtml(f.user_name || 'N/A')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(f.user_email || '')}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(purposeLabel(f))}</td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(itemLabel(f))}</td>
                            <td class="px-4 py-3 text-sm font-semibold">$${parseFloat(f.amount).toFixed(2)}</td>
                            <td class="px-4 py-3 text-sm">${f.trust_id ? '#' + f.trust_id : '—'}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">${new Date(f.created_at).toLocaleString()}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="approveAssetFunding(${f.id})" class="text-green-600 hover:underline text-xs font-semibold">Approve</button>
                                    <button onclick="rejectAssetFunding(${f.id})" class="text-red-600 hover:underline text-xs">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    container.innerHTML = html;
}

async function processAssetFunding(fundingId, action) {
    const funding = allAssetFundings.find(f => f.id == fundingId);
    if (!funding) {
        showToast('Asset deposit payment not found', 'error');
        return;
    }

    const message = action === 'approve'
        ? `Approve this asset deposit?\n\nUser: ${funding.user_name}\nAmount: $${parseFloat(funding.amount).toFixed(2)}`
        : `Reject this asset deposit?\n\nUser: ${funding.user_name}\nAmount: $${parseFloat(funding.amount).toFixed(2)}`;

    showConfirmModal(action === 'approve' ? 'Approve Asset Deposit' : 'Reject Asset Deposit', message, async function() {
        try {
            const csrfResponse = await fetch('../../api/admin/session.php');
            const csrfData = await csrfResponse.json();
            const csrfToken = csrfData.csrf_token;

            const response = await fetch('../../api/admin/trust-payments.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    asset_funding_id: fundingId,
                    action,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();
            if (data.success) {
                showToast(data.message || 'Asset deposit processed', 'success');
                loadPayments();
            } else {
                showToast(data.message || 'Failed to process asset deposit', 'error');
            }
        } catch (error) {
            console.error('Error processing asset deposit:', error);
            showToast('Error processing asset deposit', 'error');
        }
    });
}

function approveAssetFunding(id) { processAssetFunding(id, 'approve'); }
function rejectAssetFunding(id) { processAssetFunding(id, 'reject'); }

function renderLiquidations(liquidations) {
    const container = document.getElementById('liquidationsContainer');
    if (!liquidations || liquidations.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pending crypto liquidations</div>';
        return;
    }

    const txData = (l) => l.transaction_data || {};
    const html = `
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">User</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Coin</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Fee</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Destination</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Trust</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${liquidations.map(l => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 py-3 text-sm font-mono">#${l.id}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">${escapeHtml(l.user_name || 'N/A')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(l.user_email || '')}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">${escapeHtml(l.coin_name || l.coin_key)} <span class="text-xs text-slate-500">${escapeHtml(l.coin_symbol || '')}</span></td>
                            <td class="px-4 py-3 text-sm font-semibold">${parseFloat(l.amount).toFixed(8)}</td>
                            <td class="px-4 py-3 text-sm">${parseFloat(txData(l).fee || 0).toFixed(8)}</td>
                            <td class="px-4 py-3 text-xs font-mono break-all max-w-[140px]">${escapeHtml(txData(l).destination_address || txData(l).recipient || l.recipient || '—')}</td>
                            <td class="px-4 py-3 text-sm">${l.trust_id ? '#' + l.trust_id : '—'}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">${new Date(l.created_at).toLocaleString()}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="viewLiquidationDetails(${l.id})" class="text-blue-600 hover:underline text-xs">View</button>
                                    <button onclick="approveLiquidation(${l.id})" class="text-green-600 hover:underline text-xs font-semibold">Approve</button>
                                    <button onclick="rejectLiquidation(${l.id})" class="text-red-600 hover:underline text-xs">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-4">
            ${liquidations.map(l => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex justify-between mb-2">
                        <span class="font-bold text-sm">#${l.id} · ${escapeHtml(l.coin_symbol || l.coin_key)}</span>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Pending</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-1">${escapeHtml(l.user_name || '')}</p>
                    <p class="text-sm font-semibold mb-1">${parseFloat(l.amount).toFixed(8)} ${escapeHtml(l.coin_symbol || '')}</p>
                    <p class="text-xs font-mono break-all text-slate-600 mb-3">${escapeHtml((l.transaction_data || {}).destination_address || (l.transaction_data || {}).recipient || l.recipient || '')}</p>
                    <div class="flex gap-2">
                        <button onclick="viewLiquidationDetails(${l.id})" class="flex-1 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg">View</button>
                        <button onclick="approveLiquidation(${l.id})" class="flex-1 py-2 text-xs bg-green-100 text-green-700 rounded-lg font-semibold">Approve</button>
                        <button onclick="rejectLiquidation(${l.id})" class="flex-1 py-2 text-xs bg-red-100 text-red-700 rounded-lg">Reject</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function viewLiquidationDetails(liquidationId) {
    const l = allLiquidations.find(p => p.id == liquidationId);
    if (!l) { showToast('Liquidation not found', 'error'); return; }
    const td = l.transaction_data || {};
    const detailsHtml = `
        <div class="space-y-3 text-sm">
            <div><span class="text-slate-500">Liquidation ID:</span> <span class="font-mono">#${l.id}</span></div>
            <div><span class="text-slate-500">User:</span> ${escapeHtml(l.user_name || 'N/A')} (${escapeHtml(l.user_email || '')})</div>
            <div><span class="text-slate-500">Coin:</span> ${escapeHtml(l.coin_name || l.coin_key)} (${escapeHtml(l.coin_symbol || '')})</div>
            <div><span class="text-slate-500">Amount:</span> <span class="font-semibold">${parseFloat(l.amount).toFixed(8)}</span></div>
            <div><span class="text-slate-500">Network Fee:</span> ${parseFloat(td.network_fee ?? td.fee ?? l.fee ?? 0).toFixed(8)}</div>
            ${td.platform_fee_usd ? `<div><span class="text-slate-500">Platform Fee:</span> $${parseFloat(td.platform_fee_usd).toFixed(2)} (${parseFloat(td.platform_fee_coin || 0).toFixed(8)} ${escapeHtml(l.coin_symbol || '')})</div>` : ''}
            <div><span class="text-slate-500">Total Fee:</span> ${parseFloat(l.fee || 0).toFixed(8)}</div>
            <div><span class="text-slate-500">Total Debit:</span> <span class="font-semibold">${(parseFloat(l.amount) + parseFloat(td.fee || 0)).toFixed(8)}</span></div>
            <div><span class="text-slate-500">Destination:</span> <span class="font-mono text-xs break-all">${escapeHtml(td.destination_address || td.recipient || l.recipient || '—')}</span></div>
            <div><span class="text-slate-500">Trust:</span> ${l.trust_id ? '#' + l.trust_id + (l.trust_service_name ? ' — ' + escapeHtml(l.trust_service_name) : '') : '—'}</div>
            <div><span class="text-slate-500">Submitted:</span> ${new Date(l.created_at).toLocaleString()}</div>
        </div>
    `;
    showModal('Crypto Liquidation Details', detailsHtml, [
        { label: 'Close', onclick: 'closeModal()', class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white' }
    ]);
}

async function processLiquidation(liquidationId, action) {
    const l = allLiquidations.find(p => p.id == liquidationId);
    if (!l) { showToast('Liquidation not found', 'error'); return; }
    const td = l.transaction_data || {};
    const total = parseFloat(l.amount) + parseFloat(td.fee || 0);
    const label = action === 'approve' ? 'Approve Liquidation' : 'Reject Liquidation';
    const msg = action === 'approve'
        ? `Approve this liquidation?\n\nUser: ${l.user_name}\nAmount: ${parseFloat(l.amount).toFixed(8)} ${l.coin_symbol || ''}\nFee: ${parseFloat(td.fee || 0).toFixed(8)}\nTotal debit: ${total.toFixed(8)}\n\nThis will debit the user's balance.`
        : `Reject this liquidation?\n\nUser: ${l.user_name}\nAmount: ${parseFloat(l.amount).toFixed(8)} ${l.coin_symbol || ''}`;

    showConfirmModal(label, msg, async function() {
        try {
            const csrfResponse = await fetch('../../api/admin/session.php');
            const csrfData = await csrfResponse.json();
            const response = await fetch('../../api/admin/trust-payments.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    liquidation_id: liquidationId,
                    action: action,
                    csrf_token: csrfData.csrf_token
                })
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message || (action === 'approve' ? 'Liquidation approved' : 'Liquidation rejected'), 'success');
                loadPayments();
            } else {
                showToast(data.message || 'Failed to process liquidation', 'error');
            }
        } catch (error) {
            console.error('Error processing liquidation:', error);
            showToast('Error processing liquidation', 'error');
        }
    });
}

function approveLiquidation(id) { processLiquidation(id, 'approve'); }
function rejectLiquidation(id) { processLiquidation(id, 'reject'); }

function renderPayments(payments) {
    const container = document.getElementById('paymentsContainer');
    if (!payments || payments.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pending payments</div>';
        return;
    }
    
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Trust ID</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">User</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Service</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Amount</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Payment Method</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Created</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${payments.map(payment => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-mono">#${payment.id}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-navy-900 dark:text-white">${escapeHtml(payment.user_name || 'N/A')}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(payment.user_email || '')}</div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${escapeHtml(payment.service_name || 'N/A')}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-semibold">$${parseFloat(payment.price || 0).toFixed(2)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${escapeHtml(payment.payment_method_name || 'N/A')}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500">${new Date(payment.created_at).toLocaleDateString()}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="viewDetails(${payment.id})" class="text-blue-600 dark:text-blue-400 hover:underline text-xs sm:text-sm">View</button>
                                    <button onclick="approvePayment(${payment.id})" class="text-green-600 dark:text-green-400 hover:underline text-xs sm:text-sm font-semibold">Approve</button>
                                    <button onclick="rejectPayment(${payment.id})" class="text-red-600 dark:text-red-400 hover:underline text-xs sm:text-sm">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${payments.map(payment => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white">Trust #${payment.id}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1">${escapeHtml(payment.user_name || 'N/A')} (${escapeHtml(payment.user_email || '')})</p>
                        </div>
                        <span class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">Pending</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Service:</span>
                            <span class="font-medium text-navy-900 dark:text-white">${escapeHtml(payment.service_name || 'N/A')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Amount:</span>
                            <span class="font-semibold text-navy-900 dark:text-white">$${parseFloat(payment.price || 0).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Payment:</span>
                            <span class="text-navy-900 dark:text-white">${escapeHtml(payment.payment_method_name || 'N/A')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Created:</span>
                            <span class="text-navy-900 dark:text-white">${new Date(payment.created_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button onclick="viewDetails(${payment.id})" class="flex-1 px-3 py-2 text-xs font-medium bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg hover:opacity-90">View</button>
                        <button onclick="approvePayment(${payment.id})" class="flex-1 px-3 py-2 text-xs font-semibold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-lg hover:opacity-90">Approve</button>
                        <button onclick="rejectPayment(${payment.id})" class="flex-1 px-3 py-2 text-xs font-medium bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg hover:opacity-90">Reject</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
}

function viewDetails(trustId) {
    const payment = allPayments.find(p => p.id == trustId);
    if (!payment) {
        showToast('Payment not found', 'error');
        return;
    }
    
    const trustData = payment.trust_data || {};
    const personalInfo = trustData.personal_info || {};
    const businessInfo = trustData.business_info || {};
    const beneficiaries = trustData.beneficiaries || [];
    const paymentInfo = trustData.payment_info || {};

    const personFirst = personalInfo.first_name || '';
    const personLast = personalInfo.last_name || '';
    const personName = [personFirst, personLast].filter(Boolean).join(' ').trim() || personalInfo.full_name || '';
    const hasPersonalBlock = !!(personName || personalInfo.email || personalInfo.phone || personalInfo.street);
    const hasBusinessBlock = !!(
        trustData.trust_name ||
        businessInfo.company_name ||
        businessInfo.formation_state ||
        businessInfo.business_ending ||
        trustData.total_estimated_value != null
    );

    const endingLabels = {
        none: 'Prefer no ending',
        llc: 'LLC',
        limited_liability_company: 'Limited Liability Company',
        corp: 'Corp',
        corporation: 'Corporation',
        inc: 'Inc',
        incorporated: 'Incorporated'
    };
    const endingLabel = endingLabels[businessInfo.business_ending] || businessInfo.business_ending || '';
    const companyDisplay = businessInfo.company_name
        ? (businessInfo.business_ending && businessInfo.business_ending !== 'none' && endingLabel
            ? `${businessInfo.company_name} ${endingLabel}`
            : businessInfo.company_name)
        : '';
    
    const detailsHtml = `
        <div class="space-y-4">
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Trust Information</h4>
                <div class="space-y-1 text-sm">
                    <div><span class="text-slate-500 dark:text-slate-400">Trust ID:</span> <span class="font-mono">#${payment.id}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Service:</span> ${escapeHtml(payment.service_name || 'N/A')}</div>
                    <div><span class="text-slate-500 dark:text-slate-400">Amount:</span> <span class="font-semibold">$${parseFloat(payment.price || 0).toFixed(2)}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Payment Method:</span> ${escapeHtml(payment.payment_method_name || 'N/A')}</div>
                    <div><span class="text-slate-500 dark:text-slate-400">Status:</span> <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">Pending</span></div>
                </div>
            </div>
            
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">User Information</h4>
                <div class="space-y-1 text-sm">
                    <div><span class="text-slate-500 dark:text-slate-400">Name:</span> ${escapeHtml(payment.user_name || 'N/A')}</div>
                    <div><span class="text-slate-500 dark:text-slate-400">Email:</span> ${escapeHtml(payment.user_email || 'N/A')}</div>
                </div>
            </div>

            ${hasBusinessBlock ? `
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Business Information</h4>
                <div class="space-y-1 text-sm">
                    ${trustData.trust_name ? `<div><span class="text-slate-500 dark:text-slate-400">Trust Name:</span> ${escapeHtml(trustData.trust_name)}</div>` : ''}
                    ${businessInfo.company_name ? `<div><span class="text-slate-500 dark:text-slate-400">Company Name:</span> ${escapeHtml(businessInfo.company_name)}</div>` : ''}
                    ${endingLabel ? `<div><span class="text-slate-500 dark:text-slate-400">Business Ending:</span> ${escapeHtml(endingLabel)}</div>` : ''}
                    ${companyDisplay ? `<div><span class="text-slate-500 dark:text-slate-400">Display Name:</span> ${escapeHtml(companyDisplay)}</div>` : ''}
                    ${businessInfo.formation_state ? `<div><span class="text-slate-500 dark:text-slate-400">Formation State / Jurisdiction:</span> ${escapeHtml(businessInfo.formation_state)}</div>` : ''}
                    ${trustData.total_estimated_value != null && trustData.total_estimated_value !== '' ? `<div><span class="text-slate-500 dark:text-slate-400">Total Asset Value:</span> $${Number(trustData.total_estimated_value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>` : ''}
                </div>
            </div>
            ` : ''}
            
            ${hasPersonalBlock ? `
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Personal Information</h4>
                <div class="space-y-1 text-sm">
                    ${personFirst || personLast ? `
                        <div><span class="text-slate-500 dark:text-slate-400">First Name:</span> ${escapeHtml(personFirst || 'N/A')}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Last Name:</span> ${escapeHtml(personLast || 'N/A')}</div>
                    ` : `
                        <div><span class="text-slate-500 dark:text-slate-400">Name:</span> ${escapeHtml(personName || 'N/A')}</div>
                    `}
                    ${personalInfo.email ? `<div><span class="text-slate-500 dark:text-slate-400">Email:</span> ${escapeHtml(personalInfo.email)}</div>` : ''}
                    ${personalInfo.phone ? `<div><span class="text-slate-500 dark:text-slate-400">Phone:</span> ${escapeHtml(personalInfo.phone)}</div>` : ''}
                    ${personalInfo.street ? `<div><span class="text-slate-500 dark:text-slate-400">Address:</span> ${escapeHtml([personalInfo.street, personalInfo.city, personalInfo.state, personalInfo.zip].filter(Boolean).join(', '))}</div>` : ''}
                </div>
            </div>
            ` : ''}
            
            ${beneficiaries.length > 0 ? `
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Beneficiaries (${beneficiaries.length})</h4>
                <div class="space-y-2 text-sm">
                    ${beneficiaries.map((ben, idx) => `
                        <div class="p-2 bg-slate-50 dark:bg-navy-700/50 rounded">
                            <div class="font-medium">${escapeHtml(ben.name || 'N/A')}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(ben.relationship || 'N/A')} - ${parseFloat(ben.allocation || 0).toFixed(1)}%</div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
            
            ${paymentInfo.amount ? `
            <div>
                <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Payment Details</h4>
                <div class="space-y-1 text-sm">
                    <div><span class="text-slate-500 dark:text-slate-400">Amount:</span> $${parseFloat(paymentInfo.amount || 0).toFixed(2)}</div>
                    <div><span class="text-slate-500 dark:text-slate-400">Confirmed:</span> ${paymentInfo.user_confirmed ? 'Yes' : 'No'}</div>
                    ${paymentInfo.confirmed_at ? `<div><span class="text-slate-500 dark:text-slate-400">Confirmed At:</span> ${new Date(paymentInfo.confirmed_at).toLocaleString()}</div>` : ''}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    showModal('Trust Payment Details', detailsHtml, [
        { label: 'Close', onclick: 'closeModal()', class: 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white' }
    ]);
}

function approvePayment(trustId) {
    const payment = allPayments.find(p => p.id == trustId);
    if (!payment) {
        showToast('Payment not found', 'error');
        return;
    }
    
    showConfirmModal(
        'Approve Payment',
        `Are you sure you want to approve this payment?\n\nTrust ID: #${payment.id}\nUser: ${escapeHtml(payment.user_name || 'N/A')}\nAmount: $${parseFloat(payment.price || 0).toFixed(2)}\n\nThis will activate the trust.`,
        async function() {
            try {
                // Get CSRF token
                const csrfResponse = await fetch('../../api/admin/session.php');
                const csrfData = await csrfResponse.json();
                const csrfToken = csrfData.csrf_token;
                
                const response = await fetch('../../api/admin/trust-payments.php', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        trust_id: trustId,
                        action: 'approve',
                        csrf_token: csrfToken
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Payment approved successfully. Trust is now active.', 'success');
                    loadPayments();
                } else {
                    showToast(data.message || 'Failed to approve payment', 'error');
                }
            } catch (error) {
                console.error('Error approving payment:', error);
                showToast('Error approving payment', 'error');
            }
        }
    );
}

function rejectPayment(trustId) {
    const payment = allPayments.find(p => p.id == trustId);
    if (!payment) {
        showToast('Payment not found', 'error');
        return;
    }
    
    showConfirmModal(
        'Reject Payment',
        `Are you sure you want to reject this payment?\n\nTrust ID: #${payment.id}\nUser: ${escapeHtml(payment.user_name || 'N/A')}\nAmount: $${parseFloat(payment.price || 0).toFixed(2)}\n\nThe trust will remain pending.`,
        async function() {
            try {
                // Get CSRF token
                const csrfResponse = await fetch('../../api/admin/session.php');
                const csrfData = await csrfResponse.json();
                const csrfToken = csrfData.csrf_token;
                
                const response = await fetch('../../api/admin/trust-payments.php', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        trust_id: trustId,
                        action: 'reject',
                        csrf_token: csrfToken
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Payment rejected. Trust is now inactive.', 'success');
                    loadPayments();
                } else {
                    showToast(data.message || 'Failed to reject payment', 'error');
                }
            } catch (error) {
                console.error('Error rejecting payment:', error);
                showToast('Error rejecting payment', 'error');
            }
        }
    );
}

function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load payments on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadPayments);
} else {
    loadPayments();
}
</script>

<?php
}

// Render the layout with payments content
renderAdminLayout($page_title, 'trust-payments', 'renderTrustPaymentsContent');
?>
