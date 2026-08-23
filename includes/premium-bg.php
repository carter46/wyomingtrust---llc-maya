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
.premium-bg-page aside.dashboard-sidebar {
    background: rgba(18, 28, 42, 0.92);
    border-color: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.premium-bg-page aside.dashboard-sidebar .font-headline-md,
.premium-bg-page aside.dashboard-sidebar .font-label-md:not(.sidebar-active *) {
    color: rgba(255, 255, 255, 0.9);
}
.premium-bg-page aside.dashboard-sidebar a:not(.sidebar-active) {
    color: rgba(255, 255, 255, 0.72);
}
.premium-bg-page aside.dashboard-sidebar a:not(.sidebar-active):hover {
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
}
.premium-bg-page aside.dashboard-sidebar .text-secondary {
    color: #b6c4ff;
}
.premium-bg-page aside.dashboard-sidebar .border-outline-variant,
.premium-bg-page aside.dashboard-sidebar .border-t {
    border-color: rgba(255, 255, 255, 0.1);
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
.premium-bg-page #mobile-nav .bg-surface-container-lowest {
    background: rgba(18, 28, 42, 0.98);
    color: #ffffff;
}
.premium-bg-page #mobile-nav a:not(.bg-primary):not(.text-primary) {
    color: rgba(255, 255, 255, 0.85);
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

/* --- Crypto pages: navy gradient cards + white text --- */
.premium-bg-page .dashboard-content {
    color: rgba(255, 255, 255, 0.92);
}
.premium-bg-page .dashboard-content .dashboard-metric-card,
.premium-bg-page .dashboard-content .card-shadow,
.premium-bg-page .dashboard-content section > .overflow-hidden.border.rounded-2xl.bg-surface-container-lowest,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.card-shadow,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.shadow-sm,
.premium-bg-page .dashboard-content .bg-surface-container-lowest.rounded-2xl.border.border-outline-variant.overflow-hidden {
    background: linear-gradient(135deg, #041627 0%, #0a2540 42%, #115cb9 100%) !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
    box-shadow: 0 8px 32px rgba(4, 22, 39, 0.35);
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
.premium-bg-page .dashboard-content .font-headline-md.text-primary,
.premium-bg-page .dashboard-content h3.text-primary {
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .font-headline-lg.text-primary,
.premium-bg-page .dashboard-content h1.text-primary {
    color: #ffffff !important;
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
.premium-bg-page .dashboard-content button.bg-surface-container-low.text-on-surface {
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.18);
}
.premium-bg-page .dashboard-content #refreshPrices svg {
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
.premium-bg-page .dashboard-content .asset-action-btn.border-outline-variant {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
}
.premium-bg-page .dashboard-content .asset-action-btn.border-outline-variant svg {
    stroke: #ffffff !important;
}
CSS;
}
