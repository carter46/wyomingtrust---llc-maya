<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$page_title = 'Swap Crypto | WyomingTrust';
$active_nav = $trustIdParam > 0 ? 'trusts' : 'crypto-assets';
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

<div class="bg-warm-cream border border-outline-variant rounded-2xl p-4 sm:p-6 mb-6">
<div class="flex items-start gap-3">
<?php echo wt_icon('warning', 'text-secondary flex-shrink-0'); ?>
<div class="text-sm text-on-surface">
<p class="font-semibold mb-2">Important Security Notice:</p>
<ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-on-surface-variant">
<li>Exchange rates are provided by CoinGecko and may fluctuate</li>
<li>Swaps are executed at current market rates</li>
<li>Transaction fees apply to all swaps</li>
<li>Cryptocurrency swaps are typically irreversible</li>
<li>Always verify amounts before confirming</li>
</ul>
</div>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-8">
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
<button onclick="swapAssets()" class="p-2 bg-surface-container-low rounded-full hover:bg-surface-container text-on-surface">
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
<button onclick="executeSwap()" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors">
Swap
</button>
</div>
</section>

<div id="assetModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
<div class="bg-surface-container-lowest rounded-2xl max-w-md w-full max-h-[80vh] overflow-y-auto border border-outline-variant">
<div class="p-6 border-b border-outline-variant flex items-center justify-between">
<h3 class="font-bold text-lg text-primary">Select Asset</h3>
<button onclick="closeAssetModal()" class="text-on-surface-variant hover:text-on-surface">
<?php echo wt_icon('close', 'w-5 h-5'); ?>
</button>
</div>
<div id="assetList" class="p-4"></div>
</div>
</div>

<script>
let userAssets = [];
let fromAsset = null;
let toAsset = null;
let cryptoPrices = {};
let currentModal = null;

async function loadAssets() {
    try {
        const response = await fetch('../../api/user/assets.php');
        const data = await response.json();
        if (data.success && data.assets) {
            userAssets = data.assets.filter(a => parseFloat(a.balance || 0) > 0);
            if (userAssets.length > 0) {
                const prefKey = new URLSearchParams(window.location.search).get('coin_key') || '';
                const preferred = prefKey ? userAssets.find(a => a.coin_key === prefKey) : null;
                fromAsset = preferred || userAssets[0];
                toAsset = userAssets.find(a => a.coin_key !== fromAsset.coin_key) || userAssets[0];
                updateAssetSelectors();
            }
            renderAssetModal();
        }
    } catch (error) {
        console.error('Error loading assets:', error);
    }
}

async function fetchCryptoPrices() {
    if (userAssets.length === 0) return;
    try {
        const coinIds = userAssets.map(a => a.coin_key).filter(Boolean).join(',');
        const response = await fetch(`../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(coinIds)}&vs_currencies=usd`);
        if (response.ok) cryptoPrices = await response.json();
    } catch (error) {
        console.error('Error fetching prices:', error);
    }
}

function updateAssetSelectors() {
    if (fromAsset) {
        document.getElementById('fromAssetLogo').src = fromAsset.logo || '';
        document.getElementById('fromAssetLogo').classList.remove('hidden');
        document.getElementById('fromAssetName').textContent = fromAsset.display_name || fromAsset.symbol;
        document.getElementById('fromBalance').textContent = `${parseFloat(fromAsset.balance || 0).toFixed(8)} ${fromAsset.symbol}`;
    }
    if (toAsset) {
        document.getElementById('toAssetLogo').src = toAsset.logo || '';
        document.getElementById('toAssetLogo').classList.remove('hidden');
        document.getElementById('toAssetName').textContent = toAsset.display_name || toAsset.symbol;
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
        document.getElementById('toAmount').value = toAmount.toFixed(8);
        document.getElementById('exchangeRate').textContent = `1 ${fromAsset.symbol} = ${exchangeRate.toFixed(8)} ${toAsset.symbol}`;
        document.getElementById('swapFee').textContent = `~${(amount * 0.003).toFixed(8)} ${fromAsset.symbol}`;
    }
}

function swapAssets() {
    const temp = fromAsset;
    fromAsset = toAsset;
    toAsset = temp;
    updateAssetSelectors();
}

function renderAssetModal() {
    const list = document.getElementById('assetList');
    list.innerHTML = userAssets.map(asset => `
        <div onclick="selectAsset('${asset.coin_key}')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low cursor-pointer">
            <img src="${asset.logo || ''}" alt="${asset.display_name}" class="w-10 h-10 rounded-full" onerror="this.style.display='none'">
            <div class="flex-1">
                <p class="font-semibold text-on-surface">${asset.display_name}</p>
                <p class="text-xs text-on-surface-variant">${parseFloat(asset.balance || 0).toFixed(8)} ${asset.symbol}</p>
            </div>
        </div>
    `).join('');
}

document.getElementById('fromAssetSelector')?.addEventListener('click', () => {
    currentModal = 'from';
    document.getElementById('assetModal').classList.remove('hidden');
    document.getElementById('assetModal').classList.add('flex');
});

document.getElementById('toAssetSelector')?.addEventListener('click', () => {
    currentModal = 'to';
    document.getElementById('assetModal').classList.remove('hidden');
    document.getElementById('assetModal').classList.add('flex');
});

function selectAsset(coinKey) {
    const asset = userAssets.find(a => a.coin_key === coinKey);
    if (asset) {
        if (currentModal === 'from') {
            fromAsset = asset;
        } else {
            toAsset = asset;
        }
        updateAssetSelectors();
        closeAssetModal();
    }
}

function closeAssetModal() {
    document.getElementById('assetModal').classList.add('hidden');
    document.getElementById('assetModal').classList.remove('flex');
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
        const tokenResponse = await fetch('../../api/session.php');
        const tokenData = await tokenResponse.json();
        const csrfToken = tokenData.csrf_token || null;
        
        const response = await fetch('../../api/user/swap.php', {
            method: 'POST',
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
