<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Wallet Addresses Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderAddressesContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Wallet Addresses Management</h1>
    <button onclick="showCreateAddressModal()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto">Add New Address</button>
</div>
<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
<div id="addressesContainer" class="p-4 sm:p-6">
<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading addresses...</div>
</div>
</div>

<script src="includes/modal.js"></script>
<script>
let allAddresses = [];
let allCoins = [];

async function loadCoins() {
    try {
        const response = await fetch('../../api/admin/coins.php');
        const data = await response.json();
        if (data.success && data.coins) {
            allCoins = data.coins;
        }
    } catch (error) {
        console.error('Error loading coins:', error);
    }
}

async function loadAddresses() {
    try {
        const response = await fetch('../../api/admin/addresses.php');
        const data = await response.json();
        
        if (data.success && data.addresses) {
            allAddresses = data.addresses;
            renderAddresses(data.addresses);
        } else {
            document.getElementById('addressesContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load addresses</div>';
        }
    } catch (error) {
        console.error('Error loading addresses:', error);
        document.getElementById('addressesContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading addresses</div>';
    }
}

function renderAddresses(addresses) {
    const container = document.getElementById('addressesContainer');
    if (!addresses || addresses.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No wallet addresses found. Add one to get started.</div>';
        return;
    }
    
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">ID</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Coin</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Address</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Created</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${addresses.map(addr => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${addr.id}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">
                                <div>
                                    <span class="font-semibold">${escapeHtml(addr.display_name || addr.coin_key)}</span>
                                    <span class="text-xs text-slate-500 ml-2">${escapeHtml(addr.symbol || '')}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-mono text-xs break-all">${escapeHtml(addr.address)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500">${new Date(addr.created_at).toLocaleDateString()}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="editAddress(${addr.id})" class="text-primary hover:underline text-xs sm:text-sm">Edit</button>
                                    <button onclick="deleteAddress(${addr.id})" class="text-red-600 hover:underline text-xs sm:text-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${addresses.map(addr => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white">${escapeHtml(addr.display_name || addr.coin_key)}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono break-all">${escapeHtml(addr.address)}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button onclick="editAddress(${addr.id})" class="text-primary hover:underline text-xs">Edit</button>
                        <button onclick="deleteAddress(${addr.id})" class="text-red-600 hover:underline text-xs">Delete</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
}

function showCreateAddressModal() {
    if (allCoins.length === 0) {
        showMessage('Please wait, loading coins...', 'error');
        return;
    }
    
    const coinsOptions = allCoins.map(coin => 
        `<option value="${coin.id}">${escapeHtml(coin.display_name)} (${escapeHtml(coin.symbol)})</option>`
    ).join('');
    
    showModal('Add New Wallet Address', `
        <form id="addressForm" onsubmit="saveAddress(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Coin</label>
                    <select id="coinId" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                        <option value="">Select a coin</option>
                        ${coinsOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Wallet Address</label>
                    <input type="text" id="address" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white font-mono text-sm" placeholder="Enter wallet address">
                </div>
                <div class="flex gap-3 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-navy-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-navy-900 font-semibold hover:opacity-90">Save</button>
                </div>
            </div>
        </form>
    `);
}

function editAddress(addressId) {
    const address = allAddresses.find(a => a.id === addressId);
    if (!address) return;
    
    if (allCoins.length === 0) {
        showMessage('Please wait, loading coins...', 'error');
        loadCoins().then(() => editAddress(addressId));
        return;
    }
    
    const coinsOptions = allCoins.map(coin => 
        `<option value="${coin.id}" ${coin.id == address.coin_id ? 'selected' : ''}>${escapeHtml(coin.display_name)} (${escapeHtml(coin.symbol)})</option>`
    ).join('');
    
    showModal('Edit Wallet Address', `
        <form id="addressForm" onsubmit="saveAddress(event, ${addressId})">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Coin</label>
                    <select id="coinId" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white">
                        ${coinsOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Wallet Address</label>
                    <input type="text" id="address" value="${escapeHtml(address.address)}" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-700 text-slate-900 dark:text-white font-mono text-sm">
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

async function saveAddress(event, addressId = null) {
    event.preventDefault();
    
    const token = await getCsrfToken();
    if (!token) {
        showMessage('Failed to get security token. Please refresh the page.', 'error');
        return;
    }
    
    const payload = {
        coin_id: parseInt(document.getElementById('coinId').value),
        address: document.getElementById('address').value.trim(),
        csrf_token: token
    };
    
    try {
        const url = addressId ? `../../api/admin/addresses.php?id=${addressId}` : '../../api/admin/addresses.php';
        const method = addressId ? 'PUT' : 'POST';
        
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
            showMessage('Address ' + (addressId ? 'updated' : 'created') + ' successfully', 'success');
            closeModal();
            loadAddresses();
        } else {
            showMessage(data.message || 'Failed to save address', 'error');
        }
    } catch (error) {
        console.error('Error saving address:', error);
        showMessage('Error saving address', 'error');
    }
}

async function deleteAddress(addressId) {
    if (!confirm('Are you sure you want to delete this wallet address? This action cannot be undone.')) {
        return;
    }
    
    const token = await getCsrfToken();
    if (!token) {
        showMessage('Failed to get security token. Please refresh the page.', 'error');
        return;
    }
    
    try {
        const response = await fetch(`../../api/admin/addresses.php?id=${addressId}`, {
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
            showMessage('Address deleted successfully', 'success');
            loadAddresses();
        } else {
            showMessage(data.message || 'Failed to delete address', 'error');
        }
    } catch (error) {
        console.error('Error deleting address:', error);
        showMessage('Error deleting address', 'error');
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
    loadAddresses();
});
</script>

<?php
}

// Render the layout with content
renderAdminLayout($page_title, 'addresses', 'renderAddressesContent');
?>
