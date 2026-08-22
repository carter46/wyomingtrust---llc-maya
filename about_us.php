<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'About Us | ' . $site_name;
include 'includes/header.php';
?>
<div class="flex flex-col w-full">
<!-- Hero Section -->
<section class="w-full bg-surface-pure pt-16 pb-24 relative overflow-hidden flex flex-col items-center">
<!-- Subtle gradient background element for depth -->
<div class="absolute top-0 right-0 w-3/4 h-full bg-gradient-to-bl from-surface-container-low to-transparent mix-blend-multiply opacity-50 pointer-events-none rounded-bl-full"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 w-full relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-16 items-center mt-12">
<div class="lg:col-span-7 flex flex-col gap-8">
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm uppercase tracking-widest text-primary">Limited Liability, Limitless Potential ℠</span>
<h1 class="text-headline-xl font-headline-xl text-on-surface leading-[1.1]">
            Register Your Business in Wyoming
          </h1>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
            The premier state to register your business. Only $99 + State Filing Fees. Achieve total privacy, operational flexibility, and expert assistance from business formation specialists.
          </p>
</div>
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mt-4">
<a href="<?php echo escape_html(asset_url('onboarding/onboarding.php')); ?>" class="bg-secondary text-on-secondary px-8 py-4 rounded-full text-label-md font-label-md hover:brightness-110 transition-all shadow-md inline-flex items-center justify-center">Start Your Business</a>
<div class="flex flex-col gap-1">
<div class="flex items-center gap-1 text-secondary">
<span class="material-symbols-outlined fill-current text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined fill-current text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined fill-current text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined fill-current text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined fill-current text-[20px]" style="font-variation-settings: 'FILL' 1;">star_half</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface-variant">4.4/5 on Trustpilot (544 reviews)</span>
</div>
</div>
</div>
<div class="lg:col-span-5 relative">
<div class="w-full aspect-[4/5] bg-surface-container rounded-2xl shadow-xl overflow-hidden relative">
<img class="w-full h-full object-cover mix-blend-luminosity opacity-90" data-alt="A top-down view of a modern, organized wooden desk featuring a sleek black laptop, a premium leather notebook, and a brushed steel pen. The lighting is cinematic and natural, conveying a highly professional and trustworthy corporate environment." src="<?php echo escape_html(asset_url('Storage/images/about-workspace-dashboard.jpg')); ?>"/>
</div>
</div>
</div>
</section>
<!-- The Turn-Key LLC / Formation Package Section -->
<section class="w-full bg-surface py-section-gap-md relative">
<div class="max-w-7xl mx-auto px-6 lg:px-12 w-full">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
<div class="flex flex-col gap-4 max-w-3xl">
<h2 class="text-headline-lg font-headline-lg text-on-surface">The Turn-Key LLC</h2>
<p class="text-body-lg font-body-lg text-on-surface-variant">
            We provide everything you need to start your business with complete peace of mind. No hidden fees, no surprises – just professional, reliable service getting your business up and running in less than 24 hours.
          </p>
</div>
<div class="flex-shrink-0">
<div class="bg-primary-container text-on-primary-container px-6 py-4 rounded-xl shadow-sm flex flex-col items-center justify-center">
<span class="text-headline-md font-headline-md font-bold">$99</span>
<span class="text-label-sm font-label-sm opacity-80 uppercase tracking-wider">+ State Fees</span>
</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
<!-- List side -->
<div class="lg:col-span-2 flex flex-col">
<span class="text-label-sm font-label-sm text-outline uppercase tracking-wider mb-6 block">Complete Business Formation Package</span>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Registered Agent Service</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Professional representation for your business.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Free Mail Scanning</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">5 pieces included monthly.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Business Address</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Use our address for everything.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Operating Agreements</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Single & multi-member versions.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Meeting Minutes</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Organizational documentation included.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Articles of Organization</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Official state filing documents.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Certificate of Formation</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Proof of business existence.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div>
<h4 class="text-body-md font-bold text-on-surface">Bank Account Resolution</h4>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">Documentation for banking operations.</p>
</div>
</div>
</div>
</div>
<!-- Visual side -->
<div class="lg:col-span-1 bg-surface-subtle p-8 shadow-sm flex flex-col justify-center">
<div class="w-16 h-16 bg-primary flex items-center justify-center rounded-xl mb-6 shadow-md">
<span class="material-symbols-outlined text-on-primary text-[32px]">shield_person</span>
</div>
<h3 class="text-headline-md font-headline-md text-on-surface mb-4">Our Professional Promise</h3>
<p class="text-body-md font-body-md text-on-surface-variant leading-relaxed mb-8">
            Using our service ensures your personal information is protected with no risk of errors. We handle everything correctly the first time with no surprises.
          </p>
<div class="flex items-center gap-3 text-secondary font-label-md text-label-md">
<span class="material-symbols-outlined">schedule</span>
<span>24 hour guarantee for filing new companies.</span>
</div>
</div>
</div>
</div>
</section>
<!-- Benefits Section (Dark Theme) -->
<section class="w-full bg-inverse-surface py-section-gap-lg text-on-primary relative">
<div class="max-w-7xl mx-auto px-6 lg:px-12 w-full">
<div class="max-w-3xl mb-16">
<h2 class="text-headline-lg font-headline-lg text-on-primary mb-6">Wyoming LLC Benefits</h2>
<p class="text-body-lg font-body-lg opacity-80 leading-relaxed">
          Wyoming Limited Liability Companies offer the best combination of asset protection laws, privacy benefits, and cost savings. Perfect for online stores, real estate investors, and holding companies.
        </p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-16">
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">visibility_off</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">Private & Anonymous</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Owners and managers are not listed in public records. Your personal information remains completely private and secure.</p>
</div>
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">public</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">Simple Requirements</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Everything is handled online with no need to visit Wyoming. No residency requirements or wait times.</p>
</div>
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">savings</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">Low Annual Fees</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Starting at just $99 plus state fees. Minimal ongoing costs with only a $60 annual report required.</p>
</div>
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">account_balance</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">No State Taxes</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Wyoming has no state income tax, corporate tax, or franchise tax. Significant savings for all business owners.</p>
</div>
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">gavel</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">Asset Protection</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Strong charging order protection. Personal creditors cannot seize your LLC, and LLC creditors cannot seize personal assets.</p>
</div>
<div class="flex flex-col">
<span class="material-symbols-outlined text-secondary-fixed-dim text-[40px] mb-6">alt_route</span>
<h4 class="text-body-md font-bold text-on-primary mb-2">Operational Flexibility</h4>
<p class="text-body-md font-body-md opacity-70 leading-relaxed">Conduct business in all 50 states. No minimum capital required and flexible management structure options.</p>
</div>
</div>
</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>
