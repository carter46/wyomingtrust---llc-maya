<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Coins Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderCoinsContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Coins Management</h1>
    <button onclick="showCreateCoinModal()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto">Add New Coin</button>
</div>
<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
<div id="coinsContainer" class="p-4 sm:p-6">
<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading coins...</div>
</div>
</div>

<script src="includes/modal.js"></script>
<script>
let allCoins = [];

async function loadCoins() {
    try {
        const response = await fetch('../../api/admin/coins.php');
        const data = await response.json();
        
        if (data.success && data.coins) {
            allCoins = data.coins;
            renderCoins(data.coins);
        } else {
            document.getElementById('coinsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load coins</div>';
        }
    } catch (error) {
        console.error('Error loading coins:', error);
        document.getElementById('coinsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading coins</div>';
    }
}

function renderCoins(coins) {
    const container = document.getElementById('coinsContainer');
    if (!coins || coins.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No coins found</div>';
        return;
    }
    
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Coin Key</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Symbol</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Default Balance</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Default</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${coins.map(coin => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${coin.id}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-mono">${escapeHtml(coin.coin_key)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${escapeHtml(coin.display_name)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-semibold">${escapeHtml(coin.symbol)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${parseFloat(coin.default_balance).toFixed(8)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                ${coin.is_default ? '<span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">Default</span>' : '-'}
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="editCoin(${coin.id})" class="text-primary hover:underline text-xs sm:text-sm">Edit</button>
                                    ${!coin.is_default ? `<button onclick="deleteCoin(${coin.id})" class="text-red-600 hover:underline text-xs sm:text-sm">Delete</button>` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${coins.map(coin => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white">${escapeHtml(coin.display_name)}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono">${escapeHtml(coin.coin_key)}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">Symbol: ${escapeHtml(coin.symbol)}</p>
                        </div>
                        ${coin.is_default ? '<span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">Default</span>' : ''}
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button onclick="editCoin(${coin.id})" class="text-primary hover:underline text-xs">Edit</button>
                        ${!coin.is_default ? `<button onclick="deleteCoin(${coin.id})" class="text-red-600 hover:underline text-xs">Delete</button>` : ''}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
}

function showCreateCoinModal() {
    showModal('Create New Coin', `
        <form id="coinForm" onsubmit="saveCoin(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Coin Key (e.g., bitcoin, ethereum)</label>
                    <input type="text" id="coinKey" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white" placeholder="bitcoin">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Display Name</label>
                    <input type="text" id="displayName" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white" placeholder="Bitcoin">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Symbol</label>
                    <input type="text" id="symbol" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white" placeholder="BTC">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Default Balance</label>
                    <input type="number" step="0.00000001" id="defaultBalance" value="0" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Logo URL</label>
                    <input type="url" id="logo" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white" placeholder="https://assets.coingecko.com/coins/images/1/large/bitcoin.png">
                </div>
                <div class="flex gap-3 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-navy-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-navy-900 font-semibold hover:opacity-90">Save</button>
                </div>
            </div>
        </form>
    `);
}

function editCoin(coinId) {
    const coin = allCoins.find(c => c.id === coinId);
    if (!coin) return;
    
    showModal('Edit Coin', `
        <form id="coinForm" onsubmit="saveCoin(event, ${coinId})">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Coin Key</label>
                    <input type="text" id="coinKey" value="${escapeHtml(coin.coin_key)}" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Display Name</label>
                    <input type="text" id="displayName" value="${escapeHtml(coin.display_name)}" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Symbol</label>
                    <input type="text" id="symbol" value="${escapeHtml(coin.symbol)}" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Default Balance</label>
                    <input type="number" step="0.00000001" id="defaultBalance" value="${coin.default_balance}" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Logo URL</label>
                    <input type="url" id="logo" value="${escapeHtml(coin.logo || '')}" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                </div>
                <div class="flex gap-3 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-navy-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-navy-900 font-semibold hover:opacity-90">Update</button>
                </div>
            </div>
        </form>
    `);
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

async function saveCoin(event, coinId = null) {
    event.preventDefault();
    
    const token = await getCsrfToken();
    if (!token) {
        showMessage('Failed to get security token. Please refresh the page.', 'error');
        return;
    }
    
    const payload = {
        coin_key: document.getElementById('coinKey').value.trim(),
        display_name: document.getElementById('displayName').value.trim(),
        symbol: document.getElementById('symbol').value.trim().toUpperCase(),
        default_balance: parseFloat(document.getElementById('defaultBalance').value) || 0,
        logo: document.getElementById('logo').value.trim(),
        csrf_token: token
    };
    
    try {
        const url = coinId ? `../../api/admin/coins.php?id=${coinId}` : '../../api/admin/coins.php';
        const method = coinId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Coin ' + (coinId ? 'updated' : 'created') + ' successfully', 'success');
            closeModal();
            loadCoins();
        } else {
            showMessage(data.message || 'Failed to save coin', 'error');
        }
    } catch (error) {
        console.error('Error saving coin:', error);
        showMessage('Error saving coin', 'error');
    }
}

async function deleteCoin(coinId) {
    if (!confirm('Are you sure you want to delete this coin? This action cannot be undone.')) {
        return;
    }
    
    const token = await getCsrfToken();
    if (!token) {
        showMessage('Failed to get security token. Please refresh the page.', 'error');
        return;
    }
    
    try {
        const response = await fetch(`../../api/admin/coins.php?id=${coinId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            credentials: 'same-origin',
            body: JSON.stringify({ csrf_token: token })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Coin deleted successfully', 'success');
            loadCoins();
        } else {
            showMessage(data.message || 'Failed to delete coin', 'error');
        }
    } catch (error) {
        console.error('Error deleting coin:', error);
        showMessage('Error deleting coin', 'error');
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
    loadCoins();
});
</script>

<?php
}

// Render the layout with content
renderAdminLayout($page_title, 'coins', 'renderCoinsContent');
?>
