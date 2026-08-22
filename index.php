<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = $site_name . ' | Register Your Business in Wyoming';
include 'includes/header.php';
?>
<div class="flex flex-col w-full">
<!-- Hero Section -->
<section class="relative w-full min-h-[90vh] flex items-center bg-surface overflow-hidden">
<!-- Subtle Background Elements -->
<div class="absolute inset-0 z-0 bg-gradient-to-br from-surface via-surface to-surface-container-low/50"></div>
<div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-fixed-dim/20 rounded-full blur-[100px] opacity-50 mix-blend-multiply"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 relative z-10 items-center">
<!-- Text Content -->
<div class="lg:col-span-6 flex flex-col gap-6 lg:gap-8 pt-12 lg:pt-0">
<div class="flex items-center gap-3">
<div class="w-2 h-2 rounded-full bg-secondary-container animate-pulse"></div>
<span class="text-label-sm font-label-sm uppercase tracking-[0.1em] text-primary">The Premier Jurisdiction</span>
</div>
<h1 class="text-headline-lg-mobile lg:text-headline-xl font-headline-xl text-on-surface text-balance leading-tight">
          Register Your Business in Wyoming — <br/>
<span class="text-on-surface-variant opacity-80">Asset Protection & Privacy.</span>
</h1>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-lg leading-relaxed">
          Form your LLC for just $99 + state fees. Attorney-built systems for serious entrepreneurs who demand total anonymity and structural integrity.
        </p>
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-4">
<a href="<?php echo escape_html(asset_url('onboarding/onboarding.php')); ?>" class="w-full sm:w-auto bg-primary text-on-primary px-8 py-4 rounded-full text-label-md font-label-md hover:bg-primary-container transition-colors shadow-sm flex items-center justify-center gap-2 group">Start Your LLC<span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span></a>
<a href="<?php echo escape_html(asset_url('pricing.php')); ?>" class="w-full sm:w-auto bg-transparent text-primary border border-primary px-8 py-4 rounded-full text-label-md font-label-md hover:bg-primary/5 transition-colors flex items-center justify-center">View Pricing</a>
</div>
<div class="flex items-center gap-6 mt-8 pt-8 border-t border-outline-variant/30">
<div class="flex flex-col">
<span class="text-headline-md font-headline-md text-on-surface">100k+</span>
<span class="text-label-sm font-label-sm text-outline uppercase tracking-wider">Businesses Formed</span>
</div>
<div class="w-px h-12 bg-outline-variant/30"></div>
<div class="flex flex-col">
<div class="flex items-center gap-1 text-secondary-container">
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[16px]">star_half</span>
</div>
<span class="text-label-sm font-label-sm text-outline uppercase tracking-wider mt-1">4.4/5 Trustpilot Rating</span>
</div>
</div>
</div>
<!-- High-End Image -->
<div class="lg:col-span-6 relative flex justify-center lg:justify-end">
<div class="relative w-full max-w-lg aspect-[4/5] rounded-xl overflow-hidden shadow-xl bg-surface-container">
<img alt="Modern architectural office overlooking mountains, representing Wyoming LLC asset protection" class="w-full h-full object-cover" src="<?php echo escape_html(asset_url('Storage/images/wyoming-business-landscape.jpg')); ?>"/>
<div class="absolute inset-0 border border-on-surface/10 rounded-xl mix-blend-overlay pointer-events-none"></div>
</div>
<!-- Floating credibility tag -->
<div class="absolute -bottom-6 -left-6 lg:left-auto lg:-right-12 bg-surface-pure p-6 rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.06)] border border-outline-variant/20 max-w-[240px] flex items-start gap-4">
<div class="w-10 h-10 bg-primary-container rounded-full flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-primary text-[20px]">gavel</span>
</div>
<div class="flex flex-col">
<span class="text-label-md font-label-md text-on-surface font-bold">Attorney-Led Compliance</span>
<span class="text-label-sm text-on-surface-variant mt-1 leading-snug">Systems built by legal professionals.</span>
</div>
</div>
</div>
</div>
</section>
<!-- Services Overview -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-pure relative">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 lg:grid-cols-4 gap-12 border-t border-outline-variant/30 pt-16">
<div class="lg:col-span-1">
<h2 class="text-headline-md font-headline-md text-on-surface mb-4">Core Services</h2>
<p class="text-body-md font-body-md text-on-surface-variant">Everything you need to launch and protect your business with complete peace of mind.</p>
</div>
<div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-8">
<!-- Service 1 -->
<div class="flex flex-col p-8 rounded-xl bg-surface transition-all duration-300 hover:shadow-md border border-transparent hover:border-outline-variant/30 group">
<span class="material-symbols-outlined text-primary text-[32px] mb-6">domain_add</span>
<h3 class="text-body-lg font-bold text-on-surface mb-2 group-hover:text-primary transition-colors">Turn-Key Formation</h3>
<p class="text-body-md text-on-surface-variant mb-6">Complete Wyoming LLC formation for $99 + state fees. Includes operating agreement and certificate of formation.</p>
<div class="mt-auto">
<a class="text-label-md font-label-md text-primary flex items-center gap-1 group-hover:underline" href="<?php echo escape_html(asset_url('about_us.php')); ?>">Explore Formation <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
</div>
<!-- Service 2 -->
<div class="flex flex-col p-8 rounded-xl bg-surface transition-all duration-300 hover:shadow-md border border-transparent hover:border-outline-variant/30 group">
<span class="material-symbols-outlined text-primary text-[32px] mb-6">mark_email_read</span>
<h3 class="text-body-lg font-bold text-on-surface mb-2 group-hover:text-primary transition-colors">Global Mail Management</h3>
<p class="text-body-md text-on-surface-variant mb-6">Premium physical address in Wyoming with 5 free mail scans monthly. Professional representation for your business.</p>
<div class="mt-auto">
<a class="text-label-md font-label-md text-primary flex items-center gap-1 group-hover:underline" href="<?php echo escape_html(asset_url('login.php')); ?>">View Virtual Office <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Process Section -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-inverse-surface text-on-primary">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
<div>
<span class="text-label-sm font-label-sm text-primary-fixed uppercase tracking-[0.1em] mb-2 block">Methodology</span>
<h2 class="text-headline-lg font-headline-lg">The 4-Step Formation Protocol</h2>
</div>
<p class="text-body-md max-w-md opacity-80">A streamlined, highly secure process designed to establish your entity in under 24 hours.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
<!-- Connecting Line (Desktop) -->
<div class="hidden md:block absolute top-6 left-6 right-6 h-px bg-outline-variant/20 z-0"></div>
<!-- Step 1 -->
<div class="relative z-10 flex flex-col gap-4 group cursor-default">
<div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center border-4 border-inverse-surface text-label-md font-bold transition-transform group-hover:scale-110">1</div>
<h3 class="text-body-lg font-bold">Select Structure</h3>
<p class="text-body-md opacity-70">Choose between Single or Multi-member LLC configurations tailored to your needs.</p>
</div>
<!-- Step 2 -->
<div class="relative z-10 flex flex-col gap-4 group cursor-default">
<div class="w-12 h-12 rounded-full bg-surface-container-highest text-on-surface flex items-center justify-center border-4 border-inverse-surface text-label-md font-bold transition-transform group-hover:scale-110">2</div>
<h3 class="text-body-lg font-bold">Submit Data</h3>
<p class="text-body-md opacity-70">Complete our secure, encrypted online form in under 10 minutes.</p>
</div>
<!-- Step 3 -->
<div class="relative z-10 flex flex-col gap-4 group cursor-default">
<div class="w-12 h-12 rounded-full bg-surface-container-highest text-on-surface flex items-center justify-center border-4 border-inverse-surface text-label-md font-bold transition-transform group-hover:scale-110">3</div>
<h3 class="text-body-lg font-bold">Attorney Filing</h3>
<p class="text-body-md opacity-70">Our team executes the official state filings with precision and zero errors.</p>
</div>
<!-- Step 4 -->
<div class="relative z-10 flex flex-col gap-4 group cursor-default">
<div class="w-12 h-12 rounded-full bg-surface-container-highest text-on-surface flex items-center justify-center border-4 border-inverse-surface text-label-md font-bold transition-transform group-hover:scale-110">4</div>
<h3 class="text-body-lg font-bold">Receive Documents</h3>
<p class="text-body-md opacity-70">Instantly access your Operations Manual, Articles, and Bank Resolution.</p>
</div>
</div>
</div>
</section>
<!-- Testimonial & Trust Section -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-container-low overflow-hidden">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
<!-- Testimonial -->
<div class="flex flex-col gap-8 relative">
<span class="material-symbols-outlined text-[80px] text-primary/10 absolute -top-8 -left-8 -z-10" style="font-variation-settings: 'FILL' 1;">format_quote</span>
<blockquote class="text-headline-md font-headline-md text-on-surface leading-relaxed">
            "Their team has been <span class="text-primary">fantastic to work with</span>, providing excellent service as a Registered Agent. Their online platform is user-friendly, making it easy to keep all company and personal information up-to-date. They ensure maintaining a company 'in good standing' is efficient and hassle-free. Overall, a great experience."
          </blockquote>
<div class="flex items-center gap-4 mt-4">
<div class="w-16 h-16 rounded-full overflow-hidden border-2 border-surface-pure shadow-sm">
<img alt="Professional headshot of Lead Counsel" class="w-full h-full object-cover" src="<?php echo escape_html(asset_url('Storage/images/retirement_3.jpg')); ?>"/>
</div>
<div class="flex flex-col">
<span class="text-label-md font-bold text-on-surface">Robert V.</span>
<span class="text-label-sm text-on-surface-variant flex items-center gap-2">
<span class="material-symbols-outlined text-[14px] text-secondary-container" style="font-variation-settings: 'FILL' 1;">star</span>
                5/5 Rating • Jun 27, 2023
              </span>
</div>
</div>
</div>
<!-- Trust Data -->
<div class="bg-surface-pure rounded-xl p-8 lg:p-12 shadow-sm border border-outline-variant/20 flex flex-col gap-8">
<div>
<h3 class="text-headline-md font-headline-md text-on-surface mb-2">Why Form in Wyoming?</h3>
<p class="text-body-md text-on-surface-variant">The premier jurisdiction offering unparalleled advantages.</p>
</div>
<div class="flex flex-col gap-6">
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1">visibility_off</span>
<div>
<span class="text-label-md font-bold text-on-surface block mb-1">100% Anonymity</span>
<span class="text-body-md text-on-surface-variant block">Owners and managers are not listed in public records, ensuring total privacy.</span>
</div>
</div>
<div class="w-full h-px bg-outline-variant/20"></div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1">shield</span>
<div>
<span class="text-label-md font-bold text-on-surface block mb-1">Asset Protection</span>
<span class="text-body-md text-on-surface-variant block">Strong charging order protection insulates your personal assets from business liabilities.</span>
</div>
</div>
<div class="w-full h-px bg-outline-variant/20"></div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary mt-1">money_off</span>
<div>
<span class="text-label-md font-bold text-on-surface block mb-1">No State Taxes</span>
<span class="text-body-md text-on-surface-variant block">Zero state income tax, corporate tax, or franchise tax.</span>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- Why Choose section -->
<section class="py-section-gap-md lg:py-section-gap-lg px-6 lg:px-12 bg-inverse-surface text-on-primary">
<div class="max-w-7xl mx-auto">
<div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-center">
<div class="w-full lg:w-1/2">
<h2 class="font-headline-lg text-headline-lg mb-6 lg:mb-8"><span class="text-secondary-fixed-dim">&#9650;</span> Why Choose <?php echo escape_html($site_name); ?>?</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-8 lg:mb-10">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">Complete privacy &amp; anonymity for members.</p>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">No state income, corporate, or franchise tax.</p>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">Registered agent service included.</p>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">Operating agreement &amp; formation docs.</p>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">Same-day filing available.</p>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-secondary-fixed-dim flex-shrink-0">check_circle</span>
<p class="text-sm font-medium text-on-primary/80">Strong charging-order asset protection.</p>
</div>
</div>
<a href="<?php echo escape_html(asset_url('onboarding/onboarding.php')); ?>" class="inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary px-6 py-3 rounded-full font-bold hover:brightness-110 transition-all w-full sm:w-auto">
<span class="material-symbols-outlined text-[20px]">domain_add</span>
<span>Start Your LLC</span>
</a>
</div>
<div class="w-full lg:w-1/2">
<div class="aspect-video bg-primary-container rounded-2xl border border-on-primary/10 overflow-hidden shadow-2xl">
<iframe
    class="w-full h-full"
    src="https://www.youtube-nocookie.com/embed/DWlkooXW3p0?rel=0&modestbranding=1"
    title="<?php echo escape_html($site_name); ?> Video"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    allowfullscreen></iframe>
</div>
</div>
</div>
</div>
</section>

</div>

<?php include 'includes/footer.php'; ?>