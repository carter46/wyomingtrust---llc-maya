<?php
require_once __DIR__ . '/../api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$site_tagline = $site_settings['tagline'] ?? 'Secure Your Digital Legacy';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$nav_active = function ($page) use ($current_page) {
    return $current_page === $page
        ? 'text-primary font-bold'
        : 'text-on-surface-variant hover:text-primary transition-colors';
};
$login_href = asset_url('login.php');
$onboarding_href = asset_url('onboarding/onboarding.php');
$favicon_href = site_favicon_url();
$default_title = $site_name . ' | ' . $site_tagline;
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo isset($page_title) ? escape_html($page_title) : escape_html($default_title); ?></title>
<?php if ($favicon_href): ?>
<link rel="icon" href="<?php echo escape_html($favicon_href); ?>"/>
<?php endif; ?>
<style>
@layer base {
  html, body { margin: 0; padding: 0; }
  body { overscroll-behavior: none; }
  main > :first-child { margin-top: 0 !important; }
  main > :last-child { margin-bottom: 0 !important; }
}
::-webkit-scrollbar { display: none; }
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  vertical-align: middle;
}
.wt-icon { display: inline-block; vertical-align: middle; flex-shrink: 0; width: 1.25rem; height: 1.25rem; }
</style>
<script src="https://cdn.tailwindcss.com"></script>
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "surface-subtle": "#F3F4F6",
        "surface-container-highest": "#d9e3f7",
        "on-surface": "#121c2a",
        "inverse-surface": "#273140",
        "tertiary": "#4b1c00",
        "on-surface-variant": "#444651",
        "on-primary": "#ffffff",
        "surface-bright": "#f9f9ff",
        "tertiary-fixed-dim": "#ffb691",
        "on-tertiary-fixed": "#341100",
        "surface-container-high": "#dee9fd",
        "surface": "#f9f9ff",
        "on-secondary-fixed": "#341100",
        "on-secondary-container": "#5c2400",
        "primary-fixed-dim": "#b6c4ff",
        "on-background": "#121c2a",
        "surface-variant": "#d9e3f7",
        "secondary-container": "#fd761a",
        "surface-tint": "#4059aa",
        "on-error": "#ffffff",
        "surface-pure": "#FFFFFF",
        "error": "#ba1a1a",
        "on-primary-container": "#90a8ff",
        "surface-container": "#e6eeff",
        "secondary-fixed": "#ffdbca",
        "on-error-container": "#93000a",
        "outline-variant": "#c5c5d3",
        "surface-dim": "#d0daef",
        "primary": "#00236f",
        "outline": "#757682",
        "on-tertiary-fixed-variant": "#773205",
        "ink-muted": "#6B7280",
        "surface-container-low": "#eff3ff",
        "background": "#f9f9ff",
        "tertiary-fixed": "#ffdbcb",
        "primary-container": "#1e3a8a",
        "on-primary-fixed-variant": "#264191",
        "on-primary-fixed": "#00164e",
        "error-container": "#ffdad6",
        "on-secondary-fixed-variant": "#783200",
        "primary-fixed": "#dce1ff",
        "surface-container-lowest": "#ffffff",
        "on-secondary": "#ffffff",
        "on-tertiary": "#ffffff",
        "inverse-primary": "#b6c4ff",
        "on-tertiary-container": "#f39461",
        "ink-dark": "#111827",
        "secondary": "#9d4300",
        "secondary-fixed-dim": "#ffb690",
        "tertiary-container": "#6e2c00",
        "inverse-on-surface": "#ebf1ff"
      },
      borderRadius: {
        DEFAULT: "0.125rem",
        lg: "0.25rem",
        xl: "0.5rem",
        full: "0.75rem"
      },
      spacing: {
        "grid-margin": "2rem",
        "section-gap-lg": "8rem",
        "stack-sm": "0.5rem",
        "gutter": "1.5rem",
        "section-gap-md": "4rem",
        "stack-md": "1rem"
      },
      fontFamily: {
        "body-md": ["Inter"],
        "body-lg": ["Inter"],
        "headline-md": ["Hanken Grotesk"],
        "label-sm": ["Inter"],
        "headline-xl": ["Hanken Grotesk"],
        "headline-lg": ["Hanken Grotesk"],
        "headline-lg-mobile": ["Hanken Grotesk"],
        "label-md": ["Inter"]
      },
      fontSize: {
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "600" }],
        "headline-xl": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "600" }],
        "headline-lg-mobile": ["28px", { lineHeight: "36px", fontWeight: "600" }],
        "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "500" }]
      }
    }
  }
};
</script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface">
<header class="fixed top-0 w-full z-50 bg-surface-pure/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
<div class="h-20 max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">
<div class="flex items-center gap-12">
<?php
$logo_class = 'flex items-center gap-2 group';
$logo_text_class = 'font-headline-md text-headline-md text-primary tracking-tight';
$logo_img_class = 'h-14 w-auto max-w-[240px] object-contain';
include __DIR__ . '/components/site-logo.php';
?>
<nav class="hidden lg:flex items-center gap-8">
<div class="relative group">
<button type="button" class="flex items-center gap-1 text-label-md font-label-md text-on-surface-variant hover:text-primary transition-colors py-8">
Services
<span class="material-symbols-outlined text-[16px]">expand_more</span>
</button>
<div class="absolute left-0 top-full w-[480px] bg-surface-pure shadow-xl rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-outline-variant/30 p-6">
<div class="grid grid-cols-2 gap-6">
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm text-outline uppercase tracking-wider">Formation</span>
<a class="group/link" href="<?php echo escape_html(asset_url('about_us.php')); ?>">
<p class="text-body-md font-bold text-on-surface group-hover/link:text-primary transition-colors">LLC Formation</p>
<p class="text-label-sm text-on-surface-variant">Start your business in Wyoming</p>
</a>
<a class="group/link" href="<?php echo escape_html($login_href); ?>">
<p class="text-body-md font-bold text-on-surface group-hover/link:text-primary transition-colors">Registered Agent</p>
<p class="text-label-sm text-on-surface-variant">Compliance and privacy</p>
</a>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm text-outline uppercase tracking-wider">Operations</span>
<a class="group/link" href="<?php echo escape_html($login_href); ?>">
<p class="text-body-md font-bold text-on-surface group-hover/link:text-primary transition-colors">Virtual Office</p>
<p class="text-label-sm text-on-surface-variant">Premium physical address</p>
</a>
<a class="group/link" href="<?php echo escape_html($login_href); ?>">
<p class="text-body-md font-bold text-on-surface group-hover/link:text-primary transition-colors">Tax ID (EIN)</p>
<p class="text-label-sm text-on-surface-variant">Federal tax registration</p>
</a>
</div>
</div>
</div>
</div>
<a class="text-label-md font-label-md <?php echo $nav_active('why_us'); ?>" href="<?php echo escape_html(asset_url('why_us.php')); ?>">Why Us</a>
<a class="text-label-md font-label-md text-on-surface-variant hover:text-primary transition-colors" href="<?php echo escape_html($login_href); ?>">Resources</a>
<a class="text-label-md font-label-md <?php echo $nav_active('pricing'); ?>" href="<?php echo escape_html(asset_url('pricing.php')); ?>">Present</a>
<a class="text-label-md font-label-md <?php echo $nav_active('about_us'); ?>" href="<?php echo escape_html(asset_url('about_us.php')); ?>">About</a>
</nav>
</div>
<div class="flex items-center gap-6">
<?php if (isset($_SESSION['user_id'])): ?>
<a class="hidden md:block text-label-md font-label-md text-on-surface-variant hover:text-primary transition-colors" href="<?php echo escape_html(asset_url('dashboard/user/dashboard.php')); ?>">Dashboard</a>
<?php else: ?>
<a class="hidden md:block text-label-md font-label-md <?php echo $nav_active('login'); ?>" href="<?php echo escape_html($login_href); ?>">Log In</a>
<?php endif; ?>
<a href="<?php echo escape_html($onboarding_href); ?>" class="hidden md:inline-flex bg-secondary text-on-secondary px-6 py-3 rounded-full text-label-md font-label-md hover:brightness-110 transition-all shadow-sm">Start Your LLC</a>
<button id="mobileMenuBtn" type="button" class="lg:hidden text-primary p-2" onclick="toggleMobileMenu()" aria-label="Toggle menu">
<span class="material-symbols-outlined text-[28px]">menu</span>
</button>
</div>
</div>
<div id="mobileMenu" class="hidden lg:hidden border-t border-outline-variant/30 bg-surface-pure max-h-[calc(100vh-5rem)] overflow-y-auto">
<div class="px-6 py-4 max-w-7xl mx-auto flex flex-col gap-1">
<span class="font-label-sm text-label-sm text-outline uppercase tracking-widest px-2 py-2">Services</span>
<a href="<?php echo escape_html(asset_url('about_us.php')); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">LLC Formation</a>
<a href="<?php echo escape_html($login_href); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Registered Agent</a>
<a href="<?php echo escape_html($login_href); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Virtual Office</a>
<a href="<?php echo escape_html($login_href); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Tax ID (EIN)</a>
<div class="border-t border-outline-variant/30 my-2"></div>
<a href="<?php echo escape_html(asset_url('why_us.php')); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Why Us</a>
<a href="<?php echo escape_html($login_href); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Resources</a>
<a href="<?php echo escape_html(asset_url('pricing.php')); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">Present</a>
<a href="<?php echo escape_html(asset_url('about_us.php')); ?>" class="px-4 py-2 text-sm text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-low transition-colors">About</a>
<div class="border-t border-outline-variant/30 my-2 pt-2 flex flex-col gap-2">
<?php if (isset($_SESSION['user_id'])): ?>
<a href="<?php echo escape_html(asset_url('dashboard/user/dashboard.php')); ?>" class="text-center px-4 py-2.5 rounded-full border-2 border-primary text-primary font-label-md font-bold">Dashboard</a>
<?php else: ?>
<a href="<?php echo escape_html($login_href); ?>" class="text-center px-4 py-2.5 rounded-full border-2 border-primary text-primary font-label-md font-bold">Log In</a>
<?php endif; ?>
<a href="<?php echo escape_html($onboarding_href); ?>" class="text-center bg-secondary text-on-secondary px-4 py-2.5 rounded-full font-bold">Start Your LLC</a>
</div>
</div>
</div>
</header>
<script>
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  const btn = document.getElementById('mobileMenuBtn');
  const icon = btn.querySelector('.material-symbols-outlined');
  if (menu.classList.contains('hidden')) {
    menu.classList.remove('hidden');
    icon.textContent = 'close';
  } else {
    menu.classList.add('hidden');
    icon.textContent = 'menu';
  }
}
</script>
<main class="pt-20">
