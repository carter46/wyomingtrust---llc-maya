<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'Transaction History | WyomingTrust';
$active_nav = '';

include __DIR__ . '/includes/layout.php';
?>

<section>
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Transaction History</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">View all your cryptocurrency transactions</p>
</section>

<section>
<div class="flex flex-col sm:flex-row gap-4 mb-6">
<select id="typeFilter" class="px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
<option value="">All Types</option>
<option value="send">Send</option>
<option value="receive">Receive</option>
<option value="swap">Swap</option>
<option value="payment">Payment</option>
</select>
<select id="statusFilter" class="px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
<option value="">All Status</option>
<option value="completed">Completed</option>
<option value="pending">Pending</option>
<option value="failed">Failed</option>
</select>
<input type="text" id="searchInput" placeholder="Search transactions..." class="flex-1 px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
</div>
<div id="transactionsContainer" class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden">
<div class="p-10 text-center text-on-surface-variant">
<div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent mb-4"></div>
<p>Loading transactions...</p>
</div>
</div>
</section>

<script>
let transactions = [];
let cryptoPrices = {};

async function loadTransactions() {
    try {
        const response = await fetch('../../api/user/transactions.php');
        const data = await response.json();
        
        if (data.success && data.transactions) {
            transactions = data.transactions;
            await fetchCryptoPrices();
            renderTransactions();
        } else {
            document.getElementById('transactionsContainer').innerHTML = '<div class="text-center py-10 text-error">Failed to load transactions</div>';
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        document.getElementById('transactionsContainer').innerHTML = '<div class="text-center py-10 text-error">Error loading transactions</div>';
    }
}

async function fetchCryptoPrices() {
    const coinIds = [...new Set(transactions.map(t => t.coin_key || t.asset_symbol?.toLowerCase()).filter(Boolean))];
    if (coinIds.length === 0) return;
    
    try {
        const response = await fetch(`../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(coinIds.join(','))}&vs_currencies=usd`);
        if (response.ok) {
            cryptoPrices = await response.json();
        }
    } catch (error) {
        console.error('Error fetching prices:', error);
    }
}

function renderTransactions() {
    const container = document.getElementById('transactionsContainer');
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    let filtered = transactions.filter(t => {
        if (typeFilter && t.type !== typeFilter) return false;
        if (statusFilter && t.status !== statusFilter) return false;
        if (searchTerm) {
            const searchable = `${t.asset_symbol} ${t.coin_name} ${t.recipient} ${t.status}`.toLowerCase();
            if (!searchable.includes(searchTerm)) return false;
        }
        return true;
    });
    
    if (filtered.length === 0) {
        container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No transactions found</div>';
        return;
    }
    
    const html = filtered.map(t => {
        const amount = parseFloat(t.amount || 0);
        const coinSymbol = t.coin_symbol || t.asset_symbol || '?';
        const logo = t.coin_logo || '';
        const typeIcon = t.type === 'send' ? 'send' : t.type === 'receive' ? 'receive' : t.type === 'swap' ? 'swap' : 'payments';
        const statusClass = t.status === 'completed' ? 'bg-deep-forest/10 text-deep-forest' : 
                           t.status === 'pending' ? 'bg-warm-cream text-on-surface' :
                           'bg-error-container text-error';
        
        return `
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 sm:p-6 border-b border-outline-variant hover:bg-surface-container-low">
                <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        ${wtIcon(typeIcon, 'w-5 h-5')}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-sm sm:text-base text-on-surface">${escapeHtml(t.type.charAt(0).toUpperCase() + t.type.slice(1))}</h4>
                            <span class="px-2 py-0.5 text-xs rounded ${statusClass}">${escapeHtml(t.status)}</span>
                        </div>
                        <p class="text-xs sm:text-sm text-on-surface-variant">${new Date(t.created_at).toLocaleString()}</p>
                        ${t.recipient ? `<p class="text-xs text-on-surface-variant/70 mt-1 truncate">To: ${escapeHtml(t.recipient.substring(0, 20))}${t.recipient.length > 20 ? '...' : ''}</p>` : ''}
                    </div>
                </div>
                <div class="flex items-center gap-3 sm:gap-6 flex-shrink-0">
                    <div class="text-right">
                        <p class="text-sm sm:text-base font-bold text-on-surface">${t.type === 'send' ? '-' : t.type === 'receive' ? '+' : ''}${amount.toFixed(8)} ${coinSymbol}</p>
                        ${cryptoPrices[t.coin_key || t.asset_symbol?.toLowerCase()] ? 
                            `<p class="text-xs text-on-surface-variant">$${(amount * (cryptoPrices[t.coin_key || t.asset_symbol?.toLowerCase()].usd || 0)).toFixed(2)}</p>` : 
                            '<p class="text-xs text-on-surface-variant">--</p>'}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('typeFilter')?.addEventListener('change', renderTransactions);
document.getElementById('statusFilter')?.addEventListener('change', renderTransactions);
document.getElementById('searchInput')?.addEventListener('input', renderTransactions);

document.addEventListener('DOMContentLoaded', loadTransactions);
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
