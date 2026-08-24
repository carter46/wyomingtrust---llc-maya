<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'User Assets Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderUserAssetsContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white mb-4">User Assets Management</h1>
    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base">Manage user cryptocurrency balances (Credit/Debit)</p>
</div>

<div id="messageContainer" class="mb-3 sm:mb-4"></div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- User Selection -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <h2 class="text-lg font-bold text-navy-900 dark:text-white mb-4">Select User</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">User</label>
                <select id="selectedUser" onchange="onUserChanged()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                    <option value="">-- Select User --</option>
                </select>
            </div>
            <div id="trustSelectWrap" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Trust (entrusted coins)</label>
                <select id="selectedTrust" onchange="loadUserAssets()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                    <option value="">-- All selected coins --</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">Only coins the user selected during onboarding are shown. Pick a trust to filter further.</p>
            </div>
        </div>
    </div>
    
    <!-- Balance Adjustment Form -->
    <div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
        <h2 class="text-lg font-bold text-navy-900 dark:text-white mb-4">Adjust Balance</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Coin</label>
                <select id="selectedCoin" onchange="updateCoinAmountPreview()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                    <option value="">-- Select User First --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Transaction Type</label>
                <select id="transactionType" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                    <option value="credit">Credit (Add Balance)</option>
                    <option value="debit">Debit (Subtract Balance)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Amount (USD)</label>
                <input type="number" id="amountUsd" step="0.01" min="0" placeholder="0.00" oninput="updateCoinAmountPreview()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                <p id="coinAmountPreview" class="text-xs text-slate-500 mt-2">Select a coin and enter USD to see the coin amount.</p>
                <input type="hidden" id="amount" value="">
            </div>
            <button onclick="processBalanceAdjustment()" class="w-full bg-primary text-navy-900 px-4 py-2.5 rounded-lg font-semibold hover:opacity-90">Process Transaction</button>
        </div>
    </div>
</div>

<!-- User Assets Display -->
<div id="userAssetsSection" class="mt-6 bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 hidden">
    <h2 class="text-lg font-bold text-navy-900 dark:text-white mb-4">User Assets</h2>
    <div id="userAssetsContainer">
        <div class="text-center py-8 text-slate-500">Select a user to view their assets</div>
    </div>
</div>

<script>
let allUsers = [];
let userAssets = [];
let userTrusts = [];
let coinPriceCache = {};

function onUserChanged() {
    document.getElementById('selectedTrust').value = '';
    loadUserAssets();
}

function getSelectedAsset() {
    const coinId = document.getElementById('selectedCoin').value;
    if (!coinId) return null;
    return userAssets.find(a => String(a.coin_id) === String(coinId)) || null;
}

async function fetchCoinUsdPrice(coinKey) {
    if (!coinKey) return 0;
    if (coinPriceCache[coinKey] != null) return coinPriceCache[coinKey];
    try {
        const response = await fetch(
            `../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(coinKey)}&vs_currencies=usd`,
            { credentials: 'same-origin' }
        );
        if (!response.ok) return 0;
        const data = await response.json();
        const price = parseFloat(data?.[coinKey]?.usd || 0) || 0;
        if (price > 0) coinPriceCache[coinKey] = price;
        return price;
    } catch (e) {
        console.error('Price fetch failed', e);
        return 0;
    }
}

async function updateCoinAmountPreview() {
    const preview = document.getElementById('coinAmountPreview');
    const amountHidden = document.getElementById('amount');
    const usd = parseFloat(document.getElementById('amountUsd').value) || 0;
    const asset = getSelectedAsset();

    amountHidden.value = '';
    if (!asset) {
        preview.textContent = 'Select a coin and enter USD to see the coin amount.';
        preview.className = 'text-xs text-slate-500 mt-2';
        return;
    }
    if (usd <= 0) {
        preview.textContent = `Enter a USD amount to convert to ${asset.symbol || asset.display_name}.`;
        preview.className = 'text-xs text-slate-500 mt-2';
        return;
    }

    preview.textContent = 'Fetching live rate…';
    preview.className = 'text-xs text-slate-500 mt-2';
    const price = await fetchCoinUsdPrice(asset.coin_key);
    if (price <= 0) {
        preview.textContent = 'Price unavailable for this coin. Try again or pick another coin.';
        preview.className = 'text-xs text-red-500 mt-2';
        return;
    }
    const coinAmount = usd / price;
    amountHidden.value = coinAmount.toFixed(8);
    preview.innerHTML = `≈ <strong>${coinAmount.toFixed(8)} ${escapeHtml(asset.symbol || '')}</strong> <span class="text-slate-400">(1 ${escapeHtml(asset.symbol || '')} = $${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 6 })})</span>`;
    preview.className = 'text-xs text-emerald-700 dark:text-emerald-400 mt-2';
}

async function loadUsers() {
    try {
        const response = await fetch('../../api/admin/users.php');
        const data = await response.json();
        
        if (data.success && data.users) {
            allUsers = data.users;
            const select = document.getElementById('selectedUser');
            select.innerHTML = '<option value="">-- Select User --</option>' + 
                data.users.map(user => `<option value="${user.id}">${escapeHtml(user.full_name)} (${escapeHtml(user.email)})</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showMessage('Error loading users', 'error');
    }
}

async function loadUserAssets() {
    const userId = document.getElementById('selectedUser').value;
    const trustId = document.getElementById('selectedTrust').value;
    if (!userId) {
        document.getElementById('userAssetsSection').classList.add('hidden');
        document.getElementById('trustSelectWrap').classList.add('hidden');
        document.getElementById('selectedCoin').innerHTML = '<option value="">-- Select User First --</option>';
        return;
    }
    
    try {
        let url = `../../api/admin/user-assets.php?user_id=${userId}`;
        if (trustId) url += `&trust_id=${trustId}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            userTrusts = data.trusts || [];
            const trustWrap = document.getElementById('trustSelectWrap');
            const trustSelect = document.getElementById('selectedTrust');
            if (userTrusts.length > 0) {
                trustWrap.classList.remove('hidden');
                const currentTrust = trustId || '';
                trustSelect.innerHTML = '<option value="">-- All selected coins --</option>' +
                    userTrusts.map(t => `<option value="${t.id}" ${String(t.id) === String(currentTrust) ? 'selected' : ''}>${escapeHtml(t.trust_name || 'Trust')} (#${t.id}) — ${(t.entrusted_coins || []).length} coins</option>`).join('');
            } else {
                trustWrap.classList.add('hidden');
            }

            userAssets = data.assets || [];
            renderUserAssets(userAssets, trustId);
            
            const coinSelect = document.getElementById('selectedCoin');
            if (userAssets.length === 0) {
                coinSelect.innerHTML = trustId
                    ? '<option value="">-- No selected coins for this trust --</option>'
                    : '<option value="">-- No selected coins for this user --</option>';
            } else {
                coinSelect.innerHTML = '<option value="">-- Select Coin --</option>' + 
                    userAssets.map(asset => `<option value="${asset.coin_id}">${escapeHtml(asset.display_name)} (${escapeHtml(asset.symbol)}) - Balance: ${parseFloat(asset.balance).toFixed(8)}</option>`).join('');
            }
            
            document.getElementById('userAssetsSection').classList.remove('hidden');
        } else {
            document.getElementById('userAssetsSection').classList.remove('hidden');
            document.getElementById('userAssetsContainer').innerHTML = `<div class="text-center py-8 text-red-500">${escapeHtml(data.message || 'Failed to load assets')}</div>`;
        }
    } catch (error) {
        console.error('Error loading user assets:', error);
        showMessage('Error loading user assets', 'error');
    }
}

function renderUserAssets(assets, trustId) {
    const container = document.getElementById('userAssetsContainer');
    const heading = document.querySelector('#userAssetsSection h2');
    if (heading) {
        heading.textContent = trustId ? 'Selected Coins (Trust Portfolio)' : 'Selected Coins (All Trusts)';
    }
    
    if (!assets || assets.length === 0) {
        container.innerHTML = trustId
            ? '<div class="text-center py-8 text-slate-500">No selected coins for this trust</div>'
            : '<div class="text-center py-8 text-slate-500">No selected coins for this user</div>';
        return;
    }
    
    const html = `
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Coin</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Balance</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${assets.map(asset => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 py-3 text-sm">
                                <span class="font-semibold">${escapeHtml(asset.display_name)}</span>
                                <span class="text-xs text-slate-500 ml-2">${escapeHtml(asset.symbol)}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">${parseFloat(asset.balance).toFixed(8)}</td>
                            <td class="px-4 py-3">
                                <button onclick="quickSelectCoin(${asset.coin_id})" class="text-primary hover:underline text-xs">Select</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

function quickSelectCoin(coinId) {
    document.getElementById('selectedCoin').value = coinId;
    updateCoinAmountPreview();
}

let csrfToken = null;

async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    try {
        const response = await fetch('../../api/admin/session.php');
        const data = await response.json();
        if (data.csrf_token) {
            csrfToken = data.csrf_token;
            return csrfToken;
        }
    } catch (error) {
        console.error('Failed to get CSRF token:', error);
    }
    return null;
}

async function processBalanceAdjustment() {
    const userId = document.getElementById('selectedUser').value;
    const coinId = document.getElementById('selectedCoin').value;
    const type = document.getElementById('transactionType').value;
    const usd = parseFloat(document.getElementById('amountUsd').value) || 0;
    const asset = getSelectedAsset();

    await updateCoinAmountPreview();
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (!userId || !coinId || !usd || usd <= 0 || !amount || amount <= 0) {
        showMessage('Please select a coin and enter a valid USD amount', 'error');
        return;
    }
    
    if (!confirm(`Are you sure you want to ${type} $${usd.toFixed(2)} USD (≈ ${amount.toFixed(8)} ${asset?.symbol || ''}) ${type === 'credit' ? 'to' : 'from'} this user's balance?`)) {
        return;
    }
    
    const token = await getCsrfToken();
    if (!token) {
        showMessage('Failed to get security token. Please refresh the page.', 'error');
        return;
    }
    
    try {
        const response = await fetch('../../api/admin/user-assets.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                user_id: parseInt(userId),
                coin_id: parseInt(coinId),
                type: type,
                amount: amount,
                amount_usd: usd,
                csrf_token: token
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`Balance ${type === 'credit' ? 'credited' : 'debited'} successfully. New balance: ${parseFloat(data.balance).toFixed(8)}`, 'success');
            document.getElementById('amountUsd').value = '';
            document.getElementById('amount').value = '';
            document.getElementById('coinAmountPreview').textContent = 'Select a coin and enter USD to see the coin amount.';
            loadUserAssets();
        } else {
            showMessage(data.message || 'Failed to adjust balance', 'error');
        }
    } catch (error) {
        console.error('Error adjusting balance:', error);
        showMessage('Error adjusting balance', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showMessage(message, type) {
    const container = document.getElementById('messageContainer');
    const bgColor = type === 'success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400';
    container.innerHTML = `<div class="${bgColor} px-4 py-3 rounded-lg text-sm">${escapeHtml(message)}</div>`;
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    await getCsrfToken(); // Pre-fetch CSRF token
    loadUsers();
});
</script>

<?php
}

// Render the layout with content
renderAdminLayout($page_title, 'user-assets', 'renderUserAssetsContent');
?>
