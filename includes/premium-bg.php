<?php
/**
 * Login-style premium background (gradient + landscape overlay).
 */

function wt_premium_bg_image_url(): string {
    return asset_url('Storage/images/wyoming-business-landscape.jpg');
}

function wt_premium_bg_css(): string {
    $bg_image = escape_html(wt_premium_bg_image_url());

    return <<<CSS
.bg-premium {
    background: linear-gradient(135deg, #121c2a 0%, #1e293b 50%, #0f172a 100%);
    position: relative;
}
.bg-premium::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('{$bg_image}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.14;
    pointer-events: none;
}
.bg-premium::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top right, rgba(0, 35, 111, 0.35) 0%, transparent 42%),
        radial-gradient(circle at bottom left, rgba(64, 89, 170, 0.18) 0%, transparent 42%),
        linear-gradient(135deg, rgba(18, 28, 42, 0.88) 0%, rgba(15, 23, 42, 0.92) 100%);
    pointer-events: none;
}
.premium-bg-page .dashboard-shell,
.premium-bg-page .dashboard-main,
.premium-bg-page .dashboard-content {
    position: relative;
    z-index: 1;
}
.premium-bg-page .dashboard-main {
    background: transparent;
}
/* Sidebar stays white with default dashboard colors on premium crypto pages */
.premium-bg-page aside.dashboard-sidebar {
    background: #ffffff !important;
    border-right-color: #c4c6cd !important;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}
.premium-bg-page aside.dashboard-sidebar .font-headline-md {
    color: #041627 !important;
}
.premium-bg-page aside.dashboard-sidebar nav a:not(.sidebar-active),
.premium-bg-page aside.dashboard-sidebar .border-t + div a:not(.sidebar-active):not(.text-error) {
    color: #44474c !important;
}
.premium-bg-page aside.dashboard-sidebar nav a:not(.sidebar-active):hover,
.premium-bg-page aside.dashboard-sidebar .border-t + div a:not(.sidebar-active):not(.text-error):hover {
    background-color: #edeeef !important;
    color: #44474c !important;
}
.premium-bg-page aside.dashboard-sidebar .text-secondary {
    color: #115cb9 !important;
}
.premium-bg-page aside.dashboard-sidebar .border-outline-variant,
.premium-bg-page aside.dashboard-sidebar .border-t {
    border-color: #c4c6cd !important;
}
.premium-bg-page aside.dashboard-sidebar a.text-error {
    color: #ba1a1a !important;
}
.premium-bg-page aside.dashboard-sidebar a.text-error:hover {
    background-color: rgba(255, 218, 214, 0.35) !important;
}
.premium-bg-page aside.dashboard-sidebar nav a:not(.sidebar-active) svg {
    stroke: #44474c !important;
}
.premium-bg-page aside.dashboard-sidebar .sidebar-active svg {
    stroke: #ffffff !important;
}
.premium-bg-page .dashboard-topbar {
    background: rgba(18, 28, 42, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-color: rgba(255, 255, 255, 0.08);
}
.premium-bg-page .dashboard-topbar .text-on-surface-variant,
.premium-bg-page .dashboard-topbar input::placeholder {
    color: rgba(255, 255, 255, 0.55);
}
.premium-bg-page .dashboard-topbar input {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}
.premium-bg-page .dashboard-topbar .bg-surface-container {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.9);
}
.premium-bg-page .dashboard-topbar .bg-outline-variant {
    background: rgba(255, 255, 255, 0.15);
}
.premium-bg-page .dashboard-content .font-headline-lg,
.premium-bg-page .dashboard-content h1.font-headline-lg,
.premium-bg-page .dashboard-content h1 {
    color: #ffffff;
}
.premium-bg-page .dashboard-content > section > .text-on-surface-variant,
.premium-bg-page .dashboard-content > section p.text-on-surface-variant,
.premium-bg-page .dashboard-content .mb-6 > p.text-on-surface-variant {
    color: rgba(255, 255, 255, 0.68);
}
.premium-bg-page .dashboard-content a.text-secondary {
    color: #b6c4ff;
}
.premium-bg-page .dashboard-content a.text-secondary:hover {
    color: #dce1ff;
}
.premium-bg-page #mobile-nav > div:last-child {
    background: #ffffff !important;
    color: #041627 !important;
    border-right: 1px solid #c4c6cd;
}
.premium-bg-page #mobile-nav .font-headline-md {
    color: #041627 !important;
}
.premium-bg-page #mobile-nav nav a:not(.bg-primary):not(.text-primary),
.premium-bg-page #mobile-nav .border-t + div a:not(.bg-primary):not(.text-primary):not(.text-error) {
    color: #44474c !important;
}
.premium-bg-page #mobile-nav nav a:not(.bg-primary):not(.text-primary):hover,
.premium-bg-page #mobile-nav .border-t + div a:not(.bg-primary):not(.text-primary):not(.text-error):hover {
    background-color: #edeeef !important;
    color: #44474c !important;
}
.premium-bg-page #mobile-nav nav a.bg-primary\/10.text-primary,
.premium-bg-page #mobile-nav .border-t + div a.bg-primary\/10.text-primary {
    color: #115cb9 !important;
    background-color: rgba(17, 92, 185, 0.1) !important;
}
.premium-bg-page #mobile-nav .border-outline-variant,
.premium-bg-page #mobile-nav .border-t {
    border-color: #c4c6cd !important;
}
.premium-bg-page #mobile-nav a.text-error {
    color: #ba1a1a !important;
}
.premium-bg-page #mobile-nav a.text-error:hover {
    background-color: rgba(255, 218, 214, 0.35) !important;
}
.premium-bg-page #mobile-nav nav a:not(.bg-primary) svg,
.premium-bg-page #mobile-nav .border-t + div a:not(.text-error) svg {
    stroke: #44474c !important;
}
.premium-bg-page #mobile-nav nav a.bg-primary svg {
    stroke: #ffffff !important;
}
.premium-bg-page #mobile-nav button:hover {
    background-color: #edeeef !important;
}
.premium-bg-page #mobile-nav button svg {
    stroke: #44474c !important;
}

/* --- Topbar: white icons / menu toggle on dark header --- */
.premium-bg-page .dashboard-topbar {
    color: #ffffff;
}
.premium-bg-page .dashboard-topbar button {
    color: #ffffff;
}
.premium-bg-page .dashboard-topbar button:hover {
    background: rgba(255, 255, 255, 0.12);
}
.premium-bg-page .dashboard-topbar button svg,
.premium-bg-page .dashboard-topbar .wt-icon {
    stroke: #ffffff !important;
}
.premium-bg-page .dashboard-topbar .text-on-surface-variant {
    color: rgba(255, 255, 255, 0.82);
}
.premium-bg-page .dashboard-topbar .font-label-md {
    color: rgba(255, 255, 255, 0.92);
}

/* --- Crypto pages: navy gradient cards (flat panels, not buttons) --- */
.premium-bg-page {
    --crypto-card-gradient: linear-gradient(145deg, #041627 0%, #082238 52%, #0c3258 100%);
    --crypto-panel-gradient: linear-gradient(145deg, #051a2e 0%, #082238 55%, #0a2d50 100%);
    --crypto-inset-bg: rgba(255, 255, 255, 0.08);
    --crypto-inset-border: rgba(255, 255, 255, 0.12);
}
.premium-bg-page .dashboard-content {
    color: rgba(255, 255, 255, 0.92);
}
.premium-bg-page .dashboard-content .text-on-surface {
    color: rgba(255, 255, 255, 0.92) !important;
}
.premium-bg-page .dashboard-content .text-on-surface-variant {
    color: rgba(255, 255, 255, 0.68) !important;
}
.premium-bg-page .dashboard-content strong.text-primary {
    color: #ffffff !important;
}

/* Primary cards — swap/receive/link-wallet/assets/send forms + security blocks */
.premium-bg-page .dashboard-content .dashboard-metric-card,
.premium-bg-page .dashboard-content .card-shadow,
.premium-bg-page .dashboard-content section > .overflow-hidden.border.rounded-2xl.bg-surface-container-lowest,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.card-shadow,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.shadow-sm,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.overflow-hidden,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-2xl.border.border-outline-variant,
.premium-bg-page .dashboard-content .bg-warm-cream.border.border-outline-variant.rounded-2xl,
.premium-bg-page .dashboard-content section.grid > .bg-surface-container-lowest.rounded-2xl,
.premium-bg-page .dashboard-content #liquidationSuccessPanel,
.premium-bg-page .dashboard-content #sendFormPanel,
.premium-bg-page #assetModal > .bg-surface-container-lowest.rounded-2xl,
.premium-bg-page #walletModal > .bg-surface-container-lowest.rounded-2xl {
    background: var(--crypto-card-gradient) !important;
    border-color: rgba(255, 255, 255, 0.06) !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.22) !important;
}

/* Medium panels — deposit amount, QR wrapper, confirm blocks */
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-xl,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-xl.border,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-xl.border.border-outline-variant {
    background: var(--crypto-panel-gradient) !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

/* Small inset panels — estimated fee, summaries */
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-lg {
    background: var(--crypto-inset-bg) !important;
    border-color: var(--crypto-inset-border) !important;
}

/* Inner nested boxes — QR frame, address row, asset chips */
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-xl,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-lg {
    background: var(--crypto-inset-bg) !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
}
.premium-bg-page .dashboard-content #qrCode {
    background: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.35) !important;
}
.premium-bg-page #walletModal .size-10.bg-surface-container-lowest,
.premium-bg-page #walletModal .rounded-full.bg-surface-container-lowest {
    background: #ffffff !important;
}

/* Asset / coin selector rows */
.premium-bg-page .dashboard-content .border.border-outline-variant.rounded-lg,
.premium-bg-page .dashboard-content .border.border-outline-variant.rounded-xl.cursor-pointer,
.premium-bg-page .dashboard-content #lockedAssetDisplay {
    background: var(--crypto-inset-bg) !important;
    border-color: var(--crypto-inset-border) !important;
}
.premium-bg-page .dashboard-content .hover\:bg-surface-container-low:hover,
.premium-bg-page .dashboard-content .cursor-pointer.hover\:bg-surface-container-low:hover {
    background: rgba(255, 255, 255, 0.14) !important;
}

/* Accent notice panels */
.premium-bg-page .dashboard-content [class*="bg-secondary/10"] {
    background: rgba(17, 92, 185, 0.18) !important;
    border-color: rgba(182, 196, 255, 0.22) !important;
}
.premium-bg-page .dashboard-content #liquidationFeeNotice {
    background: rgba(17, 92, 185, 0.18) !important;
    border-color: rgba(182, 196, 255, 0.22) !important;
}

.premium-bg-page .dashboard-content .dashboard-metric-card .text-primary,
.premium-bg-page .dashboard-content .dashboard-metric-card .text-on-surface,
.premium-bg-page .dashboard-content .dashboard-metric-card .text-on-surface-variant,
.premium-bg-page .dashboard-content .dashboard-metric-card .dashboard-metric-value,
.premium-bg-page .dashboard-content .card-shadow .text-primary,
.premium-bg-page .dashboard-content .card-shadow .text-on-surface,
.premium-bg-page .dashboard-content .card-shadow .text-on-surface-variant,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl .text-primary,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl .text-on-surface,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl .text-on-surface-variant,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-2xl .text-primary,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-2xl .text-on-surface,
.premium-bg-page .dashboard-content .bg-surface-container-low.rounded-2xl .text-on-surface-variant,
.premium-bg-page .dashboard-content .font-headline-md.text-primary,
.premium-bg-page .dashboard-content h3.text-primary,
.premium-bg-page #assetModal .text-primary,
.premium-bg-page #assetModal .text-on-surface,
.premium-bg-page #assetModal .text-on-surface-variant,
.premium-bg-page #walletModal .text-primary,
.premium-bg-page #walletModal .text-on-surface,
.premium-bg-page #walletModal .text-on-surface-variant {
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .font-headline-lg.text-primary,
.premium-bg-page .dashboard-content h1.text-primary,
.premium-bg-page .dashboard-content h2.text-primary {
    color: #ffffff !important;
}
.premium-bg-page #assetModal .text-on-surface-variant,
.premium-bg-page #walletModal .text-on-surface-variant {
    color: rgba(255, 255, 255, 0.68) !important;
}
.premium-bg-page .dashboard-content ul.text-on-surface-variant li,
.premium-bg-page .dashboard-content .list-disc.text-on-surface-variant {
    color: rgba(255, 255, 255, 0.72) !important;
}

/* Chart + tab panels */
.premium-bg-page .dashboard-content #priceChart {
    opacity: 0.98;
}
.premium-bg-page .dashboard-content .coin-tab {
    color: rgba(255, 255, 255, 0.55);
    border-bottom-color: transparent;
}
.premium-bg-page .dashboard-content .coin-tab.text-primary,
.premium-bg-page .dashboard-content .coin-tab.border-primary {
    color: #ffffff !important;
    border-bottom-color: #b6c4ff !important;
}
.premium-bg-page .dashboard-content .coin-tab:hover {
    color: rgba(255, 255, 255, 0.9);
}
.premium-bg-page .dashboard-content .border-b.border-outline-variant {
    border-color: rgba(255, 255, 255, 0.18) !important;
}

/* Time-range toggle buttons */
.premium-bg-page .dashboard-content .time-filter {
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1) !important;
}
.premium-bg-page .dashboard-content .time-filter:hover {
    background: rgba(255, 255, 255, 0.18) !important;
}
.premium-bg-page .dashboard-content .time-filter.active,
.premium-bg-page .dashboard-content .time-filter.bg-primary {
    background: #ffffff !important;
    color: #041627 !important;
    border-color: #ffffff !important;
}

/* Assets table inside navy card */
.premium-bg-page .dashboard-content thead.bg-surface-container-low {
    background: rgba(255, 255, 255, 0.08) !important;
}
.premium-bg-page .dashboard-content tbody.divide-y,
.premium-bg-page .dashboard-content .divide-outline-variant > :not([hidden]) ~ :not([hidden]) {
    border-color: rgba(255, 255, 255, 0.1);
}
.premium-bg-page .dashboard-content tbody tr.bg-surface-container,
.premium-bg-page .dashboard-content tbody tr.hover\:bg-surface-container-low:hover,
.premium-bg-page .dashboard-content tbody tr.hover\:bg-surface-container:hover {
    background: rgba(255, 255, 255, 0.06) !important;
}
.premium-bg-page .dashboard-content tbody .text-on-surface,
.premium-bg-page .dashboard-content tbody .text-on-surface-variant,
.premium-bg-page .dashboard-content thead .text-on-surface-variant {
    color: rgba(255, 255, 255, 0.88) !important;
}
.premium-bg-page .dashboard-content #assetSearch,
.premium-bg-page .dashboard-content input:not([type="checkbox"]):not([type="radio"]),
.premium-bg-page .dashboard-content select,
.premium-bg-page .dashboard-content textarea {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.22) !important;
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content input::placeholder,
.premium-bg-page .dashboard-content textarea::placeholder {
    color: rgba(255, 255, 255, 0.45);
}
.premium-bg-page .dashboard-content #refreshPrices,
.premium-bg-page .dashboard-content button.bg-surface-container-low.text-on-surface,
.premium-bg-page .dashboard-content button.bg-surface-container-lowest.text-on-surface,
.premium-bg-page .dashboard-content button.bg-surface-container-lowest.text-sm,
.premium-bg-page .dashboard-content .fee-option {
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
}
.premium-bg-page .dashboard-content .fee-option.border-secondary {
    background: rgba(182, 196, 255, 0.2) !important;
    border-color: rgba(182, 196, 255, 0.45) !important;
}
.premium-bg-page .dashboard-content #refreshPrices svg {
    stroke: #ffffff !important;
}
.premium-bg-page .dashboard-content button.bg-surface-container-low svg,
.premium-bg-page .dashboard-content button.bg-surface-container-lowest svg {
    stroke: #ffffff !important;
}

/* Keep semantic greens/reds readable on navy */
.premium-bg-page .dashboard-content .text-deep-forest {
    color: #86efac !important;
}
.premium-bg-page .dashboard-content .text-error {
    color: #fca5a5 !important;
}
.premium-bg-page .dashboard-content #transactionHistory .text-on-surface,
.premium-bg-page .dashboard-content #transactionHistory .text-on-surface-variant,
.premium-bg-page .dashboard-content #coinAbout,
.premium-bg-page .dashboard-content #coinAbout p,
.premium-bg-page .dashboard-content #coinAbout .text-on-surface,
.premium-bg-page .dashboard-content #holdingsTab p,
.premium-bg-page .dashboard-content #assetsContainer .text-on-surface-variant {
    color: rgba(255, 255, 255, 0.85) !important;
}
.premium-bg-page .dashboard-content #coinAbout a.text-secondary {
    color: #b6c4ff !important;
}

/* Action buttons (Receive / Swap / Link Wallet / Liquidate) — solid, bright edge, lift shadow */
.premium-bg-page .dashboard-content .asset-action-btn {
    border: 1px solid rgba(255, 255, 255, 0.28) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.08) inset !important;
    transition: box-shadow 0.15s ease, transform 0.15s ease, opacity 0.15s ease;
}
.premium-bg-page .dashboard-content .asset-action-btn:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(255, 255, 255, 0.14) inset !important;
    transform: translateY(-1px);
}
.premium-bg-page .dashboard-content .asset-action-btn.bg-secondary {
    background: #115cb9 !important;
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .asset-action-btn.bg-primary {
    background: #041627 !important;
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .asset-action-btn.border-outline-variant,
.premium-bg-page .dashboard-content .asset-action-btn.bg-surface-container-lowest {
    background: #0a2036 !important;
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .asset-action-btn svg {
    stroke: #ffffff !important;
}
CSS;
}

/**
 * Softer premium background for auth pages (login): keeps gradient + landscape
 * without the heavy overlay of the full premium dark theme.
 */
function wt_premium_auth_bg_css(): string {
    return wt_premium_bg_css() . <<<'CSS'

.bg-premium-auth {
    background: linear-gradient(135deg, #1a2740 0%, #243449 48%, #152238 100%);
}
.bg-premium-auth::before {
    opacity: 0.18;
}
.bg-premium-auth::after {
    background:
        radial-gradient(circle at top right, rgba(64, 89, 170, 0.28) 0%, transparent 46%),
        radial-gradient(circle at bottom left, rgba(0, 35, 111, 0.16) 0%, transparent 46%),
        linear-gradient(135deg, rgba(26, 39, 64, 0.72) 0%, rgba(30, 45, 72, 0.76) 52%, rgba(18, 28, 48, 0.8) 100%);
}
.bg-premium-auth > main,
.bg-premium-auth > footer {
    position: relative;
    z-index: 1;
}
CSS;
}
