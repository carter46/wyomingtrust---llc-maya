<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustId = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
if ($trustId <= 0) {
    header('Location: manage-trust.php');
    exit;
}

$page_title = 'Add Assets to LLC | WyomingTrust';
$active_nav = 'trusts';

include __DIR__ . '/includes/layout.php';
?>

<section class="w-full min-w-0 max-w-4xl mx-auto">
<a href="manage-trust.php?id=<?php echo (int) $trustId; ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back to LLC
</a>

<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Add Assets to LLC</h1>
<p id="trustSubtitle" class="text-on-surface-variant text-sm mb-6">Select additional deposit-ready cryptocurrencies for this LLC portfolio.</p>

<div id="loadingState" class="text-center py-12 text-on-surface-variant">Loading...</div>

<div id="mainContent" class="hidden space-y-8">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-4 sm:p-6">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
<h2 class="font-headline-md text-headline-md text-primary">Available Coins</h2>
<label class="inline-flex items-center gap-2 text-sm font-semibold text-secondary cursor-pointer">
<input type="checkbox" id="selectAllCoins" class="rounded text-secondary focus:ring-secondary" onchange="toggleSelectAll(this.checked)">
Select All
</label>
</div>
<div class="mb-4">
<label class="sr-only" for="coinSearchInput">Search coins</label>
<input type="search" id="coinSearchInput" placeholder="Search by name or symbol..." autocomplete="off" oninput="filterAvailableCoins()" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-sm text-primary focus:ring-2 focus:ring-secondary"/>
</div>
<p class="text-xs text-on-surface-variant mb-4">Only deposit-ready coins (with a configured deposit address) that are not yet on this LLC are shown.</p>
<div id="availableCoinsList" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
<p id="noAvailableMsg" class="hidden text-center py-8 text-on-surface-variant text-sm">All available deposit-ready coins are already in your LLC portfolio.</p>
</div>

<div class="flex flex-col sm:flex-row gap-3">
<button type="button" id="saveCoinsBtn" onclick="saveSelectedCoins()" class="flex-1 bg-primary text-on-primary py-3 rounded-xl font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
Add Selected Coins
</button>
<a href="manage-trust.php?id=<?php echo (int) $trustId; ?>" class="flex-1 text-center border border-outline-variant text-primary py-3 rounded-xl font-bold hover:bg-surface-container-low transition-colors">
Cancel
</a>
</div>

<div class="bg-surface-container-low rounded-2xl border border-outline-variant p-4 sm:p-6">
<h2 class="font-headline-md text-headline-md text-primary mb-4">Already in LLC</h2>
<div id="selectedCoinsList" class="space-y-3"></div>
<p id="noSelectedMsg" class="hidden text-center py-6 text-on-surface-variant text-sm">No coins selected yet.</p>
</div>
</div>

<div id="successPanel" class="hidden text-center py-10 sm:py-14 px-4">
<div class="w-16 h-16 mx-auto mb-5 rounded-full bg-deep-forest/10 flex items-center justify-center">
<?php echo wt_icon('check-circle', 'w-9 h-9 text-deep-forest'); ?>
</div>
<h2 class="font-headline-md text-headline-md text-primary mb-3">Assets Added Successfully</h2>
<p class="text-sm text-on-surface-variant max-w-md mx-auto mb-8">Your LLC portfolio has been updated with the new coins.</p>
<a href="manage-trust.php?id=<?php echo (int) $trustId; ?>" class="inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-10 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors">
Back to LLC
</a>
</div>
</section>

<script>
const TRUST_ID = <?php echo (int) $trustId; ?>;
let trust = null;
let allCoins = [];
let filteredAvailable = [];
let userAssets = [];
let cryptoPrices = {};
let pendingSelection = new Set();

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatUsd(value) {
    return '$' + (value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getEntrustedKeys() {
    const raw = trust?.entrusted_coins || trust?.trust_data?.entrusted_coins || [];
    return Array.isArray(raw) ? raw.map(k => String(k).toLowerCase()) : [];
}

function getAssetForCoin(coinKey) {
    return userAssets.find(a => String(a.coin_key).toLowerCase() === String(coinKey).toLowerCase());
}

function getCoinUsdValue(coinKey, balance) {
    const price = cryptoPrices[coinKey]?.usd || 0;
    return (parseFloat(balance) || 0) * price;
}

async function loadData() {
    try {
        const [trustRes, coinsRes, assetsRes] = await Promise.all([
            fetch(`../../api/user/trusts.php?id=${TRUST_ID}`),
            fetch('../../api/coins.php?for=depositable'),
            fetch('../../api/user/assets.php'),
        ]);
        const trustData = await trustRes.json();
        const coinsData = await coinsRes.json();
        const assetsData = await assetsRes.json();

        if (!trustData.success || !trustData.trust) {
            document.getElementById('loadingState').textContent = 'LLC not found.';
            return;
        }

        trust = trustData.trust;
        allCoins = coinsData.success && coinsData.coins ? coinsData.coins : [];
        userAssets = assetsData.success && assetsData.assets ? assetsData.assets : [];

        const name = trust.trust_name || 'Your LLC';
        document.getElementById('trustSubtitle').textContent = `Add assets to "${name}" (LLC #${TRUST_ID})`;

        const entrustedSet = new Set(getEntrustedKeys());
        const coinKeys = [...entrustedSet, ...allCoins.map(c => c.coin_key)].filter(Boolean);
        if (coinKeys.length) {
            const priceRes = await fetch(`../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(coinKeys.join(','))}&vs_currencies=usd`);
            if (priceRes.ok) cryptoPrices = await priceRes.json();
        }

        renderPage(entrustedSet);
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');
    } catch (error) {
        console.error('Error loading trust coins:', error);
        document.getElementById('loadingState').textContent = 'Failed to load data. Please try again.';
    }
}

function getAvailableCoins(entrustedSet) {
    return allCoins.filter(c => !entrustedSet.has(String(c.coin_key).toLowerCase()));
}

function filterAvailableCoins() {
    const entrustedSet = new Set(getEntrustedKeys());
    const q = (document.getElementById('coinSearchInput')?.value || '').trim().toLowerCase();
    const available = getAvailableCoins(entrustedSet);
    filteredAvailable = !q
        ? available
        : available.filter(c => {
            const name = String(c.display_name || '').toLowerCase();
            const symbol = String(c.symbol || '').toLowerCase();
            const key = String(c.coin_key || '').toLowerCase();
            return name.includes(q) || symbol.includes(q) || key.includes(q);
        });
    renderAvailableList(filteredAvailable);
}

function renderAvailableList(available) {
    const availableList = document.getElementById('availableCoinsList');
    const noAvailable = document.getElementById('noAvailableMsg');
    const saveBtn = document.getElementById('saveCoinsBtn');

    if (available.length === 0) {
        availableList.innerHTML = '';
        noAvailable.classList.remove('hidden');
        if (pendingSelection.size === 0) saveBtn.disabled = true;
        const searchQ = (document.getElementById('coinSearchInput')?.value || '').trim();
        noAvailable.textContent = searchQ
            ? 'No coins match your search.'
            : (allCoins.length === 0
                ? 'No deposit-ready coins are configured. An admin must add deposit addresses under Addresses / Coins Management.'
                : 'All available deposit-ready coins are already in your LLC portfolio.');
        return;
    }

    noAvailable.classList.add('hidden');
    availableList.innerHTML = available.map(coin => {
        const key = escapeHtml(coin.coin_key);
        const checked = pendingSelection.has(coin.coin_key);
        const logo = coin.logo
            ? `<img src="${escapeHtml(coin.logo)}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0" onerror="this.style.display='none'">`
            : `<span class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-xs font-bold shrink-0">${escapeHtml((coin.symbol || '?').slice(0, 3))}</span>`;
        return `
            <label class="flex items-center gap-3 p-4 border-2 ${checked ? 'border-secondary bg-secondary/5' : 'border-outline-variant/30'} rounded-xl cursor-pointer hover:border-secondary transition-all coin-option" data-coin-key="${key}">
                <input type="checkbox" class="coin-checkbox rounded text-secondary focus:ring-secondary" value="${key}" ${checked ? 'checked' : ''} onchange="onCoinToggle('${key}', this.checked)"/>
                ${logo}
                <span class="min-w-0">
                    <span class="block font-bold text-primary text-sm truncate">${escapeHtml(coin.display_name || coin.coin_key)}</span>
                    <span class="block text-xs text-on-surface-variant">${escapeHtml(coin.symbol || coin.coin_key.toUpperCase())}</span>
                </span>
            </label>
        `;
    }).join('');
    updateSelectAllState();
}

function renderPage(entrustedSet) {
    filteredAvailable = getAvailableCoins(entrustedSet);
    renderAvailableList(filteredAvailable);

    const selected = allCoins.filter(c => entrustedSet.has(String(c.coin_key).toLowerCase()));
    // Also show entrusted keys that might not be in depositable list anymore
    const selectedKeys = new Set(selected.map(c => String(c.coin_key).toLowerCase()));
    const orphanKeys = [...entrustedSet].filter(k => !selectedKeys.has(k));
    const orphanRows = orphanKeys.map(key => ({
        coin_key: key,
        display_name: key.replace(/_/g, ' '),
        symbol: key.split('_')[0].toUpperCase(),
        logo: null,
    }));
    const selectedAll = [...selected, ...orphanRows];

    const selectedList = document.getElementById('selectedCoinsList');
    const noSelected = document.getElementById('noSelectedMsg');

    if (selectedAll.length === 0) {
        selectedList.innerHTML = '';
        noSelected.classList.remove('hidden');
    } else {
        noSelected.classList.add('hidden');
        selectedList.innerHTML = selectedAll.map(coin => {
            const asset = getAssetForCoin(coin.coin_key);
            const balance = parseFloat(asset?.balance || 0);
            const usd = asset?.value_usd != null ? parseFloat(asset.value_usd) : getCoinUsdValue(coin.coin_key, balance);
            const logo = coin.logo
                ? `<img src="${escapeHtml(coin.logo)}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0" onerror="this.style.display='none'">`
                : `<span class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center text-xs font-bold shrink-0">${escapeHtml((coin.symbol || '?').slice(0, 3))}</span>`;
            return `
                <div class="flex items-center justify-between gap-3 p-4 bg-surface-container-lowest rounded-xl border border-outline-variant">
                    <div class="flex items-center gap-3 min-w-0">
                        ${logo}
                        <div class="min-w-0">
                            <p class="font-semibold text-primary truncate">${escapeHtml(coin.display_name || coin.coin_key)}</p>
                            <p class="text-xs text-on-surface-variant">${balance.toFixed(8)} ${escapeHtml(coin.symbol || '')}</p>
                        </div>
                    </div>
                    <p class="font-bold text-primary shrink-0">${formatUsd(usd)}</p>
                </div>
            `;
        }).join('');
    }

    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function onCoinToggle(coinKey, checked) {
    if (checked) pendingSelection.add(coinKey);
    else pendingSelection.delete(coinKey);
    document.getElementById('saveCoinsBtn').disabled = pendingSelection.size === 0;
    const label = document.querySelector(`.coin-option[data-coin-key="${coinKey}"]`);
    if (label) {
        label.classList.toggle('border-secondary', checked);
        label.classList.toggle('bg-secondary/5', checked);
    }
    updateSelectAllState();
}

function toggleSelectAll(checked) {
    document.querySelectorAll('.coin-checkbox').forEach(cb => {
        cb.checked = checked;
        onCoinToggle(cb.value, checked);
    });
    if (!checked) pendingSelection.clear();
    document.getElementById('saveCoinsBtn').disabled = pendingSelection.size === 0;
}

function updateSelectAllState() {
    const boxes = document.querySelectorAll('.coin-checkbox');
    const selectAll = document.getElementById('selectAllCoins');
    if (!selectAll || boxes.length === 0) return;
    const checkedCount = [...boxes].filter(b => b.checked).length;
    selectAll.checked = checkedCount === boxes.length && boxes.length > 0;
    selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
}

let csrfToken = null;
async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    const res = await fetch('../../api/session.php', { credentials: 'same-origin' });
    const data = await res.json();
    csrfToken = data.csrf_token || null;
    return csrfToken;
}

async function saveSelectedCoins() {
    if (pendingSelection.size === 0) return;
    const btn = document.getElementById('saveCoinsBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    try {
        // CSRF: require_csrf_token() accepts X-CSRF-Token header or csrf_token in JSON body (same as manage-trust.php)
        const token = await getCsrfToken();
        const response = await fetch('../../api/user/trusts.php', {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token || '',
            },
            body: JSON.stringify({
                id: TRUST_ID,
                add_entrusted_coins: [...pendingSelection],
                csrf_token: token,
            }),
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('mainContent').classList.add('hidden');
            document.getElementById('successPanel').classList.remove('hidden');
        } else {
            alert(data.message || 'Failed to add assets');
            btn.disabled = false;
            btn.textContent = 'Add Selected Coins';
        }
    } catch (error) {
        console.error('Error saving coins:', error);
        alert('Failed to add assets. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Add Selected Coins';
    }
}

document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
