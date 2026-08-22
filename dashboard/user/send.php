<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$isLiquidateMode = isset($_GET['mode']) && $_GET['mode'] === 'liquidate';
$page_title = $isLiquidateMode ? 'Liquidate Asset | WyomingTrust' : 'Send Crypto | WyomingTrust';
$active_nav = $trustIdParam > 0 ? 'trusts' : '';

include __DIR__ . '/includes/layout.php';
?>

<section class="w-full min-w-0">
<?php if ($trustIdParam > 0 && $coinKeyParam !== ''): ?>
<a href="asset-detail.php?coin_key=<?php echo escape_html($coinKeyParam); ?>&trust_id=<?php echo $trustIdParam; ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-4">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back to Asset
</a>
<?php endif; ?>
<h1 class="font-headline-lg text-headline-lg text-primary mb-4" id="pageHeading"><?php echo $isLiquidateMode ? 'Liquidate Asset' : 'Send Cryptocurrency'; ?></h1>

<?php if ($isLiquidateMode): ?>
<div id="liquidationFeeNotice" class="hidden bg-secondary/10 border border-secondary/20 rounded-xl p-4 mb-6 text-sm text-on-surface">
<strong>Liquidation fee:</strong> <span id="liquidationFeeNoticeText">Paid at checkout (pending admin approval).</span>
</div>
<?php endif; ?>

<div class="bg-warm-cream border border-outline-variant rounded-2xl p-4 sm:p-6 mb-6">
<div class="flex items-start gap-3">
<?php echo wt_icon('warning', 'text-secondary flex-shrink-0'); ?>
<div class="text-sm text-on-surface">
<p class="font-semibold mb-2">Security Notice:</p>
<ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-on-surface-variant">
<li>All wallet addresses are validated before transactions</li>
<li>Cryptocurrency transactions are irreversible - verify addresses carefully</li>
<li>We use AES-256-CBC encryption to protect your wallet data</li>
<li>Your private keys are never stored in plain text</li>
<li>Double-check recipient addresses before confirming transactions</li>
</ul>
</div>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-8" id="sendFormPanel">
<div id="assetSelector" class="flex items-center gap-3 p-4 border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low mb-6">
<img id="selectedAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<span id="selectedAssetName" class="font-bold text-on-surface">Select Asset</span>
<?php echo wt_icon('chevron-down', 'ml-auto text-on-surface-variant'); ?>
</div>
<div id="lockedAssetDisplay" class="hidden flex items-center gap-3 p-4 border border-outline-variant rounded-lg bg-surface-container-low mb-6">
<img id="lockedAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<div>
<p id="lockedAssetName" class="font-bold text-on-surface">--</p>
<p id="lockedAssetSymbol" class="text-xs text-on-surface-variant">--</p>
</div>
</div>
<div class="mb-6">
<label class="block text-sm font-semibold mb-2 text-on-surface">Recipient Address</label>
<div class="flex gap-2">
<input type="text" id="recipientAddress" placeholder="Enter wallet address" class="flex-1 px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
<button onclick="pasteAddress()" class="px-4 py-3 bg-surface-container-low rounded-lg hover:bg-surface-container text-on-surface">
<?php echo wt_icon('edit', 'text-base'); ?>
</button>
</div>
</div>
<div class="mb-6">
<label class="block text-sm font-semibold mb-2 text-on-surface">Amount</label>
<div class="flex gap-2 items-center">
<input type="number" id="amountInput" step="0.00000001" placeholder="0.00" oninput="calculateUSD()" class="flex-1 px-4 py-3 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
<button onclick="setMaxAmount()" class="px-4 py-3 bg-surface-container-low rounded-lg hover:bg-surface-container text-xs font-semibold text-on-surface">MAX</button>
</div>
<p class="text-xs text-on-surface-variant mt-2">Balance: <span id="selectedAssetBalance">--</span></p>
<p class="text-xs text-secondary mt-1" id="amountUSD">≈ $0.00</p>
</div>
<div class="mb-6">
<label class="block text-sm font-semibold mb-2 text-on-surface">Network Fee</label>
<div class="flex gap-2">
<button onclick="selectFee('slow')" class="fee-option flex-1 px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">Slow</button>
<button onclick="selectFee('normal')" class="fee-option active flex-1 px-4 py-2 border border-primary bg-primary/10 rounded-lg text-sm text-on-surface">Normal</button>
<button onclick="selectFee('fast')" class="fee-option flex-1 px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">Fast</button>
</div>
<p class="text-xs text-on-surface-variant mt-2">Fee: <span id="networkFee">--</span></p>
</div>
<div class="mb-6 p-4 bg-surface-container-low rounded-lg">
<div class="flex justify-between text-sm mb-2">
<span class="text-on-surface-variant">Total</span>
<span class="font-bold text-on-surface" id="totalAmount">--</span>
</div>
<div class="flex justify-between text-xs text-on-surface-variant">
<span>Total USD</span>
<span id="totalUSD">--</span>
</div>
</div>
<button type="button" onclick="sendTransaction()" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:bg-primary/90 transition-colors" id="submitBtn">
<?php echo $isLiquidateMode ? 'Confirm Liquidation' : 'Send Transaction'; ?>
</button>
</div>

<div id="liquidationSuccessPanel" class="hidden bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 sm:p-10 text-center">
<div class="w-16 h-16 mx-auto mb-5 rounded-full bg-deep-forest/10 flex items-center justify-center">
<?php echo wt_icon('check-circle', 'w-9 h-9 text-deep-forest'); ?>
</div>
<h2 class="font-headline-md text-headline-md text-primary mb-3">Liquidation Request Submitted</h2>
<p class="text-sm sm:text-base text-on-surface-variant max-w-md mx-auto mb-2">
Your liquidation request is <strong class="text-primary">pending admin approval</strong>. An administrator will review and process it shortly.
</p>
<p class="text-sm text-on-surface-variant max-w-md mx-auto mb-8">
Your balance will not change until the request is approved. You will be notified once processing is complete.
</p>
<button type="button" id="liquidationDoneBtn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-10 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors">
Done
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
let selectedAsset = null;
let cryptoPrices = {};
let selectedFee = 'normal';

const fees = {
    slow: { btc: 0.0001, eth: 0.001, ltc: 0.001, bch: 0.001, pol: 0.01, doge: 1, usdt: 1, trx: 1, xrp: 0.01, ada: 0.5, sol: 0.0001, dot: 0.01, bnb: 0.001, usdc: 1 },
    normal: { btc: 0.0002, eth: 0.002, ltc: 0.002, bch: 0.002, pol: 0.02, doge: 2, usdt: 2, trx: 2, xrp: 0.02, ada: 1, sol: 0.0002, dot: 0.02, bnb: 0.002, usdc: 2 },
    fast: { btc: 0.0005, eth: 0.005, ltc: 0.005, bch: 0.005, pol: 0.05, doge: 5, usdt: 5, trx: 5, xrp: 0.05, ada: 2, sol: 0.0005, dot: 0.05, bnb: 0.005, usdc: 5 }
};

function getFeeForCoin(coinKey) {
    const coinKeyLower = coinKey.toLowerCase();
    const feeTier = fees[selectedFee] || fees.normal;
    
    const feeMap = {
        'bitcoin': 'btc',
        'ethereum': 'eth',
        'litecoin': 'ltc',
        'bitcoin-cash': 'bch',
        'polygon': 'pol',
        'dogecoin': 'doge',
        'tether': 'usdt',
        'tron': 'trx',
        'ripple': 'xrp',
        'cardano': 'ada',
        'solana': 'sol',
        'polkadot': 'dot',
        'binancecoin': 'bnb',
        'usd-coin': 'usdc'
    };
    
    const feeSymbol = feeMap[coinKeyLower] || 'btc';
    return feeTier[feeSymbol] || 0.0001;
}

const urlParams = new URLSearchParams(window.location.search);
const urlCoinKey = urlParams.get('coin_key');
const urlTrustId = urlParams.get('trust_id');
const isLiquidateMode = urlParams.get('mode') === 'liquidate';
const lockToSingleCoin = !!urlCoinKey;

function getNetworkFee() {
    if (!selectedAsset) return 0;
    return getFeeForCoin(selectedAsset.coin_key);
}

function getCombinedFee() {
    return getNetworkFee();
}

async function ensureLiquidationCheckout() {
    if (!isLiquidateMode || !urlCoinKey) return true;

    try {
        const params = new URLSearchParams({ type: 'liquidation', coin_key: urlCoinKey });
        if (urlTrustId) params.set('trust_id', urlTrustId);
        const res = await fetch(`../../api/user/checkout.php?${params.toString()}`, { credentials: 'same-origin' });
        const data = await res.json();

        if (!data.success) {
            console.error('Checkout verification failed:', data.message || 'Unknown error');
            return false;
        }

        if (!data.has_fee) return true;

        if (!data.payment_satisfied && !data.fee_paid) {
            if (data.already_submitted && data.payment_status === 'pending') {
                const notice = document.getElementById('liquidationFeeNotice');
                const noticeText = document.getElementById('liquidationFeeNoticeText');
                if (notice) notice.classList.remove('hidden');
                if (noticeText) {
                    noticeText.textContent = `$${parseFloat(data.fee).toFixed(2)} fee payment is pending admin approval. You cannot submit liquidation until it is approved.`;
                }
                return false;
            }
            window.location.href = `checkout.php?${params.toString()}`;
            return false;
        }

        const notice = document.getElementById('liquidationFeeNotice');
        const noticeText = document.getElementById('liquidationFeeNoticeText');
        if (notice) notice.classList.remove('hidden');
        if (noticeText) {
            noticeText.textContent = `$${parseFloat(data.fee).toFixed(2)} liquidation fee approved. Only network fees apply to the crypto transfer.`;
        }

        return true;
    } catch (error) {
        console.error('Checkout verification failed:', error);
        return false;
    }
}

async function loadCoinFromCatalog(coinKey) {
    const response = await fetch('../../api/coins.php');
    const data = await response.json();
    if (!data.success || !data.coins) return null;
    return data.coins.find(c => c.coin_key === coinKey) || null;
}

async function loadAssets() {
    try {
        const response = await fetch('../../api/user/assets.php');
        const data = await response.json();
        const allAssets = data.success && data.assets ? data.assets : [];

        if (lockToSingleCoin) {
            let asset = allAssets.find(a => a.coin_key === urlCoinKey);
            if (!asset) {
                const coin = await loadCoinFromCatalog(urlCoinKey);
                if (coin) {
                    asset = {
                        coin_key: coin.coin_key,
                        display_name: coin.display_name,
                        symbol: coin.symbol,
                        logo: coin.logo,
                        balance: 0,
                    };
                }
            }
            if (asset) {
                selectedAsset = asset;
                userAssets = [asset];
                updateSelectedAsset();
                updateLockedAssetDisplay(asset);
                document.getElementById('assetSelector').classList.add('hidden');
                document.getElementById('lockedAssetDisplay').classList.remove('hidden');
                await fetchCryptoPrices();
            } else {
                document.getElementById('assetSelector').classList.add('hidden');
                document.getElementById('lockedAssetDisplay').classList.remove('hidden');
                document.getElementById('lockedAssetName').textContent = 'Asset not found';
                document.getElementById('lockedAssetSymbol').textContent = urlCoinKey;
            }
            return;
        }

        userAssets = allAssets.filter(a => parseFloat(a.balance || 0) > 0);

        if (urlCoinKey) {
            const asset = userAssets.find(a => a.coin_key === urlCoinKey);
            if (asset) selectedAsset = asset;
        }

        if (!selectedAsset && userAssets.length > 0) {
            selectedAsset = userAssets[0];
        }

        if (selectedAsset) {
            updateSelectedAsset();
        }
        renderAssetModal();
        await fetchCryptoPrices();
    } catch (error) {
        console.error('Error loading assets:', error);
    }
}

function updateLockedAssetDisplay(asset) {
    if (!asset) return;
    const logo = document.getElementById('lockedAssetLogo');
    if (asset.logo) {
        logo.src = asset.logo;
        logo.classList.remove('hidden');
    }
    document.getElementById('lockedAssetName').textContent = asset.display_name || asset.symbol || asset.coin_key;
    document.getElementById('lockedAssetSymbol').textContent = asset.symbol || asset.coin_key;
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

function updateSelectedAsset() {
    if (!selectedAsset) return;
    document.getElementById('selectedAssetLogo').src = selectedAsset.logo || '';
    document.getElementById('selectedAssetLogo').classList.remove('hidden');
    document.getElementById('selectedAssetName').textContent = selectedAsset.display_name || selectedAsset.symbol;
    document.getElementById('selectedAssetBalance').textContent = `${parseFloat(selectedAsset.balance || 0).toFixed(8)} ${selectedAsset.symbol}`;
    calculateTotal();
}

function renderAssetModal() {
    const list = document.getElementById('assetList');
    if (!list) return;
    
    if (!userAssets || userAssets.length === 0) {
        list.innerHTML = '<div class="p-4 text-center text-on-surface-variant">No assets available. <a href="link-wallet.php" class="text-secondary hover:underline">Link a wallet</a></div>';
        return;
    }
    
    list.innerHTML = userAssets.map(asset => {
        const balance = parseFloat(asset.balance || 0);
        const displayName = asset.display_name || asset.symbol || 'Unknown';
        const symbol = asset.symbol || '';
        const coinKey = asset.coin_key || '';
        const logo = asset.logo || '';
        
        return `
        <div onclick="selectAsset('${coinKey}')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low cursor-pointer">
            <img src="${logo}" alt="${displayName}" class="w-10 h-10 rounded-full" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center hidden">
                <span class="text-xs font-bold text-on-surface">${displayName.charAt(0)}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold truncate text-on-surface">${displayName}</p>
                <p class="text-xs text-on-surface-variant">${balance.toFixed(8)} ${symbol}</p>
            </div>
        </div>
        `;
    }).join('');
}

document.getElementById('assetSelector')?.addEventListener('click', () => {
    if (lockToSingleCoin) return;
    document.getElementById('assetModal').classList.remove('hidden');
    document.getElementById('assetModal').classList.add('flex');
});

function selectAsset(coinKey) {
    selectedAsset = userAssets.find(a => a.coin_key === coinKey);
    if (selectedAsset) {
        updateSelectedAsset();
        closeAssetModal();
    }
}

function closeAssetModal() {
    document.getElementById('assetModal').classList.add('hidden');
    document.getElementById('assetModal').classList.remove('flex');
}

function selectFee(feeType) {
    selectedFee = feeType;
    document.querySelectorAll('.fee-option').forEach(opt => {
        opt.classList.remove('active', 'bg-primary/10', 'border-primary');
        opt.classList.add('border-outline-variant', 'bg-surface-container-lowest');
    });
    const targetBtn = document.querySelector(`[onclick="selectFee('${feeType}')"]`);
    if (targetBtn) {
        targetBtn.classList.add('active', 'bg-primary/10', 'border-primary');
        targetBtn.classList.remove('border-outline-variant', 'bg-surface-container-lowest');
    }
    calculateTotal();
}

function calculateUSD() {
    if (!selectedAsset) return;
    const amount = parseFloat(document.getElementById('amountInput').value) || 0;
    const price = cryptoPrices[selectedAsset.coin_key]?.usd || 0;
    document.getElementById('amountUSD').textContent = `≈ $${(amount * price).toFixed(2)}`;
    calculateTotal();
}

function calculateTotal() {
    if (!selectedAsset) return;
    const amount = parseFloat(document.getElementById('amountInput').value) || 0;
    const networkFee = getNetworkFee();
    const fee = networkFee;
    const total = amount + fee;
    const balance = parseFloat(selectedAsset.balance || 0);
    
    document.getElementById('totalAmount').textContent = `${total.toFixed(8)} ${selectedAsset.symbol}`;
    const price = cryptoPrices[selectedAsset.coin_key]?.usd || 0;
    document.getElementById('totalUSD').textContent = `$${(total * price).toFixed(2)}`;
    document.getElementById('networkFee').textContent = `~${networkFee.toFixed(8)} ${selectedAsset.symbol}`;
    
    if (total > balance) {
        document.getElementById('totalAmount').classList.add('text-error');
    } else {
        document.getElementById('totalAmount').classList.remove('text-error');
    }
}

function setMaxAmount() {
    if (!selectedAsset) return;
    const balance = parseFloat(selectedAsset.balance || 0);
    const fee = getCombinedFee();
    const maxAmount = Math.max(0, balance - fee);
    document.getElementById('amountInput').value = maxAmount.toFixed(8);
    calculateUSD();
}

async function pasteAddress() {
    try {
        const text = await navigator.clipboard.readText();
        document.getElementById('recipientAddress').value = text;
    } catch (err) {
        alert('Unable to access clipboard. Please paste manually.');
    }
}

async function sendTransaction() {
    if (!selectedAsset) {
        alert('Please select an asset');
        return;
    }
    const amount = parseFloat(document.getElementById('amountInput').value);
    const recipient = document.getElementById('recipientAddress').value.trim();
    const balance = parseFloat(selectedAsset.balance || 0);
    const networkFee = getNetworkFee();
    const fee = networkFee;
    const total = amount + fee;
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    if (!recipient) {
        alert('Please enter recipient address');
        return;
    }
    if (total > balance) {
        alert(`Insufficient balance. You have ${balance.toFixed(8)} ${selectedAsset.symbol}, but need ${total.toFixed(8)} (including fee)`);
        return;
    }
    
    if (!confirm(`${isLiquidateMode ? 'Liquidate' : 'Send'} ${amount.toFixed(8)} ${selectedAsset.symbol} to ${recipient.substring(0, 10)}...?\nFee: ${fee.toFixed(8)} ${selectedAsset.symbol}\nTotal: ${total.toFixed(8)} ${selectedAsset.symbol}`)) {
        return;
    }
    
    try {
        const token = await getCsrfToken();
        const body = {
            coin_key: selectedAsset.coin_key,
            recipient,
            amount,
            fee: networkFee,
            csrf_token: token
        };
        if (isLiquidateMode) {
            body.is_liquidation = true;
            if (urlTrustId) body.trust_id = parseInt(urlTrustId, 10);
        }
        const response = await fetch('../../api/user/send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token || ''
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();
        if (!data.success && data.redirect_checkout && isLiquidateMode && urlCoinKey) {
            const params = new URLSearchParams({ type: 'liquidation', coin_key: urlCoinKey });
            if (urlTrustId) params.set('trust_id', urlTrustId);
            window.location.href = `checkout.php?${params.toString()}`;
            return;
        }
        if (data.success) {
            if (isLiquidateMode && data.pending) {
                document.getElementById('sendFormPanel')?.classList.add('hidden');
                document.querySelector('.bg-warm-cream')?.classList.add('hidden');
                document.getElementById('pageHeading')?.classList.add('hidden');
                const panel = document.getElementById('liquidationSuccessPanel');
                panel?.classList.remove('hidden');
                const doneBtn = document.getElementById('liquidationDoneBtn');
                if (doneBtn) {
                    doneBtn.onclick = () => {
                        if (urlTrustId && urlCoinKey) {
                            window.location.href = `asset-detail.php?coin_key=${encodeURIComponent(urlCoinKey)}&trust_id=${encodeURIComponent(urlTrustId)}`;
                        } else if (urlTrustId) {
                            window.location.href = `manage-trust.php?id=${encodeURIComponent(urlTrustId)}`;
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    };
                }
                return;
            }
            alert(isLiquidateMode ? 'Liquidation transaction sent successfully!' : 'Transaction sent successfully!');
            if (urlTrustId && urlCoinKey) {
                window.location.href = `asset-detail.php?coin_key=${encodeURIComponent(urlCoinKey)}&trust_id=${encodeURIComponent(urlTrustId)}`;
            } else if (urlTrustId) {
                window.location.href = `manage-trust.php?id=${encodeURIComponent(urlTrustId)}`;
            } else {
                window.location.href = 'dashboard.php';
            }
        } else {
            alert(data.message || 'Failed to send transaction');
        }
    } catch (error) {
        console.error('Error sending transaction:', error);
        alert('Failed to send transaction: ' + (error.message || 'Unknown error'));
    }
}

let csrfToken = null;

async function getCsrfToken() {
    if (csrfToken) return csrfToken;
    try {
        const response = await fetch('../../api/session.php');
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

document.addEventListener('DOMContentLoaded', async () => {
    await getCsrfToken();
    const canProceed = await ensureLiquidationCheckout();
    if (!canProceed) {
        const notice = document.getElementById('liquidationFeeNotice');
        const pendingShown = notice && !notice.classList.contains('hidden');
        if (!pendingShown) {
            alert('Unable to verify liquidation fee. Please try again or return to the asset page.');
        }
        return;
    }
    await loadAssets();
    await fetchCryptoPrices();
});
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
