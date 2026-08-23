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
CSS;
}
