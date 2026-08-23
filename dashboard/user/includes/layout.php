<?php
/**
 * User dashboard shell — Heritage Modern layout.
 * Expects: $page_title, $userName; optional: $active_nav (dashboard|trusts|crypto-assets|billing|support|profile)
 */
$active_nav = $active_nav ?? '';
$userName = $userName ?? ($_SESSION['user_name'] ?? 'User');
$userInitials = 'WT';
$nameParts = preg_split('/\s+/', trim($userName));
if (count($nameParts) >= 2) {
    $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
} elseif ($userName !== '') {
    $userInitials = strtoupper(substr($userName, 0, 2));
}

$navClass = function ($key) use ($active_nav) {
    if ($active_nav === $key) {
        return 'flex items-center gap-3 px-4 py-3 rounded-lg sidebar-active transition-all duration-200';
    }
    return 'flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container transition-all';
};
$footerNavClass = function ($key) use ($active_nav) {
    if ($active_nav === $key) {
        return 'flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium transition-all';
    }
    return 'flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container transition-all';
};
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo escape_html($page_title ?? 'WyomingTrust Dashboard'); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap" rel="stylesheet"/>
<script src="<?php echo escape_html(wt_icon_script_url()); ?>"></script>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "surface-container": "#edeeef",
                "deep-forest": "#2D4B3F",
                "primary": "#041627",
                "on-primary": "#ffffff",
                "sky-accent": "#B6D6F2",
                "secondary": "#115cb9",
                "surface": "#f8f9fa",
                "on-surface-variant": "#44474c",
                "on-secondary": "#ffffff",
                "warm-cream": "#FEFDF3",
                "outline-variant": "#c4c6cd",
                "outline": "#74777d",
                "on-surface": "#191c1d",
                "surface-container-low": "#f3f4f5",
                "surface-container-lowest": "#ffffff",
                "secondary-container": "#659dfe",
                "on-secondary-container": "#003370",
                "primary-container": "#1a2b3c",
                "on-primary-container": "#8192a7",
                "error": "#ba1a1a",
                "error-container": "#ffdad6",
            },
            borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
            spacing: {
                "container-max": "1200px",
                "gutter": "24px",
                "section-padding-md": "48px",
                "section-padding-lg": "80px",
                "stack-gap": "16px",
            },
            maxWidth: { "container-max": "1200px" },
            fontFamily: {
                "body-md": ["DM Sans", "sans-serif"],
                "label-sm": ["DM Sans", "sans-serif"],
                "label-md": ["DM Sans", "sans-serif"],
                "headline-lg": ["\"Source Serif 4\"", "serif"],
                "headline-md": ["\"Source Serif 4\"", "serif"],
                "body-lg": ["DM Sans", "sans-serif"],
                "display-lg": ["\"Source Serif 4\"", "serif"],
            },
            fontSize: {
                "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                "label-sm": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "700" }],
                "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "500" }],
                "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }],
                "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
            },
        },
    },
};
</script>
<style>
.sidebar-active { background-color: #041627; color: #ffffff; box-shadow: 0 10px 15px -3px rgba(4, 22, 39, 0.1); }
.sidebar-active .wt-icon { stroke: #ffffff; }
.glass-effect { backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.85); }
.card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(4, 22, 39, 0.1), 0 10px 10px -5px rgba(4, 22, 39, 0.04);
}
.metric-card-gradient {
    background: linear-gradient(135deg, #041627 0%, #0a2540 45%, #115cb9 100%);
}
.metric-stat-value { font-size: 1.875rem; line-height: 1.2; font-weight: 700; }
@media (min-width: 768px) {
    .metric-stat-value { font-size: 2.25rem; }
}
.dashboard-metric-card { min-width: 0; overflow: hidden; }
.dashboard-metric-value-wrap { min-width: 0; width: 100%; max-width: 100%; overflow: hidden; }
.dashboard-metric-value {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    line-height: 1.15;
    font-weight: 700;
    white-space: nowrap;
    font-size: 1.75rem;
}
.dashboard-metric-value.font-headline-lg { font-family: "Source Serif 4", serif; }
.wt-icon { display: inline-block; vertical-align: middle; flex-shrink: 0; width: 1.25rem; height: 1.25rem; }
.dashboard-shell { width: 100%; max-width: 100vw; overflow-x: hidden; }
.dashboard-main {
    min-width: 0;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
@media (min-width: 768px) {
    .dashboard-main {
        margin-left: 18rem;
        width: calc(100% - 18rem);
        max-width: calc(100vw - 18rem);
    }
}
.dashboard-content {
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #d9dadb; border-radius: 10px; }
<?php if (!empty($extra_styles)): ?>
<?php echo $extra_styles; ?>
<?php endif; ?>
</style>
<?php if (!empty($extra_head)): ?>
<?php echo $extra_head; ?>
<?php endif; ?>
</head>
<body class="bg-surface font-body-md text-on-surface antialiased overflow-x-hidden">
<div class="dashboard-shell flex min-h-screen">
<aside class="hidden md:flex flex-col w-72 fixed h-full bg-surface-container-lowest border-r border-outline-variant z-50">
<div class="p-gutter h-20 flex items-center gap-2.5">
<span class="flex-shrink-0 w-9 h-9 rounded-lg bg-[#16a34a] flex items-center justify-center shadow-sm" aria-hidden="true">
<svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12 2.25L4.5 5.75V11.25C4.5 16.04 7.73 20.36 12 21.75C16.27 20.36 19.5 16.04 19.5 11.25V5.75L12 2.25Z" fill="white"/>
<path d="M9.75 12.25L11.1 13.85L14.55 10.1" stroke="#16a34a" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span>
<a href="dashboard.php" class="font-headline-md text-headline-md font-bold text-primary tracking-tight">WyomingTrust</a>
</div>
<nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
<a class="<?php echo $navClass('dashboard'); ?>" href="dashboard.php">
<?php echo wt_icon('dashboard', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">Dashboard</span>
</a>
<a class="<?php echo $navClass('trusts'); ?>" href="manage-trust.php">
<?php echo wt_icon('gavel', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">LLC Management</span>
</a>
<a class="<?php echo $navClass('crypto-assets'); ?>" href="assets.php">
<?php echo wt_icon('wallet', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">Crypto Assets</span>
</a>
<a class="<?php echo $navClass('billing'); ?>" href="billing.php">
<?php echo wt_icon('receipt-long', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">Billing</span>
</a>
<a class="<?php echo $navClass('support'); ?>" href="../../login.php">
<?php echo wt_icon('help', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">Support</span>
</a>
</nav>
<div class="p-6 border-t border-outline-variant">
<div class="flex items-center gap-4 mb-4">
<div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold text-sm"><?php echo escape_html($userInitials); ?></div>
<div class="min-w-0">
<p class="font-label-md text-label-md font-bold truncate"><?php echo escape_html($userName); ?></p>
<p class="text-xs text-secondary">Member</p>
</div>
</div>
<div class="border-t border-outline-variant pt-4 space-y-1">
<a class="<?php echo $footerNavClass('profile'); ?>" href="profile.php">
<?php echo wt_icon('person', 'w-5 h-5'); ?>
<span class="font-label-md text-label-md">My Profile</span>
</a>
<a href="../../api/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-error font-label-md text-label-md hover:bg-error-container/20 transition-colors">
<?php echo wt_icon('logout', 'w-5 h-5', '#ba1a1a'); ?>
Logout
</a>
</div>
</div>
</aside>
<main class="dashboard-main min-h-screen flex flex-col">
<header class="h-20 glass-effect sticky top-0 z-40 flex items-center justify-between px-gutter md:px-12 border-b border-outline-variant/30 min-w-0 shrink-0">
<div class="flex items-center flex-1 max-w-xl">
<button type="button" class="md:hidden mr-4 p-2 hover:bg-surface-container rounded-full" onclick="toggleMobileNav()" aria-label="Open menu">
<?php echo wt_icon('menu', 'w-6 h-6'); ?>
</button>
<div class="relative w-full max-w-md hidden sm:block">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none"><?php echo wt_icon('search', 'w-5 h-5'); ?></span>
<input class="w-full pl-11 pr-4 py-2 bg-surface-container-low border-none rounded-full focus:ring-2 focus:ring-secondary/50 font-body-md text-sm" placeholder="Search LLCs or share holders..." type="search"/>
</div>
</div>
<div class="flex items-center gap-2 md:gap-6">
<button type="button" class="relative p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors" aria-label="Notifications">
<?php echo wt_icon('notifications', 'w-6 h-6'); ?>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
</button>
<button type="button" onclick="window.location.href='profile.php'" class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors" aria-label="Settings">
<?php echo wt_icon('settings', 'w-6 h-6'); ?>
</button>
<div class="h-8 w-px bg-outline-variant hidden md:block"></div>
<div class="hidden md:flex items-center gap-3 bg-surface-container px-4 py-1.5 rounded-full">
<span class="font-label-md text-label-md font-medium">Member</span>
<div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center">
<?php echo wt_icon('star', 'w-3.5 h-3.5', '#ffffff'); ?>
</div>
</div>
</div>
</header>
<div class="dashboard-content p-gutter md:p-12 space-y-10 max-w-container-max mx-auto w-full flex-1">
