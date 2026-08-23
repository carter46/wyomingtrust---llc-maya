<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$coinKeyParam = isset($_GET['coin_key']) ? sanitize_text($_GET['coin_key']) : '';
$page_title = 'Deposit Crypto | WyomingTrust';
$active_nav = $trustIdParam > 0 ? 'trusts' : '';
$extra_styles = '.card-shadow { box-shadow: 0 4px 20px rgba(4, 22, 39, 0.05); }';
$extra_head = '<script src="' . escape_html(asset_url('assets/js/qrcode.min.js')) . '"></script>';

include __DIR__ . '/includes/layout.php';
?>

<section class="flex flex-wrap items-center justify-between gap-4 mb-6">
<div>
<?php
$receiveBackHref = $coinKeyParam !== ''
    ? 'asset-detail.php?coin_key=' . rawurlencode($coinKeyParam) . ($trustIdParam > 0 ? '&trust_id=' . $trustIdParam : '')
    : 'assets.php';
$receiveBackLabel = $coinKeyParam !== '' ? 'Back to Asset' : 'Back to Assets';
?>
<a href="<?php echo escape_html($receiveBackHref); ?>" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-3">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> <?php echo escape_html($receiveBackLabel); ?>
</a>
<h1 class="font-headline-lg text-headline-lg text-primary mb-1">Deposit Cryptocurrency</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Send crypto to your trust deposit address</p>
</div>
</section>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant card-shadow p-6 sm:p-8">
<div id="assetSelector" class="flex items-center gap-3 p-4 border border-outline-variant rounded-xl cursor-pointer hover:bg-surface-container-low mb-6">
<img id="selectedAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<span id="selectedAssetName" class="font-bold text-on-surface">Select Asset</span>
<?php echo wt_icon('chevron-down', 'ml-auto text-on-surface-variant'); ?>
</div>
<div id="lockedAssetDisplay" class="hidden flex items-center gap-3 p-4 border border-outline-variant rounded-xl bg-surface-container-low mb-6">
<img id="lockedAssetLogo" src="" alt="" class="w-10 h-10 rounded-full hidden">
<div>
<p id="lockedAssetName" class="font-bold text-on-surface">--</p>
<p id="lockedAssetSymbol" class="text-xs text-on-surface-variant">--</p>
</div>
</div>

<div id="depositLoadingPanel" class="text-center py-12">
<div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent mb-4"></div>
<p class="text-on-surface-variant text-sm">Loading deposit details...</p>
</div>

<div id="depositUnavailablePanel" class="hidden text-center py-10 px-4">
<div class="w-16 h-16 mx-auto mb-4 rounded-full bg-error-container/30 flex items-center justify-center">
<?php echo wt_icon('warning', 'w-8 h-8 text-error'); ?>
</div>
<h2 class="font-headline-md text-headline-md text-primary mb-2">Deposit Not Available</h2>
<p class="text-on-surface-variant text-sm max-w-md mx-auto mb-2">
Deposits for <strong id="unavailableCoinName" class="text-primary">this coin</strong> are not available at this moment.
</p>
<p class="text-on-surface-variant text-xs max-w-md mx-auto">
An administrator has not yet configured a deposit wallet address for this asset. Please check back later or contact support.
</p>
<a href="<?php echo escape_html($receiveBackHref); ?>" class="inline-flex items-center gap-2 mt-6 text-secondary font-label-md font-bold hover:underline">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> <?php echo escape_html($receiveBackLabel); ?>
</a>
</div>

<div id="depositAvailablePanel" class="hidden">
<div id="depositAddressSection">
<div id="depositAmountSection">
<div class="mb-6 p-4 sm:p-5 bg-surface-container-low rounded-xl border border-outline-variant">
<div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-outline-variant">
<div class="min-w-0 flex-1">
<p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Live Price</p>
<div class="dashboard-metric-value-wrap max-w-xl">
<p id="depositLivePrice" class="dashboard-metric-value text-primary" data-fit-max="28" data-fit-max-mobile="18" data-fit-min="12">Loading...</p>
</div>
</div>
<p id="depositPriceChange" class="text-sm font-medium text-on-surface-variant shrink-0">--</p>
</div>
<div>
<label for="depositUsdAmount" id="depositAmountLabel" class="block text-sm font-semibold text-primary mb-2">Amount to Deposit (USD)</label>
<div class="relative max-w-xl dashboard-metric-value-wrap">
<input type="number" id="depositUsdAmount" min="0" step="0.01" placeholder="0.00" class="w-full px-4 py-3 pr-16 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary"/>
<span id="depositCurrencySuffix" class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-on-surface-variant pointer-events-none">USD</span>
</div>
<div class="dashboard-metric-value-wrap">
<p id="depositCoinQuote" class="dashboard-metric-value text-secondary mt-3 min-h-[1.5em]" data-fit-max="22" data-fit-min="11">—</p>
</div>
<p id="depositRateHint" class="text-xs text-on-surface-variant mt-1">Enter how much you want to deposit in US dollars. We will calculate the crypto amount to send.</p>
</div>
</div>
<div class="mb-6 text-center">
<button type="button" id="continueToDepositBtn" onclick="continueToDeposit()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
Continue to Deposit <?php echo wt_icon('arrow-forward', 'w-5 h-5'); ?>
</button>
</div>
<div class="bg-surface-container-low border border-outline-variant rounded-2xl p-4 sm:p-6">
<div class="flex items-start gap-3">
<?php echo wt_icon('info', 'text-secondary flex-shrink-0'); ?>
<div class="text-sm text-on-surface">
<p class="font-semibold mb-2">Security Information:</p>
<ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-on-surface-variant">
<li>Only send the selected cryptocurrency to the address shown</li>
<li>Deposit addresses are configured by WyomingTrust administrators</li>
<li>Transactions sent to the wrong network or coin may be unrecoverable</li>
<li>We do not store your private keys or seed phrases</li>
</ul>
</div>
</div>
</div>
</div>

<div id="depositQrSection" class="hidden">
<div class="mb-4 flex items-center justify-between gap-3">
<button type="button" onclick="backToAmountStep()" class="inline-flex items-center gap-1 text-secondary text-sm font-semibold hover:underline">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Edit amount
</button>
<p id="depositQrAmountSummary" class="text-sm text-on-surface-variant text-right"></p>
</div>
<div class="text-center p-6 sm:p-8 bg-surface-container-low rounded-xl">
<div id="qrCode" class="inline-flex items-center justify-center p-4 bg-surface-container-lowest rounded-xl mb-4 border border-outline-variant w-56 h-56 mx-auto" aria-label="Deposit address QR code"></div>
<p class="text-xs text-on-surface-variant mb-4">Scan this QR code to deposit crypto</p>
<div class="flex items-center gap-2 p-3 bg-surface-container-lowest rounded-xl border border-outline-variant max-w-xl mx-auto">
<input type="text" id="receiveAddress" readonly class="flex-1 bg-transparent text-xs sm:text-sm font-mono break-all text-on-surface min-w-0">
<button type="button" onclick="copyAddress()" class="shrink-0 px-4 py-2 bg-primary text-on-primary rounded-lg hover:bg-primary/90 text-xs font-semibold inline-flex items-center gap-1">
<?php echo wt_icon('share', 'w-4 h-4'); ?> Copy
</button>
</div>
<p class="text-xs text-on-surface-variant mt-3">Send only <span id="selectedAssetSymbol" class="font-semibold text-primary">--</span> to this address</p>
</div>
<div class="mt-8 text-center">
<button type="button" id="madePaymentBtn" onclick="showConfirmPaymentForm()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
<?php echo wt_icon('check-circle', 'w-5 h-5'); ?> Confirm Deposit
</button>
</div>
</div>
</div>

<div id="depositConfirmSection" class="hidden mt-2">
<h2 class="font-headline-md text-headline-md text-primary mb-2">Confirm Your Deposit</h2>
<p class="text-sm text-on-surface-variant mb-6">Enter your transaction details to complete your deposit submission.</p>
<form id="depositConfirmForm" class="space-y-5 max-w-xl">
<input type="hidden" id="depositAddressHidden" value="">
<input type="hidden" id="depositAmount" name="amount" value="">
<div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant">
<p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">You Are Depositing</p>
<p id="depositConfirmUsdDisplay" class="font-bold text-primary text-lg">--</p>
<p id="depositConfirmAmountDisplay" class="text-sm text-on-surface-variant mt-1">--</p>
</div>
<div>
<label for="depositTxHash" class="block text-sm font-semibold text-primary mb-2">Transaction Hash (TX ID)</label>
<input type="text" id="depositTxHash" name="tx_hash" required placeholder="Paste your transaction hash" class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface font-mono text-sm focus:outline-none focus:ring-2 focus:ring-secondary"/>
</div>
<div>
<label for="depositProof" class="block text-sm font-semibold text-primary mb-2">Payment Proof <span class="font-normal text-on-surface-variant">(optional)</span></label>
<input type="file" id="depositProof" name="proof" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-secondary file:text-on-secondary file:font-semibold hover:file:opacity-90"/>
<p class="text-xs text-on-surface-variant mt-1">Upload a screenshot or receipt (PDF, JPG, PNG — max 10MB)</p>
</div>
<div class="flex flex-col sm:flex-row gap-3">
<button type="button" onclick="hideConfirmPaymentForm()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border border-outline-variant text-primary px-6 py-3 rounded-xl font-label-md font-bold hover:bg-surface-container-low transition-colors">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back
</button>
<button type="submit" id="depositSubmitBtn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
<?php echo wt_icon('check-circle', 'w-5 h-5'); ?> Complete Transaction
</button>
</div>
</form>
</div>

<div id="depositPendingNotice" class="hidden mt-8 p-4 rounded-xl border border-secondary/30 bg-secondary/10 text-sm text-on-surface text-center">
<?php echo wt_icon('info', 'inline w-4 h-4 text-secondary mr-1'); ?>
<strong>Deposit pending review.</strong> You already submitted a payment for this asset. An administrator will verify it shortly.
<a href="dashboard.php" class="block mt-3 text-secondary font-semibold hover:underline">Back to Dashboard</a>
</div>

<div id="depositSuccessPanel" class="hidden text-center py-10 sm:py-14 px-4">
<div class="w-16 h-16 mx-auto mb-5 rounded-full bg-deep-forest/10 flex items-center justify-center">
<?php echo wt_icon('check-circle', 'w-9 h-9 text-deep-forest'); ?>
</div>
<h2 class="font-headline-md text-headline-md text-primary mb-3">Deposit Submitted Successfully</h2>
<p class="text-sm sm:text-base text-on-surface-variant max-w-md mx-auto mb-2">
Your crypto deposit is <strong class="text-primary">pending review</strong>. An administrator will verify your transaction shortly.
</p>
<p class="text-sm text-on-surface-variant max-w-md mx-auto mb-8">
Once confirmed, your funds will be credited to your account within <strong class="text-primary">24 hours</strong>.
</p>
<button type="button" onclick="window.location.href='dashboard.php'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-10 py-4 rounded-xl font-label-md font-bold hover:bg-primary/90 transition-colors">
Done
</button>
</div>
</div>
</div>

<div id="assetModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
<div class="bg-surface-container-lowest rounded-2xl max-w-md w-full max-h-[80vh] overflow-y-auto border border-outline-variant">
<div class="p-6 border-b border-outline-variant flex items-center justify-between">
<h3 class="font-bold text-lg text-primary">Select Asset</h3>
<button type="button" onclick="closeAssetModal()" class="text-on-surface-variant hover:text-on-surface">
<?php echo wt_icon('close', 'w-5 h-5'); ?>
</button>
</div>
<div id="assetList" class="p-4"></div>
</div>
</div>

<?php include __DIR__ . '/includes/modal.php'; ?>

<script>
let userAssets = [];
let selectedAsset = null;
let adminAddresses = {};
let hasPendingDeposit = false;
let confirmFormOpen = false;
let depositQrStepOpen = false;
let currentCoinPrice = 0;
let priceRefreshTimer = null;

const COINGECKO_API = '../../api/coingecko.php';
const STABLECOIN_KEYS = new Set(['tether', 'usd-coin', 'usdt', 'usdc', 'busd', 'dai', 'true-usd']);

function isStablecoin(coinKey) {
    if (!coinKey) return false;
    const key = coinKey.toLowerCase();
    if (STABLECOIN_KEYS.has(key)) return true;
    const sym = (selectedAsset?.symbol || '').toUpperCase();
    return ['USDT', 'USDC', 'BUSD', 'DAI', 'USD'].includes(sym);
}

function formatUsd(value) {
    return '$' + (value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatCoinAmount(amount) {
    if (amount >= 1) return amount.toFixed(4);
    if (amount >= 0.01) return amount.toFixed(6);
    return amount.toFixed(8);
}

function formatPriceDisplay(price) {
    if (!price || price <= 0) return '$0.00';
    const maxFrac = price >= 1000 ? 2 : (price >= 1 ? 4 : 6);
    return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: maxFrac });
}

function updateDepositAmountLabels() {
    const symbol = selectedAsset?.symbol || selectedAsset?.coin_key || '--';
    const rateHint = document.getElementById('depositRateHint');
    if (rateHint && currentCoinPrice > 0) {
        rateHint.textContent = `Rate: ${formatPriceDisplay(currentCoinPrice)} per ${symbol}. Amount updates as the market price changes.`;
    } else if (rateHint) {
        rateHint.textContent = 'Enter how much you want to deposit in US dollars. We will calculate the crypto amount to send.';
    }
}

function getUsdDepositAmount() {
    return parseFloat(document.getElementById('depositUsdAmount')?.value) || 0;
}

function getCoinAmountFromUsd(usdAmount) {
    if (!usdAmount || usdAmount <= 0 || !currentCoinPrice || currentCoinPrice <= 0) return 0;
    return usdAmount / currentCoinPrice;
}

function updateDepositCoinQuote() {
    const quoteEl = document.getElementById('depositCoinQuote');
    if (!quoteEl) return;
    const usdAmount = getUsdDepositAmount();
    const symbol = selectedAsset?.symbol || selectedAsset?.coin_key || '';

    if (usdAmount <= 0) {
        quoteEl.textContent = '—';
        return;
    }
    if (currentCoinPrice <= 0) {
        quoteEl.textContent = 'Unable to calculate — price unavailable';
        return;
    }

    const coinAmt = getCoinAmountFromUsd(usdAmount);
    const disp = formatCoinAmount(coinAmt);
    quoteEl.textContent = `You will send: ${disp} ${symbol}`;
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function updatePriceDisplay(price, change24h) {
    currentCoinPrice = price || 0;
    const priceEl = document.getElementById('depositLivePrice');
    const changeEl = document.getElementById('depositPriceChange');
    const symbol = selectedAsset?.symbol || selectedAsset?.coin_key || 'coin';

    if (priceEl) {
        priceEl.textContent = currentCoinPrice > 0
            ? `1 ${symbol} = ${formatPriceDisplay(currentCoinPrice)}`
            : 'Price unavailable';
    }
    if (changeEl) {
        if (change24h != null && !Number.isNaN(change24h)) {
            const sign = change24h >= 0 ? '+' : '';
            changeEl.textContent = `${sign}${change24h.toFixed(2)}% (24h)`;
            changeEl.className = `text-sm font-medium shrink-0 ${change24h >= 0 ? 'text-deep-forest' : 'text-error'}`;
        } else {
            changeEl.textContent = '--';
            changeEl.className = 'text-sm font-medium text-on-surface-variant shrink-0';
        }
    }
    updateDepositAmountLabels();
    updateDepositCoinQuote();
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function getCachedCoinPrice(coinKey) {
    try {
        const cached = sessionStorage.getItem('crypto_prices_cache');
        if (!cached) return null;
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp < 120000 && data && data[coinKey]) return data[coinKey];
        return null;
    } catch (e) {
        return null;
    }
}

function cacheCoinPrice(coinKey, priceData) {
    try {
        const cached = sessionStorage.getItem('crypto_prices_cache');
        const existing = cached ? JSON.parse(cached) : { data: {}, timestamp: Date.now() };
        existing.data[coinKey] = priceData;
        existing.timestamp = Date.now();
        sessionStorage.setItem('crypto_prices_cache', JSON.stringify(existing));
    } catch (e) { /* ignore */ }
}

async function fetchDepositCoinPrice() {
    if (!selectedAsset?.coin_key) return;

    const coinKey = selectedAsset.coin_key;
    if (isStablecoin(coinKey)) {
        updatePriceDisplay(1, 0);
        return;
    }

    if (selectedAsset.price_usd) {
        updatePriceDisplay(selectedAsset.price_usd, selectedAsset.price_change_24h || 0);
    }

    const cached = getCachedCoinPrice(coinKey);
    if (cached?.usd) {
        updatePriceDisplay(cached.usd, cached.usd_24h_change || 0);
    }

    try {
        const response = await fetch(
            `${COINGECKO_API}?path=/simple/price&ids=${encodeURIComponent(coinKey)}&vs_currencies=usd&include_24hr_change=true`,
            { credentials: 'same-origin' }
        );
        if (response.ok) {
            const data = await response.json();
            if (data && data[coinKey]) {
                const assetData = data[coinKey];
                cacheCoinPrice(coinKey, assetData);
                updatePriceDisplay(assetData.usd || 0, assetData.usd_24h_change || 0);
                return;
            }
        }
    } catch (error) {
        console.error('Error fetching deposit coin price:', error);
    }

    if (!currentCoinPrice && selectedAsset.price_usd) {
        updatePriceDisplay(selectedAsset.price_usd, selectedAsset.price_change_24h || 0);
    } else if (!currentCoinPrice) {
        updatePriceDisplay(0, null);
    }
}

function startPriceRefresh() {
    if (priceRefreshTimer) clearInterval(priceRefreshTimer);
    fetchDepositCoinPrice();
    priceRefreshTimer = setInterval(fetchDepositCoinPrice, 120000);
}

function stopPriceRefresh() {
    if (priceRefreshTimer) {
        clearInterval(priceRefreshTimer);
        priceRefreshTimer = null;
    }
}

function syncConfirmAmountDisplay() {
    const usdAmount = getUsdDepositAmount();
    const coinAmount = getCoinAmountFromUsd(usdAmount);
    const symbol = selectedAsset?.symbol || selectedAsset?.coin_key || '';
    const amountDisplay = document.getElementById('depositConfirmAmountDisplay');
    const usdDisplay = document.getElementById('depositConfirmUsdDisplay');
    const hiddenAmount = document.getElementById('depositAmount');

    if (hiddenAmount) hiddenAmount.value = coinAmount > 0 ? String(coinAmount) : '';
    if (usdDisplay) {
        usdDisplay.textContent = usdAmount > 0 ? formatUsd(usdAmount) : '--';
    }
    if (amountDisplay) {
        amountDisplay.textContent = coinAmount > 0
            ? `Send ${formatCoinAmount(coinAmount)} ${symbol}`
            : '--';
    }
}

function setDepositStep(step) {
    // step: 'amount' | 'qr' | 'confirm' | 'success'
    const amountEl = document.getElementById('depositAmountSection');
    const qrEl = document.getElementById('depositQrSection');
    const confirmEl = document.getElementById('depositConfirmSection');
    const addressWrap = document.getElementById('depositAddressSection');

    depositQrStepOpen = step === 'qr';
    confirmFormOpen = step === 'confirm';

    if (addressWrap) addressWrap.classList.toggle('hidden', step === 'confirm' || step === 'success');
    if (amountEl) amountEl.classList.toggle('hidden', step !== 'amount');
    if (qrEl) qrEl.classList.toggle('hidden', step !== 'qr');
    if (confirmEl) confirmEl.classList.toggle('hidden', step !== 'confirm');
}

function continueToDeposit() {
    if (hasPendingDeposit || !selectedAsset) return;
    const usdAmount = getUsdDepositAmount();
    if (!usdAmount || usdAmount <= 0) {
        showAlertModal('Amount Required', 'Please enter the USD amount you want to deposit before continuing.', 'error');
        document.getElementById('depositUsdAmount')?.focus();
        return;
    }
    if (!currentCoinPrice || currentCoinPrice <= 0) {
        showAlertModal('Price Unavailable', 'Unable to calculate the crypto amount right now. Please wait for the live price to load and try again.', 'error');
        return;
    }

    const address = (adminAddresses[selectedAsset.coin_key] || document.getElementById('receiveAddress')?.value || '').trim();
    if (!address) {
        showAlertModal('Address Unavailable', 'No deposit address is configured for this asset yet.', 'error');
        return;
    }

    document.getElementById('receiveAddress').value = address;
    const coinAmount = getCoinAmountFromUsd(usdAmount);
    const symbol = selectedAsset?.symbol || selectedAsset?.coin_key || '';
    const summary = document.getElementById('depositQrAmountSummary');
    if (summary) {
        summary.textContent = `${formatUsd(usdAmount)} ≈ ${formatCoinAmount(coinAmount)} ${symbol}`;
    }
    generateQRCode(address);
    setDepositStep('qr');
    try {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) { /* ignore */ }
}

function backToAmountStep() {
    setDepositStep('amount');
}

function showConfirmPaymentForm() {
    if (hasPendingDeposit || !selectedAsset) return;
    const usdAmount = getUsdDepositAmount();
    if (!usdAmount || usdAmount <= 0) {
        showAlertModal('Amount Required', 'Please enter the USD amount you want to deposit before continuing.', 'error');
        backToAmountStep();
        document.getElementById('depositUsdAmount')?.focus();
        return;
    }
    if (!currentCoinPrice || currentCoinPrice <= 0) {
        showAlertModal('Price Unavailable', 'Unable to calculate the crypto amount right now. Please wait for the live price to load and try again.', 'error');
        return;
    }
    const address = document.getElementById('receiveAddress')?.value?.trim() || '';
    const hidden = document.getElementById('depositAddressHidden');
    if (hidden) hidden.value = address;
    syncConfirmAmountDisplay();
    setDepositStep('confirm');
    document.getElementById('depositTxHash')?.focus();
}

function showDepositSuccessPanel() {
    stopPriceRefresh();
    setDepositStep('success');
    document.getElementById('depositPendingNotice')?.classList.add('hidden');
    document.getElementById('depositSuccessPanel')?.classList.remove('hidden');
}

function hideConfirmPaymentForm() {
    setDepositStep('qr');
}

function resetDepositView() {
    document.getElementById('depositSuccessPanel')?.classList.add('hidden');
    setDepositStep('amount');
}

const urlParams = new URLSearchParams(window.location.search);
const urlCoinKey = urlParams.get('coin_key');
const urlTrustId = <?php echo $trustIdParam; ?>;
const lockToSingleCoin = !!urlCoinKey;

function showPanel(panelId) {
    ['depositLoadingPanel', 'depositUnavailablePanel', 'depositAvailablePanel'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', id !== panelId);
    });
}

async function loadCoinFromCatalog(coinKey) {
    const response = await fetch('../../api/coins.php');
    const data = await response.json();
    if (!data.success || !data.coins) return null;
    return data.coins.find(c => c.coin_key === coinKey) || null;
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

async function loadAdminAddresses() {
    try {
        const response = await fetch('../../api/addresses.php');
        const data = await response.json();
        if (data.success && data.addressMap) {
            adminAddresses = data.addressMap;
        }
    } catch (error) {
        console.error('Error loading admin addresses:', error);
    }
}

async function loadAssets() {
    showPanel('depositLoadingPanel');
    await loadAdminAddresses();

    try {
        const response = await fetch('../../api/user/assets.php');
        const data = await response.json();
        if (!data.success || !data.assets) {
            showPanel('depositUnavailablePanel');
            return;
        }

        userAssets = data.assets;

        if (lockToSingleCoin) {
            let asset = userAssets.find(a => a.coin_key === urlCoinKey);
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
            document.getElementById('assetSelector').classList.add('hidden');
            document.getElementById('lockedAssetDisplay').classList.remove('hidden');
            if (asset) {
                selectedAsset = asset;
                updateLockedAssetDisplay(asset);
                updateSelectedAsset();
            } else {
                document.getElementById('lockedAssetName').textContent = 'Asset not found';
                document.getElementById('lockedAssetSymbol').textContent = urlCoinKey;
                showPanel('depositUnavailablePanel');
                document.getElementById('unavailableCoinName').textContent = urlCoinKey;
            }
            return;
        }

        if (urlCoinKey) {
            const asset = userAssets.find(a => a.coin_key === urlCoinKey);
            if (asset) selectedAsset = asset;
        }
        if (!selectedAsset && userAssets.length > 0) {
            selectedAsset = userAssets[0];
        }
        if (selectedAsset) {
            updateSelectedAsset();
        } else {
            showPanel('depositUnavailablePanel');
        }
        renderAssetModal();
    } catch (error) {
        console.error('Error loading assets:', error);
        showPanel('depositUnavailablePanel');
    }
}

function updateSelectedAsset() {
    if (!selectedAsset) return;
    const logoEl = document.getElementById('selectedAssetLogo');
    if (logoEl) {
        logoEl.src = selectedAsset.logo || '';
        logoEl.classList.toggle('hidden', !selectedAsset.logo);
    }
    const nameEl = document.getElementById('selectedAssetName');
    if (nameEl) nameEl.textContent = selectedAsset.display_name || selectedAsset.symbol;
    document.getElementById('selectedAssetSymbol').textContent = selectedAsset.symbol || selectedAsset.coin_key || '';
    const coinInput = document.getElementById('depositUsdAmount');
    if (coinInput) coinInput.value = '';
    updateDepositAmountLabels();
    updateDepositCoinQuote();
    renderDepositState();
}

function renderDepositState() {
    if (!selectedAsset) {
        stopPriceRefresh();
        showPanel('depositUnavailablePanel');
        return;
    }

    const address = (adminAddresses[selectedAsset.coin_key] || '').trim();
    if (!address) {
        stopPriceRefresh();
        document.getElementById('unavailableCoinName').textContent =
            selectedAsset.display_name || selectedAsset.symbol || selectedAsset.coin_key;
        showPanel('depositUnavailablePanel');
        return;
    }

    showPanel('depositAvailablePanel');
    resetDepositView();
    document.getElementById('receiveAddress').value = address;
    updateDepositAmountLabels();
    startPriceRefresh();
    checkPendingDeposit();
}

async function checkPendingDeposit() {
    const notice = document.getElementById('depositPendingNotice');
    const addressSection = document.getElementById('depositAddressSection');
    const formSection = document.getElementById('depositConfirmSection');
    const continueBtn = document.getElementById('continueToDepositBtn');
    const madeBtn = document.getElementById('madePaymentBtn');
    if (!selectedAsset) return;

    try {
        const params = new URLSearchParams({ coin_key: selectedAsset.coin_key });
        if (urlTrustId > 0) params.set('trust_id', String(urlTrustId));
        const res = await fetch(`../../api/user/deposit-submissions.php?${params.toString()}`, { credentials: 'same-origin' });
        const data = await res.json();
        const pending = data.success && Array.isArray(data.submissions)
            ? data.submissions.find(s => s.status === 'pending')
            : null;
        hasPendingDeposit = !!pending;
        if (notice) notice.classList.toggle('hidden', !hasPendingDeposit);
        if (hasPendingDeposit) {
            if (addressSection) addressSection.classList.add('hidden');
            if (formSection) formSection.classList.add('hidden');
            document.getElementById('depositSuccessPanel')?.classList.add('hidden');
        } else if (!confirmFormOpen && !depositQrStepOpen) {
            setDepositStep('amount');
        }
        if (continueBtn) continueBtn.disabled = hasPendingDeposit;
        if (madeBtn) madeBtn.disabled = hasPendingDeposit;
    } catch (e) {
        console.error('Error checking pending deposit:', e);
    }
}

function closeModal() {
    const modal = document.getElementById('customModal');
    if (modal) modal.classList.add('hidden');
}

function showAlertModal(title, message, type = 'info') {
    return new Promise((resolve) => {
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        document.getElementById('modalInput').classList.add('hidden');
        cancelBtn.classList.add('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = 'OK';
        if (type === 'success') {
            setModalIcon('check-circle', 'text-deep-forest text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-deep-forest/10 sm:mx-0 sm:h-10 sm:w-10';
        } else if (type === 'error') {
            setModalIcon('error', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
        } else {
            setModalIcon('info', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
        }
        confirmBtn.onclick = () => { closeModal(); resolve(true); };
        modal.classList.remove('hidden');
    });
}

async function submitDepositConfirmation(event) {
    event.preventDefault();
    if (!selectedAsset || hasPendingDeposit) return;

    syncConfirmAmountDisplay();
    const amount = parseFloat(document.getElementById('depositAmount').value)
        || getCoinAmountFromUsd(getUsdDepositAmount());
    const txHash = document.getElementById('depositTxHash').value.trim();
    const proofFile = document.getElementById('depositProof').files[0];
    const address = document.getElementById('depositAddressHidden').value.trim()
        || document.getElementById('receiveAddress').value.trim();

    if (!amount || amount <= 0) {
        await showAlertModal('Invalid Amount', 'Please enter the amount you deposited.', 'error');
        return;
    }
    if (!txHash) {
        await showAlertModal('Transaction Hash Required', 'Please enter your transaction hash (TX ID).', 'error');
        return;
    }

    const submitBtn = document.getElementById('depositSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    try {
        const sessionRes = await fetch('../../api/session.php', { credentials: 'same-origin' });
        const sessionData = await sessionRes.json();
        const csrfToken = sessionData.csrf_token || '';

        const formData = new FormData();
        formData.append('coin_key', selectedAsset.coin_key);
        formData.append('amount', String(amount));
        formData.append('amount_usd', String(getUsdDepositAmount()));
        formData.append('tx_hash', txHash);
        formData.append('deposit_address', address);
        formData.append('csrf_token', csrfToken);
        if (urlTrustId > 0) formData.append('trust_id', String(urlTrustId));
        if (proofFile) formData.append('proof', proofFile);

        const res = await fetch('../../api/user/deposit-submissions.php', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            credentials: 'same-origin',
            body: formData,
        });
        const data = await res.json();

        if (data.success) {
            showDepositSuccessPanel();
            return;
        }
        await showAlertModal('Submission Failed', data.message || 'Could not submit deposit.', 'error');
    } catch (error) {
        console.error('Deposit submission error:', error);
        await showAlertModal('Error', 'An error occurred while submitting your deposit.', 'error');
    } finally {
        if (submitBtn && !hasPendingDeposit) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Complete Transaction';
        }
    }
}

function drawQRPlaceholder(host, line1, line2) {
    const canvas = document.createElement('canvas');
    canvas.width = 200;
    canvas.height = 200;
    canvas.className = 'w-48 h-48';
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, 200, 200);
    ctx.fillStyle = '#041627';
    ctx.font = '13px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(line1, 100, 90);
    if (line2) ctx.fillText(line2, 100, 110);
    host.appendChild(canvas);
}

function generateQRCode(address) {
    const host = document.getElementById('qrCode');
    if (!host || !address) return;
    host.innerHTML = '';

    // davidshimjs qrcodejs: new QRCode(el, options) — local asset (CDN node-qrcode build 404'd)
    if (typeof QRCode === 'function') {
        try {
            const mount = document.createElement('div');
            mount.className = 'w-48 h-48';
            host.appendChild(mount);
            new QRCode(mount, {
                text: String(address),
                width: 192,
                height: 192,
                colorDark: '#041627',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.M
            });
            return;
        } catch (err) {
            console.error('Error generating QR code:', err);
            host.innerHTML = '';
        }
    }

    // Offline-friendly fallback image API (img-src https: allowed)
    const img = document.createElement('img');
    img.alt = 'Deposit address QR code';
    img.width = 192;
    img.height = 192;
    img.className = 'w-48 h-48';
    img.decoding = 'async';
    img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=192x192&margin=8&data=' + encodeURIComponent(address);
    img.onerror = function () {
        host.innerHTML = '';
        drawQRPlaceholder(host, 'QR code unavailable', 'copy address instead');
    };
    host.appendChild(img);
}

function renderAssetModal() {
    const list = document.getElementById('assetList');
    if (!list) return;
    list.innerHTML = userAssets.map(asset => `
        <div onclick="selectAsset('${asset.coin_key}')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low cursor-pointer">
            <img src="${asset.logo || ''}" alt="${asset.display_name}" class="w-10 h-10 rounded-full" onerror="this.style.display='none'">
            <div class="flex-1">
                <p class="font-semibold text-on-surface">${asset.display_name}</p>
                <p class="text-xs text-on-surface-variant">${asset.symbol}</p>
            </div>
        </div>
    `).join('');
}

document.getElementById('assetSelector')?.addEventListener('click', () => {
    if (lockToSingleCoin) return;
    const modal = document.getElementById('assetModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
});

function selectAsset(coinKey) {
    selectedAsset = userAssets.find(a => a.coin_key === coinKey);
    if (selectedAsset) {
        updateSelectedAsset();
        closeAssetModal();
    }
}

function closeAssetModal() {
    const modal = document.getElementById('assetModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function copyAddress() {
    const address = document.getElementById('receiveAddress').value;
    if (!address) return;
    try {
        await navigator.clipboard.writeText(address);
        alert('Address copied to clipboard!');
    } catch (err) {
        alert('Unable to copy address. Please copy manually.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadAssets();
    document.getElementById('depositConfirmForm')?.addEventListener('submit', submitDepositConfirmation);
    const usdInput = document.getElementById('depositUsdAmount');
    const onUsdChange = () => {
        updateDepositCoinQuote();
        syncConfirmAmountDisplay();
    };
    usdInput?.addEventListener('input', onUsdChange);
    usdInput?.addEventListener('change', onUsdChange);
});
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
