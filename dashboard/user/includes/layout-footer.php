<?php
$mobileNavClass = function ($key) use ($active_nav) {
    if (($active_nav ?? '') === $key) {
        return 'flex items-center gap-4 px-4 py-3 rounded-lg bg-primary text-on-primary';
    }
    return 'flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container';
};
$mobileFooterNavClass = function ($key) use ($active_nav) {
    if (($active_nav ?? '') === $key) {
        return 'flex items-center gap-4 px-4 py-3 rounded-lg bg-primary/10 text-primary';
    }
    return 'flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container';
};
?>
</div>
</main>
</div>
<div class="fixed inset-0 z-[60] pointer-events-none opacity-0 transition-opacity duration-300 md:hidden" id="mobile-nav">
<div class="absolute inset-0 bg-primary/20 backdrop-blur-sm" onclick="toggleMobileNav()"></div>
<div class="absolute inset-y-0 left-0 w-72 bg-surface-container-lowest shadow-2xl flex flex-col p-6 -translate-x-full transition-transform duration-300">
<div class="flex items-center justify-between mb-8">
<span class="font-headline-md text-headline-md font-bold text-primary">WyomingTrust</span>
<button type="button" class="p-2 hover:bg-surface-container rounded-full" onclick="toggleMobileNav()" aria-label="Close menu">
<?php echo wt_icon('close', 'w-6 h-6'); ?>
</button>
</div>
<nav class="flex-1 space-y-2 overflow-y-auto">
<a class="<?php echo $mobileNavClass('dashboard'); ?>" href="dashboard.php"><?php echo wt_icon('dashboard', 'w-5 h-5'); ?>Dashboard</a>
<a class="<?php echo $mobileNavClass('trusts'); ?>" href="manage-trust.php"><?php echo wt_icon('gavel', 'w-5 h-5'); ?>LLC Management</a>
<a class="<?php echo $mobileNavClass('create-trust'); ?>" href="../../onboarding/onboarding.php"><?php echo wt_icon('add-circle', 'w-5 h-5'); ?>Create New LLC</a>
<a class="<?php echo $mobileNavClass('beneficiaries'); ?>" href="beneficiaries.php"><?php echo wt_icon('group', 'w-5 h-5'); ?>Beneficiaries</a>
<a class="<?php echo $mobileNavClass('billing'); ?>" href="billing.php"><?php echo wt_icon('receipt-long', 'w-5 h-5'); ?>Billing</a>
<a class="<?php echo $mobileNavClass('support'); ?>" href="../../login.php"><?php echo wt_icon('help', 'w-5 h-5'); ?>Support</a>
</nav>
<div class="border-t border-outline-variant pt-4 mt-4 space-y-2">
<a class="<?php echo $mobileFooterNavClass('profile'); ?>" href="profile.php"><?php echo wt_icon('person', 'w-5 h-5'); ?>My Profile</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-lg text-error hover:bg-error-container/20" href="../../api/logout.php"><?php echo wt_icon('logout', 'w-5 h-5', '#ba1a1a'); ?>Logout</a>
</div>
</div>
</div>
<script>
function toggleMobileNav() {
    const nav = document.getElementById('mobile-nav');
    const panel = nav.querySelector('div:last-child');
    if (nav.classList.contains('opacity-0')) {
        nav.classList.replace('opacity-0', 'opacity-100');
        nav.classList.replace('pointer-events-none', 'pointer-events-auto');
        panel.classList.replace('-translate-x-full', 'translate-x-0');
    } else {
        nav.classList.replace('opacity-100', 'opacity-0');
        nav.classList.replace('pointer-events-auto', 'pointer-events-none');
        panel.classList.replace('translate-x-0', '-translate-x-full');
    }
}

(function () {
    const observed = new WeakSet();

    function fitDashboardAmount(el) {
        if (!el || !el.classList.contains('dashboard-metric-value')) return;
        const wrap = el.closest('.dashboard-metric-value-wrap') || el.parentElement;
        if (!wrap || wrap.clientWidth <= 0) return;
        const max = parseFloat(el.dataset.fitMax || '28') || 28;
        const min = parseFloat(el.dataset.fitMin || '10') || 10;
        el.style.fontSize = max + 'px';
        let size = max;
        while (el.scrollWidth > wrap.clientWidth && size > min) {
            size -= 0.5;
            el.style.fontSize = size + 'px';
        }
    }

    function attachMetricValue(el) {
        fitDashboardAmount(el);
        if (observed.has(el)) return;
        observed.add(el);
        const mo = new MutationObserver(function () { fitDashboardAmount(el); });
        mo.observe(el, { childList: true, characterData: true, subtree: true });
    }

    window.fitDashboardAmounts = function (root) {
        (root || document).querySelectorAll('.dashboard-metric-value').forEach(attachMetricValue);
    };

    function initDashboardAmountFit() {
        window.fitDashboardAmounts();
        const content = document.querySelector('.dashboard-content');
        if (content && typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(function () { window.fitDashboardAmounts(); });
            ro.observe(content);
        }
        window.addEventListener('resize', function () { window.fitDashboardAmounts(); });
        if (content) {
            new MutationObserver(function () { window.fitDashboardAmounts(); })
                .observe(content, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardAmountFit);
    } else {
        initDashboardAmountFit();
    }
})();
</script>
