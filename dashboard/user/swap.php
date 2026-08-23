<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$page_title = 'Swap Crypto | WyomingTrust';
$active_nav = 'crypto-assets';
$swapBackHref = $coinKeyParam !== ''
    ? 'asset-detail.php?coin_key=' . rawurlencode($coinKeyParam) . ($trustIdParam > 0 ? '&trust_id=' . $trustIdParam : '')
    : 'assets.php';
$swapBackLabel = $coinKeyParam !== '' ? 'Back to Asset' : 'Back to Assets';

include __DIR__ . '/includes/layout.php';
?>

<section class="w-full min-w-0">
<a href="<?php echo escape_html($swapBackHref); ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> <?php echo escape_html($swapBackLabel); ?>
</a>
<h1 class="font-headline-lg text-headline-lg text-primary mb-4">Swap Cryptocurrency</h1>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-8 mb-6">
<div class="mb-6">
<label class="block text-sm font-semibold mb-2 text-on-surface">From</label>
<div id="fromAssetSelector" class="flex items-center gap-3 p-4 border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low mb-2">
<img id="fromAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<span id="fromAssetName" class="font-bold text-on-surface">Select Asset</span>
<?php echo wt_icon('chevron-down', 'ml-auto text-on-surface-variant'); ?>
</div>
<input type="number" id="fromAmount" step="0.00000001" placeholder="0.00" oninput="calculateSwap()" class="w-full px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
<p class="text-xs text-on-surface-variant mt-2">Balance: <span id="fromBalance">--</span></p>
</div>
<div class="flex justify-center my-4">
<button type="button" onclick="swapAssets()" class="p-2 bg-surface-container-low rounded-full hover:bg-surface-container text-on-surface">
<?php echo wt_icon('swap', 'text-2xl'); ?>
</button>
</div>
<div class="mb-6">
<label class="block text-sm font-semibold mb-2 text-on-surface">To</label>
<div id="toAssetSelector" class="flex items-center gap-3 p-4 border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low mb-2">
<img id="toAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<span id="toAssetName" class="font-bold text-on-surface">Select Asset</span>
<?php echo wt_icon('chevron-down', 'ml-auto text-on-surface-variant'); ?>
</div>
<input type="number" id="toAmount" readonly class="w-full px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-low text-sm text-on-surface">
<p class="text-xs text-on-surface-variant mt-2">Exchange Rate: <span id="exchangeRate">--</span></p>
</div>
<div class="mb-6 p-4 bg-surface-container-low rounded-lg">
<p class="text-xs text-on-surface-variant mb-2">Estimated Fee: <span id="swapFee">--</span></p>
</div>
<button type="button" onclick="executeSwap()" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors">
Swap
</button>
</div>

<div class="bg-surface-container-low border border-outline-variant rounded-2xl p-4 sm:p-6">
<div class="flex items-start gap-3">
<?php echo wt_icon('warning', 'text-secondary flex-shrink-0'); ?>
<div class="text-sm text-on-surface">
<p class="font-semibold mb-2">Important Security Notice:</p>
<ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-on-surface-variant">
<li>Exchange rates may fluctuate</li>
<li>Swaps are executed at current market rates</li>
<li>Transaction fees apply to all swaps</li>
<li>Cryptocurrency swaps are typically irreversible</li>
<li>Always verify amounts before confirming</li>
</ul>
</div>
</div>
</div>
</section>

<div id="assetModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
<div class="bg-surface-container-lowest rounded-2xl max-w-md w-full max-h-[80vh] overflow-y-auto border border-outline-variant">
<div class="p-6 border-b border-outline-variant flex items-center justify-between">
<h3 class="font-bold text-lg text-primary" id="assetModalTitle">Select Asset</h3>
<button type="button" onclick="closeAssetModal()" class="text-on-surface-variant hover:text-on-surface">
<?php echo wt_icon('close', 'w-5 h-5'); ?>
</button>
</div>
<div id="assetList" class="p-4"></div>
</div>
</div>

<script>
let fromAssets = [];   // coins user can spend (balance > 0)
let toAssets = [];     // all catalog coins (swap destination)
let fromAsset = null;
let toAsset = null;
let cryptoPrices = {};
let currentModal = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function normalizeCoin(c, balance) {
    return {
        coin_key: c.coin_key,
        display_name: c.display_name || c.symbol || c.coin_key,
        symbol: c.symbol || c.coin_key || '',
        logo: c.logo || '',
        balance: parseFloat(balance != null ? balance : (c.balance || 0)) || 0,
    };
}

async function loadAssets() {
    try {
        const [assetsRes, coinsRes] = await Promise.all([
            fetch('../../api/user/assets.php', { credentials: 'same-origin' }),
            fetch('../../api/coins.php', { credentials: 'same-origin' }),
        ]);
        const assetsData = await assetsRes.json();
        const coinsData = await coinsRes.json();

        const userList = (assetsData.success && Array.isArray(assetsData.assets)) ? assetsData.assets : [];
        const catalog = (coinsData.success && Array.isArray(coinsData.coins)) ? coinsData.coins : [];
        const balanceByKey = {};
        userList.forEach(a => {
            if (a.coin_key) balanceByKey[a.coin_key] = parseFloat(a.balance || 0) || 0;
        });

        fromAssets = userList
            .map(a => normalizeCoin(a))
            .filter(a => a.balance > 0);

        // Destination: full catalog (with user's balance if any)
        const catalogKeys = new Set();
        toAssets = catalog.map(c => {
            catalogKeys.add(c.coin_key);
            return normalizeCoin(c, balanceByKey[c.coin_key] || 0);
        });
        // Include any user-held coins missing from catalog
        userList.forEach(a => {
            if (a.coin_key && !catalogKeys.has(a.coin_key)) {
                toAssets.push(normalizeCoin(a));
            }
        });
        toAssets.sort((a, b) => String(a.display_name).localeCompare(String(b.display_name)));

        const prefKey = new URLSearchParams(window.location.search).get('coin_key') || '';
        if (fromAssets.length > 0) {
            fromAsset = (prefKey && fromAssets.find(a => a.coin_key === prefKey)) || fromAssets[0];
        } else {
            fromAsset = null;
        }

        if (toAssets.length > 0) {
            toAsset = toAssets.find(a => fromAsset && a.coin_key !== fromAsset.coin_key)
                || toAssets.find(a => a.coin_key !== prefKey)
                || toAssets[0];
        } else {
            toAsset = null;
        }

        updateAssetSelectors();
    } catch (error) {
        console.error('Error loading assets:', error);
        fromAssets = [];
        toAssets = [];
    }
}

async function fetchCryptoPrices() {
    const keys = [...new Set([
        ...fromAssets.map(a => a.coin_key),
        ...toAssets.map(a => a.coin_key),
        fromAsset?.coin_key,
        toAsset?.coin_key,
    ].filter(Boolean))];
    if (keys.length === 0) return;
    try {
        const response = await fetch(
            `../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(keys.join(','))}&vs_currencies=usd`,
            { credentials: 'same-origin' }
        );
        if (response.ok) {
            const data = await response.json();
            if (data && !data.error) cryptoPrices = data;
        }
    } catch (error) {
        console.error('Error fetching prices:', error);
    }
    calculateSwap();
}

function updateAssetSelectors() {
    const fromLogo = document.getElementById('fromAssetLogo');
    const toLogo = document.getElementById('toAssetLogo');

    if (fromAsset) {
        if (fromAsset.logo) {
            fromLogo.src = fromAsset.logo;
            fromLogo.classList.remove('hidden');
        } else {
            fromLogo.classList.add('hidden');
        }
        document.getElementById('fromAssetName').textContent = fromAsset.display_name || fromAsset.symbol;
        document.getElementById('fromBalance').textContent = `${fromAsset.balance.toFixed(8)} ${fromAsset.symbol}`;
    } else {
        fromLogo.classList.add('hidden');
        document.getElementById('fromAssetName').textContent = fromAssets.length ? 'Select Asset' : 'No funded assets';
        document.getElementById('fromBalance').textContent = '--';
    }

    if (toAsset) {
        if (toAsset.logo) {
            toLogo.src = toAsset.logo;
            toLogo.classList.remove('hidden');
        } else {
            toLogo.classList.add('hidden');
        }
        document.getElementById('toAssetName').textContent = toAsset.display_name || toAsset.symbol;
    } else {
        toLogo.classList.add('hidden');
        document.getElementById('toAssetName').textContent = 'Select Asset';
    }
    calculateSwap();
}

function calculateSwap() {
    if (!fromAsset || !toAsset) return;
    const amount = parseFloat(document.getElementById('fromAmount').value) || 0;
    const fromPrice = cryptoPrices[fromAsset.coin_key]?.usd || 0;
    const toPrice = cryptoPrices[toAsset.coin_key]?.usd || 0;

    if (fromPrice > 0 && toPrice > 0) {
        const exchangeRate = fromPrice / toPrice;
        const toAmount = amount * exchangeRate;
        document.getElementById('toAmount').value = amount > 0 ? toAmount.toFixed(8) : '';
        document.getElementById('exchangeRate').textContent = `1 ${fromAsset.symbol} = ${exchangeRate.toFixed(8)} ${toAsset.symbol}`;
        document.getElementById('swapFee').textContent = `~${(amount * 0.003).toFixed(8)} ${fromAsset.symbol}`;
    } else {
        document.getElementById('exchangeRate').textContent = 'Price unavailable';
        document.getElementById('swapFee').textContent = amount > 0 ? `~${(amount * 0.003).toFixed(8)} ${fromAsset.symbol}` : '--';
    }
}

function swapAssets() {
    if (!fromAsset || !toAsset) return;
    // Only flip if destination also has balance (can be spent as From)
    const toAsFrom = fromAssets.find(a => a.coin_key === toAsset.coin_key);
    if (!toAsFrom) {
        alert('You can only swap from assets you hold. Choose a funded coin in From.');
        return;
    }
    const prevFrom = fromAsset;
    fromAsset = toAsFrom;
    toAsset = toAssets.find(a => a.coin_key === prevFrom.coin_key) || prevFrom;
    updateAssetSelectors();
}

function openAssetModal(mode) {
    currentModal = mode;
    const title = document.getElementById('assetModalTitle');
    if (title) title.textContent = mode === 'from' ? 'Select asset to swap from' : 'Select asset to receive';
    renderAssetModal();
    const modal = document.getElementById('assetModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function renderAssetModal() {
    const list = document.getElementById('assetList');
    const items = currentModal === 'from' ? fromAssets : toAssets;

    if (!items.length) {
        list.innerHTML = currentModal === 'from'
            ? `<div class="text-sm text-on-surface-variant p-4 text-center">
                    No funded assets to swap from.<br>
                    <a href="receive.php" class="text-secondary font-semibold hover:underline mt-2 inline-block">Deposit crypto first</a>
               </div>`
            : `<div class="text-sm text-on-surface-variant p-4 text-center">No coins available.</div>`;
        return;
    }

    list.innerHTML = items.map(asset => {
        const bal = currentModal === 'from'
            ? `${asset.balance.toFixed(8)} ${escapeHtml(asset.symbol)}`
            : escapeHtml(asset.symbol || '');
        return `
            <div onclick="selectAsset('${escapeHtml(asset.coin_key)}')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low cursor-pointer">
                <img src="${escapeHtml(asset.logo || '')}" alt="" class="w-10 h-10 rounded-full ${asset.logo ? '' : 'hidden'}" onerror="this.style.display='none'">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-on-surface truncate">${escapeHtml(asset.display_name)}</p>
                    <p class="text-xs text-on-surface-variant">${bal}</p>
                </div>
            </div>
        `;
    }).join('');
}

document.getElementById('fromAssetSelector')?.addEventListener('click', () => openAssetModal('from'));
document.getElementById('toAssetSelector')?.addEventListener('click', () => openAssetModal('to'));

function selectAsset(coinKey) {
    const pool = currentModal === 'from' ? fromAssets : toAssets;
    const asset = pool.find(a => a.coin_key === coinKey);
    if (!asset) return;

    if (currentModal === 'from') {
        fromAsset = asset;
        if (toAsset && toAsset.coin_key === fromAsset.coin_key) {
            toAsset = toAssets.find(a => a.coin_key !== fromAsset.coin_key) || toAsset;
        }
    } else {
        if (fromAsset && coinKey === fromAsset.coin_key) {
            alert('Choose a different asset than the one you are swapping from.');
            return;
        }
        toAsset = asset;
    }
    updateAssetSelectors();
    closeAssetModal();
}

function closeAssetModal() {
    const modal = document.getElementById('assetModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function executeSwap() {
    if (!fromAsset || !toAsset) {
        alert('Please select both assets');
        return;
    }
    const fromAmount = parseFloat(document.getElementById('fromAmount').value);
    if (!fromAmount || fromAmount <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    if (fromAmount > parseFloat(fromAsset.balance || 0)) {
        alert('Insufficient balance');
        return;
    }
    try {
        const tokenResponse = await fetch('../../api/session.php', { credentials: 'same-origin' });
        const tokenData = await tokenResponse.json();
        const csrfToken = tokenData.csrf_token || null;

        const response = await fetch('../../api/user/swap.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken || ''
            },
            body: JSON.stringify({
                from_coin_key: fromAsset.coin_key,
                to_coin_key: toAsset.coin_key,
                from_amount: fromAmount,
                to_amount: parseFloat(document.getElementById('toAmount').value),
                fee: fromAmount * 0.003,
                csrf_token: csrfToken
            })
        });
        const data = await response.json();
        if (data.success) {
            alert('Swap completed successfully!');
            window.location.href = 'transactions.php';
        } else {
            alert(data.message || 'Failed to execute swap');
        }
    } catch (error) {
        console.error('Error executing swap:', error);
        alert('Failed to execute swap');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadAssets();
    await fetchCryptoPrices();
});
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
