<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'My Assets | WyomingTrust';
$active_nav = 'crypto-assets';
$extra_styles = '@media (max-width: 767px) {
    .dashboard-content {
        padding-top: calc(5rem + 0.25rem) !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        padding-bottom: 1rem !important;
    }
    .dashboard-content > * + * { margin-top: 0.75rem !important; }
    .assets-metrics-tight .dashboard-metric-card { padding: 0.875rem !important; }
}';

include __DIR__ . '/includes/layout.php';
?>

<section class="hidden justify-end gap-2 mb-4" id="assetsQuickActions" aria-hidden="true">
<a href="send.php" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-3 sm:px-6 py-2 sm:py-2.5 rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">
<?php echo wt_icon('send', 'text-base'); ?>
<span class="hidden sm:inline">Send</span>
</a>
<a href="receive.php" class="flex items-center justify-center gap-2 bg-surface-container-low text-on-surface px-3 sm:px-6 py-2 sm:py-2.5 rounded-lg text-sm font-bold hover:bg-surface-container transition-colors">
<?php echo wt_icon('receive', 'text-base'); ?>
<span class="hidden sm:inline">Receive</span>
</a>
<a href="swap.php" class="flex items-center justify-center gap-2 bg-secondary text-on-secondary px-3 sm:px-6 py-2 sm:py-2.5 rounded-lg text-sm font-bold hover:bg-secondary/90 transition-colors">
<?php echo wt_icon('swap', 'text-base'); ?>
<span class="hidden sm:inline">Swap</span>
</a>
</section>

<section class="space-y-3 sm:space-y-4 mb-2 assets-metrics-tight">
<div class="dashboard-metric-card rounded-2xl p-4 sm:p-8 border border-outline-variant bg-surface-container-lowest shadow-sm text-center">
<div class="flex items-center justify-center gap-2 text-primary mb-2">
<?php echo wt_icon('wallet', 'w-4 h-4'); ?>
<p class="text-on-surface-variant text-[11px] sm:text-sm font-medium uppercase tracking-wide">Total Portfolio Value</p>
</div>
<div class="dashboard-metric-value-wrap mx-auto max-w-md">
<p id="totalPortfolioValue" class="dashboard-metric-value text-on-surface tracking-tight leading-tight text-center" data-fit-max="40" data-fit-max-mobile="28" data-fit-min="16">$0.00</p>
</div>
<p class="text-[11px] sm:text-xs text-on-surface-variant mt-2" id="portfolioChange">--</p>
</div>

<div class="grid grid-cols-3 gap-2 sm:gap-4">
<div class="dashboard-metric-card min-w-0 flex flex-col items-center text-center gap-1 rounded-xl sm:rounded-2xl p-2.5 sm:p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-[9px] sm:text-xs font-medium leading-tight">24h Change</p>
<div class="dashboard-metric-value-wrap w-full">
<p id="total24hChange" class="dashboard-metric-value text-on-surface tracking-tight leading-tight text-center" data-fit-max="22" data-fit-max-mobile="13" data-fit-min="9">--</p>
</div>
</div>
<div class="dashboard-metric-card min-w-0 flex flex-col items-center text-center gap-1 rounded-xl sm:rounded-2xl p-2.5 sm:p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-[9px] sm:text-xs font-medium leading-tight">Total Assets</p>
<div class="dashboard-metric-value-wrap w-full">
<p id="totalAssetsCount" class="dashboard-metric-value text-on-surface tracking-tight leading-tight text-center" data-fit-max="22" data-fit-max-mobile="13" data-fit-min="9">0</p>
</div>
</div>
<div class="dashboard-metric-card min-w-0 flex flex-col items-center text-center gap-1 rounded-xl sm:rounded-2xl p-2.5 sm:p-5 border border-outline-variant bg-surface-container-lowest shadow-sm">
<p class="text-on-surface-variant text-[9px] sm:text-xs font-medium leading-tight">Updated</p>
<p class="text-on-surface tracking-tight text-[11px] sm:text-base font-bold leading-tight truncate max-w-full" id="lastUpdated">Just Now</p>
</div>
</div>
</section>

<section class="overflow-hidden border border-outline-variant rounded-2xl bg-surface-container-lowest shadow-sm">
<div class="p-4 sm:p-6 border-b border-outline-variant">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<h3 class="font-headline-md text-headline-md text-primary">All Assets</h3>
<div class="flex gap-2 sm:gap-4">
<input type="text" id="assetSearch" placeholder="Search assets..." class="flex-1 sm:flex-none sm:w-64 px-4 py-2 text-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
<button id="refreshPrices" class="px-4 py-2 text-sm font-semibold bg-surface-container-low text-on-surface rounded-lg hover:bg-surface-container transition-colors">
<?php echo wt_icon('refresh', 'text-base align-middle'); ?>
</button>
</div>
</div>
</div>
<div id="assetsContainer" class="p-4 sm:p-6">
<div class="text-center py-10 text-on-surface-variant">
<div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent mb-4"></div>
<p>Loading assets...</p>
</div>
</div>
</section>

<script>
const COINGECKO_API = '../../api/coingecko.php';
const REFRESH_INTERVAL = 300000;
let cryptoPrices = {};
let userAssets = [];
let selectedCoinKeys = new Set(); // entrusted coins across user LLCs
let setupTrustId = 0; // preferred LLC for pending-setup deep link
let priceRefreshTimer = null;

async function loadAssets() {
    try {
        const [depositRes, assetsRes, trustsRes] = await Promise.all([
            fetch('../../api/coins.php?for=depositable', { credentials: 'same-origin' }),
            fetch('../../api/user/assets.php', { credentials: 'same-origin' }),
            fetch('../../api/user/trusts.php', { credentials: 'same-origin' }),
        ]);
        const depositData = await depositRes.json();
        const assetsData = await assetsRes.json();
        const trustsData = await trustsRes.json();

        if (!depositData.success || !Array.isArray(depositData.coins)) {
            document.getElementById('assetsContainer').innerHTML = '<div class="text-center py-10 text-error">Failed to load deposit-ready assets</div>';
            return;
        }

        const balanceByKey = {};
        const priceByKey = {};
        ((assetsData.success && assetsData.assets) ? assetsData.assets : []).forEach(a => {
            if (!a.coin_key) return;
            balanceByKey[a.coin_key] = parseFloat(a.balance || 0) || 0;
            if (a.price_usd != null) priceByKey[a.coin_key] = a;
        });

        selectedCoinKeys = new Set();
        setupTrustId = 0;
        const trusts = (trustsData.success && Array.isArray(trustsData.trusts)) ? trustsData.trusts : [];
        trusts.forEach(t => {
            const raw = t.entrusted_coins || t.trust_data?.entrusted_coins || [];
            if (Array.isArray(raw)) {
                raw.forEach(k => selectedCoinKeys.add(String(k).toLowerCase()));
            }
            if (!setupTrustId && t.id) {
                const svc = String(t.service_key || t.trust_type || '').toLowerCase();
                if (svc.includes('smart_contract') || (Array.isArray(raw) && raw.length > 0)) {
                    setupTrustId = Number(t.id) || 0;
                }
            }
        });
        if (!setupTrustId && trusts.length) {
            setupTrustId = Number(trusts[0].id) || 0;
        }

        // Only coins with wallet addresses (payment gateway / deposit routes)
        userAssets = depositData.coins.map(c => {
            const key = c.coin_key;
            const held = priceByKey[key] || {};
            return {
                coin_key: key,
                display_name: c.display_name || held.display_name || key,
                symbol: c.symbol || held.symbol || '',
                logo: c.logo || held.logo || '',
                balance: balanceByKey[key] != null ? balanceByKey[key] : 0,
                price_usd: held.price_usd || 0,
                price_change_24h: held.price_change_24h || 0,
                is_selected: selectedCoinKeys.has(String(key).toLowerCase()),
            };
        });

        await fetchCryptoPrices();
        renderAssets();
        updatePortfolioSummary();
    } catch (error) {
        console.error('Error loading assets:', error);
        document.getElementById('assetsContainer').innerHTML = '<div class="text-center py-10 text-error">Error loading assets</div>';
    }
}

function getCachedPrices() {
    try {
        const cached = sessionStorage.getItem('crypto_prices_cache');
        if (!cached) return null;
        const { data, timestamp } = JSON.parse(cached);
        const age = Date.now() - timestamp;
        if (age < 30000) {
            return data;
        }
        sessionStorage.removeItem('crypto_prices_cache');
        return null;
    } catch (e) {
        return null;
    }
}

function setCachedPrices(prices) {
    try {
        sessionStorage.setItem('crypto_prices_cache', JSON.stringify({
            data: prices,
            timestamp: Date.now()
        }));
    } catch (e) {
        // Ignore storage errors
    }
}

function batchCoinIds(coinIds, batchSize = 12) {
    const ids = coinIds.split(',').filter(Boolean);
    const batches = [];
    for (let i = 0; i < ids.length; i += batchSize) {
        batches.push(ids.slice(i, i + batchSize).join(','));
    }
    return batches;
}

async function fetchCryptoPrices() {
    if (userAssets.length === 0) return;
    
    const cached = getCachedPrices();
    if (cached) {
        cryptoPrices = cached;
        updateLastUpdated();
        return;
    }
    
    try {
        const coinIds = userAssets.map(a => a.coin_key).filter(Boolean).join(',');
        if (!coinIds) {
            userAssets.forEach(asset => {
                if (asset.price_usd) {
                    cryptoPrices[asset.coin_key] = {
                        usd: asset.price_usd,
                        usd_24h_change: asset.price_change_24h || 0
                    };
                }
            });
            updateLastUpdated();
            return;
        }
        
        const batches = batchCoinIds(coinIds, 12);
        const allPrices = {};
        
        for (const batch of batches) {
            let retries = 3;
            let delay = 5000;
            let success = false;
            
            while (retries > 0 && !success) {
                try {
                    const response = await fetch(
                        `${COINGECKO_API}?path=/simple/price&ids=${encodeURIComponent(batch)}&vs_currencies=usd&include_24hr_change=true`
                    );
                    
                    if (response.status === 429) {
                        console.warn(`Rate limit exceeded for batch. Waiting ${delay}ms...`);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        delay *= 2;
                        retries--;
                        continue;
                    }
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data && !data.error) {
                            Object.assign(allPrices, data);
                            success = true;
                        }
                    }
                    
                    if (success) break;
                    retries--;
                } catch (error) {
                    console.error('Error fetching batch:', error);
                    retries--;
                    if (retries > 0) {
                        await new Promise(resolve => setTimeout(resolve, delay));
                        delay *= 2;
                    }
                }
                
                if (batches.length > 1) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
            }
            
            if (!success) {
                const batchIds = batch.split(',');
                batchIds.forEach(id => {
                    const asset = userAssets.find(a => a.coin_key === id);
                    if (asset && asset.price_usd) {
                        allPrices[id] = {
                            usd: asset.price_usd,
                            usd_24h_change: asset.price_change_24h || 0
                        };
                    }
                });
            }
        }
        
        if (Object.keys(allPrices).length > 0) {
            cryptoPrices = allPrices;
            setCachedPrices(allPrices);
            updateLastUpdated();
        } else {
            userAssets.forEach(asset => {
                if (asset.price_usd) {
                    cryptoPrices[asset.coin_key] = {
                        usd: asset.price_usd,
                        usd_24h_change: asset.price_change_24h || 0
                    };
                }
            });
            updateLastUpdated();
        }
    } catch (error) {
        console.error('Error fetching prices:', error);
        const cached = getCachedPrices();
        if (cached) {
            cryptoPrices = cached;
            updateLastUpdated();
        } else {
            userAssets.forEach(asset => {
                if (asset.price_usd) {
                    cryptoPrices[asset.coin_key] = {
                        usd: asset.price_usd,
                        usd_24h_change: asset.price_change_24h || 0
                    };
                }
            });
            updateLastUpdated();
        }
    }
}

function renderAssets() {
    const container = document.getElementById('assetsContainer');
    if (!userAssets || userAssets.length === 0) {
        container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No assets found. <a href="../../onboarding/onboarding.php" class="text-secondary hover:underline">Start by linking a wallet</a></div>';
        return;
    }
    
    const searchTerm = document.getElementById('assetSearch')?.value.toLowerCase() || '';
    const filteredAssets = userAssets.filter(asset => {
        const name = (asset.display_name || '').toLowerCase();
        const symbol = (asset.symbol || '').toLowerCase();
        return name.includes(searchTerm) || symbol.includes(searchTerm);
    });
    
    const sortedAssets = [...filteredAssets].sort((a, b) => {
        // Selected (active) first, then by price
        if (!!a.is_selected !== !!b.is_selected) return a.is_selected ? -1 : 1;
        const priceA = a.price_usd || cryptoPrices[a.coin_key]?.usd || 0;
        const priceB = b.price_usd || cryptoPrices[b.coin_key]?.usd || 0;
        return priceB - priceA;
    });

    if (!sortedAssets.length) {
        container.innerHTML = '<div class="text-center py-10 text-on-surface-variant">No deposit-ready assets available yet.</div>';
        return;
    }
    
    const setupHref = setupTrustId > 0
        ? `manage-trust.php?id=${setupTrustId}#cryptoPortfolioSection`
        : 'manage-trust.php';
    
    const assetsHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low border-b border-outline-variant">
                    <tr>
                        <th class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 text-[10px] sm:text-xs font-bold text-on-surface-variant uppercase">Asset</th>
                        <th class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 text-[10px] sm:text-xs font-bold text-on-surface-variant uppercase">Balance</th>
                        <th class="hidden sm:table-cell px-2 sm:px-4 md:px-6 py-2 sm:py-3 text-[10px] sm:text-xs font-bold text-on-surface-variant uppercase">Price</th>
                        <th class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 text-[10px] sm:text-xs font-bold text-on-surface-variant uppercase text-right">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    ${sortedAssets.map(asset => {
                        const price = asset.price_usd || cryptoPrices[asset.coin_key]?.usd || 0;
                        const change24h = asset.price_change_24h || cryptoPrices[asset.coin_key]?.usd_24h_change || 0;
                        const balance = parseFloat(asset.balance || 0);
                        const value = balance * price;
                        const changeClass = change24h >= 0 ? 'text-deep-forest' : 'text-error';
                        const changeSign = change24h >= 0 ? '+' : '';
                        const priceLabel = '$' + Number(price).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: price > 1000 ? 2 : 6
                        });
                        const changeLabel = `${changeSign}${Math.abs(change24h).toFixed(2)}%`;
                        const isSelected = !!asset.is_selected;
                        const rowClass = isSelected
                            ? 'bg-surface-container hover:bg-surface-container transition-colors cursor-pointer'
                            : 'hover:bg-surface-container-low transition-colors cursor-pointer';
                        const statusBadge = isSelected
                            ? `<span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wide bg-deep-forest/15 text-deep-forest">Active</span>`
                            : `<a href="${setupHref}" onclick="event.stopPropagation()" class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-800 hover:bg-amber-200">Pending setup</a>`;
                        
                        return `
                            <tr class="${rowClass}" onclick="window.location.href='asset-detail.php?coin_key=${encodeURIComponent(asset.coin_key || '')}${setupTrustId > 0 ? '&trust_id=' + setupTrustId : ''}'">
                                <td class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4">
                                    <div class="flex items-center gap-1.5 sm:gap-2 md:gap-3">
                                        <img src="${asset.logo || ''}" alt="${asset.display_name}" class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 rounded-full flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 rounded-full bg-surface-container-low flex items-center justify-center hidden">
                                            <span class="text-[8px] sm:text-[10px] md:text-xs font-bold text-on-surface">${escapeHtml((asset.symbol || '?').charAt(0))}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-[10px] sm:text-xs md:text-sm truncate text-on-surface">${escapeHtml(asset.display_name || asset.symbol || 'Unknown')}</p>
                                            <p class="text-[9px] sm:text-[10px] md:text-xs text-on-surface-variant">${escapeHtml(asset.symbol || '')}</p>
                                            ${statusBadge}
                                            <div class="sm:hidden mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                                <span class="text-[9px] font-medium text-on-surface">${priceLabel}</span>
                                                <span class="text-[9px] ${changeClass}">${changeLabel}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4">
                                    <p class="text-[10px] sm:text-xs md:text-sm font-medium text-on-surface">${formatCryptoBalance(balance)}</p>
                                    <p class="text-[9px] sm:text-[10px] md:text-xs text-on-surface-variant">${escapeHtml(asset.symbol || '')}</p>
                                </td>
                                <td class="hidden sm:table-cell px-2 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4">
                                    <p class="text-[10px] sm:text-xs md:text-sm font-medium text-on-surface">${priceLabel}</p>
                                    <p class="text-[9px] sm:text-[10px] md:text-xs ${changeClass}">${changeLabel}</p>
                                </td>
                                <td class="px-2 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 text-right">
                                    <p class="text-[10px] sm:text-xs md:text-sm font-bold text-on-surface">$${value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = assetsHTML;
}

/** Zero → 0.00; otherwise up to 8 decimals with trailing zeros removed. */
function formatCryptoBalance(amount) {
    const num = Number(amount);
    if (!Number.isFinite(num) || Math.abs(num) < 1e-12) return '0.00';
    return num.toFixed(8).replace(/\.?0+$/, '');
}

function updatePortfolioSummary() {
    let totalValue = 0;
    let totalChange = 0;
    let totalAssets = 0;
    
    userAssets.forEach(asset => {
        const price = asset.price_usd || cryptoPrices[asset.coin_key]?.usd || 0;
        const change24h = asset.price_change_24h || cryptoPrices[asset.coin_key]?.usd_24h_change || 0;
        const balance = parseFloat(asset.balance || 0);
        const value = balance * price;

        // Total Assets = selected (set up) coins, even at $0 balance
        if (asset.is_selected) {
            totalAssets++;
        }
        if (balance > 0) {
            totalValue += value;
            totalChange += (value * change24h / 100);
        }
    });
    
    const changePercent = totalValue > 0 ? (totalChange / (totalValue - totalChange) * 100) : 0;
    const changeClass = totalChange >= 0 ? 'text-deep-forest' : 'text-error';
    const changeSign = totalChange >= 0 ? '+' : '';
    
    document.getElementById('totalPortfolioValue').textContent = '$' + totalValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('portfolioChange').innerHTML = `<span class="${changeClass}">${changeSign}$${Math.abs(totalChange).toFixed(2)} (${changeSign}${Math.abs(changePercent).toFixed(2)}%)</span>`;
    const changeEl = document.getElementById('total24hChange');
    changeEl.textContent = `${changeSign}${Math.abs(changePercent).toFixed(2)}%`;
    changeEl.className = 'dashboard-metric-value text-on-surface tracking-tight leading-tight text-center ' + changeClass;
    document.getElementById('totalAssetsCount').textContent = totalAssets;
    if (typeof window.fitDashboardAmounts === 'function') window.fitDashboardAmounts();
}

function updateLastUpdated() {
    const now = new Date();
    document.getElementById('lastUpdated').textContent = now.toLocaleTimeString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function startPriceRefresh() {
    if (priceRefreshTimer) clearInterval(priceRefreshTimer);
    priceRefreshTimer = setInterval(() => {
        fetchCryptoPrices().then(() => {
            renderAssets();
            updatePortfolioSummary();
        });
    }, REFRESH_INTERVAL);
}

document.getElementById('refreshPrices')?.addEventListener('click', () => {
    fetchCryptoPrices().then(() => {
        renderAssets();
        updatePortfolioSummary();
    });
});

document.getElementById('assetSearch')?.addEventListener('input', () => {
    renderAssets();
});

document.addEventListener('DOMContentLoaded', () => {
    loadAssets();
    startPriceRefresh();
});
</script>
<?php include __DIR__ . '/includes/layout-footer.php'; ?>
