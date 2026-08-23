<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$trustIdParam = isset($_GET['trust_id']) ? (int) $_GET['trust_id'] : 0;
$page_title = 'Asset Details | WyomingTrust';
$active_nav = 'crypto-assets';
$premium_bg = true;
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
$extra_styles = '.card-shadow { box-shadow: 0 4px 20px rgba(4, 22, 39, 0.05); }
.asset-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    flex: 1 1 0;
    min-width: 0;
    padding: 0.65rem 0.4rem;
    border-radius: 0.5rem;
    text-align: center;
    font-weight: 700;
    font-size: 0.6875rem;
    line-height: 1.15;
    transition: opacity 0.15s ease, background-color 0.15s ease;
}
.asset-action-btn .wt-icon,
.asset-action-btn svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}
.asset-action-btn span {
    display: block;
    width: 100%;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
@media (min-width: 640px) {
    .asset-action-btn {
        padding: 0.85rem 1rem;
        font-size: 0.8125rem;
        gap: 0.45rem;
        max-width: 8.5rem;
    }
    .asset-action-btn .wt-icon,
    .asset-action-btn svg {
        width: 1.5rem;
        height: 1.5rem;
    }
}';

include __DIR__ . '/includes/layout.php';
?>

<section class="flex flex-col gap-4 mb-6 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
<div class="min-w-0">
<a href="assets.php" class="inline-flex items-center gap-1 text-secondary font-label-md text-label-md hover:underline mb-3">
<?php echo wt_icon('arrow-back', 'w-4 h-4'); ?> Back to Assets
</a>
<h1 class="font-headline-lg text-headline-lg text-primary mb-1" id="coinSymbol">Loading...</h1>
<p class="font-body-md text-body-md text-on-surface-variant" id="coinName">Loading...</p>
</div>
<div class="flex flex-row items-stretch gap-2 sm:gap-3 w-full sm:w-auto no-print">
<button type="button" id="depositBtn" class="asset-action-btn bg-secondary text-on-secondary hover:opacity-90">
<?php echo wt_icon('receive', 'w-5 h-5'); ?>
<span>Receive</span>
</button>
<a id="swapBtn" href="swap.php" class="asset-action-btn bg-primary text-on-primary hover:opacity-90">
<?php echo wt_icon('swap', 'w-5 h-5'); ?>
<span>Swap</span>
</a>
<a id="linkWalletBtn" href="link-wallet.php" class="asset-action-btn bg-surface-container-lowest border border-outline-variant text-primary hover:bg-surface-container">
<?php echo wt_icon('wallet', 'w-5 h-5'); ?>
<span>Link Wallet</span>
</a>
<div id="liquidationActionSection" class="hidden contents">
<button type="button" id="liquidateBtn" class="asset-action-btn bg-primary text-on-primary hover:opacity-90">
<?php echo wt_icon('send', 'w-5 h-5'); ?>
<span>Liquidate</span>
</button>
</div>
</div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
<div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant card-shadow flex flex-col justify-center min-w-0 dashboard-metric-card">
<p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3">Live Price</p>
<div class="text-left min-w-0">
<div class="dashboard-metric-value-wrap">
<div class="dashboard-metric-value text-primary mb-2" id="currentPrice" data-fit-max="36" data-fit-max-mobile="22" data-fit-min="14">$0.00</div>
</div>
<div class="text-lg font-medium mb-1" id="priceChange">--</div>
<div class="text-sm text-on-surface-variant" id="marketCap"></div>
</div>
</div>
<div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant card-shadow flex flex-col justify-center min-w-0 dashboard-metric-card">
<p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3">My Balance</p>
<div class="text-left min-w-0">
<div class="flex items-center justify-start gap-3 mb-4">
<img src="" alt="Crypto Logo" class="w-12 h-12 rounded-full shrink-0" id="balanceLogo" onerror="this.style.display='none'">
<div class="text-xl font-bold text-primary" id="balanceSymbol">--</div>
</div>
<div class="dashboard-metric-value-wrap">
<div class="dashboard-metric-value text-primary mb-2" id="balanceAmount" data-fit-max="30" data-fit-max-mobile="20" data-fit-min="12">0.000</div>
</div>
<div class="dashboard-metric-value-wrap">
<div class="dashboard-metric-value text-on-surface-variant" id="balanceUSD" data-fit-max="22" data-fit-max-mobile="16" data-fit-min="11">USD $0.00</div>
</div>
</div>
</div>
</section>

<section>
<div class="flex gap-2 mb-4 overflow-x-auto pb-2">
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container" data-days="1">1H</button>
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary active" data-days="1">1D</button>
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container" data-days="7">1W</button>
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container" data-days="30">1M</button>
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container" data-days="365">1Y</button>
<button type="button" class="time-filter px-4 py-2 rounded-lg text-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container" data-days="max">All</button>
</div>
<div class="bg-surface-container-lowest rounded-2xl p-4 border border-outline-variant mb-4 card-shadow" style="height: 300px;">
<canvas id="priceChart"></canvas>
</div>
</section>

<section>
<div class="flex gap-2 mb-4 border-b border-outline-variant">
<button type="button" class="coin-tab px-4 py-2 text-sm font-medium border-b-2 border-primary text-primary" data-tab="holdings">Holdings</button>
<button type="button" class="coin-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-on-surface-variant hover:text-on-surface" data-tab="history">History</button>
<button type="button" class="coin-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-on-surface-variant hover:text-on-surface" data-tab="about">About</button>
</div>
<div id="tabContent">
<div id="holdingsTab" class="tab-content">
<div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant card-shadow">
<p class="text-on-surface-variant text-center text-sm">Your balance and live price are shown above. Use Receive to add funds, Swap to exchange, or Link Wallet to connect an external wallet.</p>
</div>
</div>
<div id="historyTab" class="tab-content hidden">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
<div id="transactionHistory" class="p-4">
<p class="text-on-surface-variant text-center">Loading transaction history...</p>
</div>
</div>
</div>
<div id="aboutTab" class="tab-content hidden pb-20">
<div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant">
<div id="coinAbout" class="text-on-surface-variant"><p>Loading coin information...</p></div>
</div>
</div>
</div>
</section>

<?php include __DIR__ . '/includes/modal.php'; ?>

<script>
let priceChart = null;
let currentDays = '1';
let currentAsset = null;
let assetBalance = 0;
let currentPrice = 0;

const urlParams = new URLSearchParams(window.location.search);
const coinKey = urlParams.get('coin_key') || 'bitcoin';
const trustId = <?php echo $trustIdParam; ?>;

(function initActionHrefs() {
    const link = document.getElementById('linkWalletBtn');
    if (link) {
        const params = new URLSearchParams({ coin_key: coinKey });
        if (trustId > 0) params.set('trust_id', String(trustId));
        link.href = `link-wallet.php?${params.toString()}`;
    }
    const swap = document.getElementById('swapBtn');
    if (swap) {
        const params = new URLSearchParams({ coin_key: coinKey });
        if (trustId > 0) params.set('trust_id', String(trustId));
        swap.href = `swap.php?${params.toString()}`;
    }
})();

/** Zero → 0.000; otherwise trim trailing zeros (keep meaningful digits). */
function formatAssetBalance(amount) {
    const num = Number(amount);
    if (!Number.isFinite(num) || Math.abs(num) < 1e-12) return '0.000';
    return num.toFixed(8).replace(/\.?0+$/, '');
}

function assetHasFundedValue() {
    const balance = Number(assetBalance) || 0;
    if (balance > 0) return true;
    const usd = balance * (Number(currentPrice) || 0);
    return usd > 0;
}

function updateActionButtons() {
    const liquidationSection = document.getElementById('liquidationActionSection');
    const liquidateBtn = document.getElementById('liquidateBtn');
    const hasValue = assetHasFundedValue();
    if (liquidationSection) {
        liquidationSection.classList.toggle('hidden', !hasValue);
    }
    if (liquidateBtn) {
        liquidateBtn.classList.toggle('hidden', !hasValue);
    }
}

function closeModal() {
    const modal = document.getElementById('customModal');
    if (modal) modal.classList.add('hidden');
}

function showConfirmModal(title, message, confirmText = 'Confirm', cancelText = 'Cancel', type = 'warning') {
    return new Promise((resolve, reject) => {
        const modal = document.getElementById('customModal');
        const iconWrap = document.getElementById('modalIcon').parentElement;
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const inputDiv = document.getElementById('modalInput');
        inputDiv.classList.add('hidden');
        cancelBtn.classList.remove('hidden');
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;
        if (type === 'danger') {
            setModalIcon('warning', 'text-error text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-container sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg px-4 py-2 bg-error text-on-primary font-bold sm:ml-3 sm:w-auto sm:text-sm';
        } else {
            setModalIcon('help', 'text-secondary text-xl');
            iconWrap.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10';
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg px-4 py-2 bg-primary text-on-primary font-bold sm:ml-3 sm:w-auto sm:text-sm';
        }
        confirmBtn.onclick = () => { modal.classList.add('hidden'); resolve(true); };
        cancelBtn.onclick = () => { modal.classList.add('hidden'); reject(false); };
        modal.classList.remove('hidden');
    });
}

async function handleDeposit() {
    let url = `receive.php?coin_key=${encodeURIComponent(coinKey)}`;
    if (trustId > 0) url += `&trust_id=${trustId}`;
    window.location.href = url;
}

async function handleLiquidate() {
    try {
        const params = new URLSearchParams({ type: 'liquidation', coin_key: coinKey });
        if (trustId > 0) params.set('trust_id', String(trustId));
        const res = await fetch(`../../api/user/checkout.php?${params.toString()}`, { credentials: 'same-origin' });
        const data = await res.json();

        const sendParams = new URLSearchParams({ coin_key: coinKey, mode: 'liquidate' });
        if (trustId > 0) sendParams.set('trust_id', String(trustId));

        if (!data.success) {
            alert(data.message || 'Unable to verify liquidation fee. Please try again.');
            return;
        }

        if (!data.has_fee || data.payment_satisfied || data.fee_paid) {
            window.location.href = `send.php?${sendParams.toString()}`;
            return;
        }

        if (data.already_submitted && data.payment_status === 'pending') {
            alert('Your liquidation fee payment is pending approval. You will be able to liquidate once it is approved.');
            return;
        }

        window.location.href = `checkout.php?${params.toString()}`;
    } catch (error) {
        console.error('Liquidation fee check failed:', error);
        alert('Unable to verify liquidation fee. Please try again.');
    }
}

async function initializePage() {
    try {
        const coinsResponse = await fetch('../../api/coins.php');
        const coinsData = await coinsResponse.json();
        if (!coinsData.success || !coinsData.coins) throw new Error('Failed to load coins');

        const coin = coinsData.coins.find(c => c.coin_key === coinKey);
        if (!coin) throw new Error('Coin not found');

        currentAsset = { id: coin.coin_key, symbol: coin.symbol, name: coin.display_name, logo: coin.logo };
        document.getElementById('coinSymbol').textContent = currentAsset.symbol;
        document.getElementById('coinName').textContent = currentAsset.name;
        document.getElementById('balanceSymbol').textContent = currentAsset.symbol;
        document.getElementById('balanceLogo').src = currentAsset.logo;

        await loadUserBalance();
        await fetchAssetData();
        await fetchChartData(currentDays);
        setupEventListeners();
        loadTransactionHistory();
    } catch (error) {
        console.error('Error initializing page:', error);
        document.getElementById('coinSymbol').textContent = 'Error';
        document.getElementById('coinName').textContent = error.message;
    }
}

async function loadUserBalance() {
    try {
        const response = await fetch('../../api/user/assets.php', { credentials: 'same-origin' });
        const data = await response.json();
        if (data.success && data.assets) {
            const asset = data.assets.find(a => a.coin_key === coinKey);
            assetBalance = asset ? parseFloat(asset.balance || 0) : 0;
            updateBalanceDisplay();
        }
    } catch (error) {
        console.error('Error loading user balance:', error);
    }
}

function updateBalanceDisplay() {
    const symbol = (currentAsset && currentAsset.symbol) ? currentAsset.symbol : '';
    document.getElementById('balanceAmount').textContent = `${formatAssetBalance(assetBalance)}${symbol ? ' ' + symbol : ''}`;
    const usdValue = assetBalance * currentPrice;
    document.getElementById('balanceUSD').textContent = `USD $${usdValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    updateActionButtons();
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function setupEventListeners() {
    document.querySelectorAll('.time-filter').forEach(filter => {
        filter.addEventListener('click', function() {
            document.querySelectorAll('.time-filter').forEach(f => {
                f.classList.remove('active', 'bg-primary', 'text-on-primary');
                f.classList.add('bg-surface-container-low', 'text-on-surface-variant');
            });
            this.classList.add('active', 'bg-primary', 'text-on-primary');
            this.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
            currentDays = this.getAttribute('data-days');
            fetchChartData(currentDays);
        });
    });

    document.querySelectorAll('.coin-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            document.querySelectorAll('.coin-tab').forEach(t => {
                t.classList.remove('border-primary', 'text-primary');
                t.classList.add('border-transparent', 'text-on-surface-variant');
            });
            this.classList.add('border-primary', 'text-primary');
            this.classList.remove('border-transparent', 'text-on-surface-variant');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById(tabName + 'Tab').classList.remove('hidden');
            if (tabName === 'history') loadTransactionHistory();
            if (tabName === 'about') loadCoinAbout();
        });
    });

    document.getElementById('depositBtn').addEventListener('click', handleDeposit);
    document.getElementById('liquidateBtn').addEventListener('click', handleLiquidate);
}

async function loadCoinAbout() {
    const el = document.getElementById('coinAbout');
    if (!el || !currentAsset) return;
    if (el.dataset.loaded === '1') return;

    el.innerHTML = '<p>Loading coin information...</p>';
    try {
        const id = encodeURIComponent(currentAsset.id || coinKey);
        const response = await fetch(
            `../../api/coingecko.php?path=/coins/${id}&localization=false&tickers=false&market_data=false&community_data=false&developer_data=false&sparkline=false`,
            { credentials: 'same-origin' }
        );
        if (!response.ok) throw new Error('Unable to load coin details');
        const data = await response.json();
        if (data && data.error) throw new Error(data.error);

        const rawHtml = (data.description && data.description.en) ? data.description.en : '';
        const tmp = document.createElement('div');
        tmp.innerHTML = rawHtml;
        const text = (tmp.textContent || tmp.innerText || '').replace(/\s+\n/g, '\n').trim();

        if (!text) {
            el.innerHTML = `<p class="text-sm">No description is available for ${escapeHtml(currentAsset.name || currentAsset.symbol || 'this coin')}.</p>`;
            el.dataset.loaded = '1';
            return;
        }

        const truncated = text.length > 2500 ? text.slice(0, 2500).trim() + '…' : text;
        const homepage = Array.isArray(data.links && data.links.homepage)
            ? (data.links.homepage.find(Boolean) || '')
            : '';
        el.innerHTML = `
            <h3 class="font-bold text-primary mb-2">${escapeHtml(data.name || currentAsset.name || '')}</h3>
            <p class="text-sm leading-relaxed whitespace-pre-wrap text-on-surface">${escapeHtml(truncated)}</p>
            ${homepage ? `<p class="mt-4 text-sm"><a class="text-secondary hover:underline" href="${escapeHtml(homepage)}" target="_blank" rel="noopener noreferrer">Official website</a></p>` : ''}
        `;
        el.dataset.loaded = '1';
    } catch (error) {
        console.error('Error loading coin about:', error);
        el.innerHTML = `<p class="text-sm text-error">Could not load coin information. Please try again later.</p>`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function getCachedAssetPrice(coinId) {
    try {
        const cached = sessionStorage.getItem('crypto_prices_cache');
        if (!cached) return null;
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp < 30000 && data && data[coinId]) return data[coinId];
        return null;
    } catch (e) { return null; }
}

async function fetchAssetData() {
    try {
        const cached = getCachedAssetPrice(currentAsset.id);
        if (cached) {
            currentPrice = cached.usd || 0;
            updatePriceDisplay(currentPrice, cached.usd_24h_change || 0, cached.usd_market_cap || 0);
            updateBalanceDisplay();
            return;
        }
        const response = await fetch(`../../api/coingecko.php?path=/simple/price&ids=${encodeURIComponent(currentAsset.id)}&vs_currencies=usd&include_24hr_change=true&include_market_cap=true`, { credentials: 'same-origin' });
        if (response.status === 429 && cached) {
            currentPrice = cached.usd || 0;
            updatePriceDisplay(currentPrice, cached.usd_24h_change || 0, cached.usd_market_cap || 0);
            updateBalanceDisplay();
            return;
        }
        const data = await response.json();
        if (data && data[currentAsset.id]) {
            const assetData = data[currentAsset.id];
            currentPrice = assetData.usd || 0;
            updatePriceDisplay(currentPrice, assetData.usd_24h_change || 0, assetData.usd_market_cap || 0);
            updateBalanceDisplay();
        }
    } catch (error) {
        console.error('Error fetching asset data:', error);
        const cached = getCachedAssetPrice(currentAsset.id);
        if (cached) {
            currentPrice = cached.usd || 0;
            updatePriceDisplay(currentPrice, cached.usd_24h_change || 0, cached.usd_market_cap || 0);
            updateBalanceDisplay();
        }
    }
}

async function fetchChartData(days = '1') {
    try {
        const url = `../../api/coingecko.php?path=/coins/${encodeURIComponent(currentAsset.id)}/market_chart&vs_currency=usd&days=${encodeURIComponent(days)}`;
        let response = await fetch(url, { credentials: 'same-origin' });
        if (response.status === 429) {
            await new Promise(resolve => setTimeout(resolve, 5000));
            response = await fetch(url, { credentials: 'same-origin' });
        }
        if (response.ok) {
            const data = await response.json();
            if (data && data.prices) processChartData(data.prices, days);
            else createFallbackChart();
        } else createFallbackChart();
    } catch (error) {
        console.error('Error fetching chart data:', error);
        createFallbackChart();
    }
}

function processChartData(prices, days) {
    const labels = [];
    const chartData = [];
    const sampleInterval = Math.max(1, Math.floor(prices.length / 50));
    prices.forEach(([timestamp, price], index) => {
        if (index % sampleInterval === 0) {
            const date = new Date(timestamp);
            let label;
            if (days === '1') label = date.getHours() + 'h';
            else if (days === '7') label = date.toLocaleDateString('en', { weekday: 'short' });
            else if (days === '30') label = date.getDate() + '/' + (date.getMonth() + 1);
            else label = date.toLocaleDateString('en', { month: 'short' });
            labels.push(label);
            chartData.push(price);
        }
    });
    createChart(labels, chartData);
}

function createChart(labels, data) {
    const ctx = document.getElementById('priceChart').getContext('2d');
    if (priceChart) priceChart.destroy();
    const isPositive = data[data.length - 1] >= data[0];
    const chartColor = isPositive ? '#86efac' : '#fca5a5';
    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: `${currentAsset.symbol} Price`,
                data,
                borderColor: chartColor,
                backgroundColor: isPositive ? 'rgba(134, 239, 172, 0.12)' : 'rgba(252, 165, 165, 0.12)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: (context) => `$${context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                    }
                }
            },
            scales: { x: { display: false, grid: { display: false } }, y: { display: false, grid: { display: false } } },
            interaction: { intersect: false, mode: 'nearest' }
        }
    });
}

function createFallbackChart() {
    const ctx = document.getElementById('priceChart').getContext('2d');
    if (priceChart) priceChart.destroy();
    const labels = ['12h', '14h', '16h', '18h', '20h', '22h', '24h'];
    const data = [currentPrice * 0.98, currentPrice * 0.99, currentPrice, currentPrice * 1.01, currentPrice * 1.02, currentPrice * 1.01, currentPrice];
    priceChart = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ data, borderColor: '#b6c4ff', backgroundColor: 'rgba(182, 196, 255, 0.12)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
    });
}

function updatePriceDisplay(price, change, marketCap) {
    if (price) {
        document.getElementById('currentPrice').textContent = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    if (change !== undefined) {
        const changeAmount = (change / 100) * price;
        const isPositive = change >= 0;
        const el = document.getElementById('priceChange');
        el.textContent = `${isPositive ? '▲' : '▼'} $${Math.abs(changeAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${isPositive ? '+' : ''}${change.toFixed(2)}%)`;
        el.className = isPositive ? 'text-lg font-medium mb-1 text-deep-forest' : 'text-lg font-medium mb-1 text-error';
    }
    if (marketCap) {
        document.getElementById('marketCap').textContent = `Market Cap: $${(marketCap / 1e9).toFixed(2)}B`;
    }
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

async function loadTransactionHistory() {
    const historyContainer = document.getElementById('transactionHistory');
    historyContainer.innerHTML = '<p class="text-on-surface-variant text-center">Loading...</p>';
    try {
        const response = await fetch(`../../api/user/transactions.php?coin_key=${encodeURIComponent(coinKey)}`, { credentials: 'same-origin' });
        const data = await response.json();
        if (data.success && data.transactions && data.transactions.length > 0) {
            historyContainer.innerHTML = data.transactions.map(tx => {
                const type = escapeHtml(tx.type || 'unknown');
                const amount = parseFloat(tx.amount || 0);
                const date = new Date(tx.created_at);
                const typeClass = type === 'send' ? 'text-error' : type === 'receive' ? 'text-deep-forest' : 'text-on-surface-variant';
                const iconName = type === 'send' ? 'send' : type === 'receive' ? 'receive' : 'swap';
                const coinSymbol = escapeHtml(tx.coin_symbol || currentAsset.symbol || '');
                const status = escapeHtml(tx.status || 'completed');
                return `
                    <div class="flex items-center justify-between p-4 border-b border-outline-variant/30">
                        <div class="flex items-center gap-3">
                            ${typeof wtIcon === 'function' ? wtIcon(iconName, 'w-5 h-5 ' + typeClass) : ''}
                            <div>
                                <p class="font-medium text-on-surface">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                                <p class="text-xs text-on-surface-variant">${escapeHtml(date.toLocaleDateString())} ${escapeHtml(date.toLocaleTimeString())}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold ${typeClass}">${type === 'send' ? '-' : '+'}${amount.toFixed(8)} ${coinSymbol}</p>
                            <p class="text-xs text-on-surface-variant">${status}</p>
                        </div>
                    </div>`;
            }).join('');
        } else {
            historyContainer.innerHTML = '<p class="text-on-surface-variant text-center p-4">No transaction history</p>';
        }
    } catch (error) {
        console.error('Error loading transaction history:', error);
        historyContainer.innerHTML = '<p class="text-error text-center p-4">Error loading transaction history</p>';
    }
}

setInterval(fetchAssetData, 30000);

document.addEventListener('DOMContentLoaded', initializePage);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
