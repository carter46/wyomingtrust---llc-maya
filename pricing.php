<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Present | ' . $site_name;
include 'includes/header.php';
?>
<div class="flex flex-col w-full">
<!-- Typographic Hero -->
<section class="w-full bg-surface pt-32 pb-20 relative overflow-hidden">
<div class="absolute top-0 right-0 w-[800px] h-[800px] bg-primary-fixed-dim/20 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 opacity-60"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
<div class="flex flex-col gap-6 max-w-4xl">
<span class="font-label-sm text-label-sm text-primary tracking-widest uppercase">Pricing &amp; Packages</span>
<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight">
          Professional formation.<br/>
<span class="text-surface-tint">Transparent pricing.</span>
</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
          We provide everything you need to start your business in Wyoming with complete peace of mind. Get trusted, attorney-built systems without the law firm price tag.
        </p>
</div>
</div>
</section>
<!-- Pricing Core Architecture -->
<section class="w-full bg-surface pb-section-gap-lg relative z-20">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
<!-- The $99 Offer -->
<div class="lg:col-span-7 bg-surface-container-lowest shadow-xl rounded-2xl p-8 lg:p-16 flex flex-col justify-between relative overflow-hidden group">
<div class="absolute top-0 right-0 w-64 h-64 bg-surface-container-highest rounded-full blur-[80px] opacity-30 group-hover:scale-110 transition-transform duration-700"></div>
<div class="relative z-10">
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">File Your LLC or Corporation</h2>
<p class="font-body-md text-body-md text-on-surface-variant mb-12">for Just $99 + State Fees.</p>
<div class="flex items-baseline gap-2 mb-12">
<span class="font-headline-xl text-[72px] leading-[72px] font-bold text-on-surface tracking-tighter">$99</span>
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wide">Our Service Fee</span>
</div>
<a href="<?php echo escape_html(asset_url('onboarding/onboarding.php')); ?>" class="w-full sm:w-auto bg-secondary text-on-secondary px-8 py-4 rounded-full font-label-md text-label-md hover:brightness-110 transition-all shadow-md flex items-center justify-center gap-2">Start Your Business<span class="material-symbols-outlined text-[20px]">arrow_forward</span></a>
</div>
<div class="relative z-10 mt-12 pt-8 border-t border-outline-variant/30 flex items-center gap-4">
<span class="material-symbols-outlined text-primary text-[24px]">verified_user</span>
<p class="font-label-md text-label-md text-on-surface-variant">24 hour guarantee for filing new companies.</p>
</div>
</div>
<!-- State Fees Panel -->
<div class="lg:col-span-5 bg-primary text-on-primary rounded-2xl p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden shadow-lg">
<div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-primary-container rounded-full blur-[60px] opacity-50"></div>
<div class="relative z-10">
<span class="font-label-sm text-label-sm text-primary-fixed uppercase tracking-widest opacity-80">Required State Fees</span>
<div class="flex items-baseline gap-2 mt-6 mb-8">
<span class="font-headline-lg text-[48px] leading-[48px] font-bold tracking-tighter">$102</span>
</div>
<div class="flex flex-col gap-6">
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary-fixed-dim mt-1">account_balance</span>
<div>
<h4 class="font-label-md text-label-md text-on-primary mb-1">Wyoming State Filing</h4>
<p class="font-body-md text-body-md text-on-primary/70">Mandatory fee paid directly to the Secretary of State for processing your LLC.</p>
</div>
</div>
<div class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary-fixed-dim mt-1">receipt_long</span>
<div>
<h4 class="font-label-md text-label-md text-on-primary mb-1">Total Due Today</h4>
<p class="font-body-md text-body-md text-on-primary/70">You will be charged a total of <strong>$201</strong> to complete your entire formation.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Bento Grid of Inclusions -->
<section class="w-full bg-surface-container-low py-section-gap-lg">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16">
<div class="max-w-2xl">
<h2 class="font-headline-lg text-headline-lg text-on-surface">The Turn-Key LLC</h2>
<p class="mt-4 font-body-lg text-body-lg text-on-surface-variant">Every filing includes these essential documents and services to ensure your business is fully compliant and ready to operate.</p>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">contract</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Operating Agreement</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Customized single or multi-member internal governing documents.</p>
</div>
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">gavel</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Articles of Organization</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Official state filing documents prepared and filed correctly the first time.</p>
</div>
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">badge</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Certificate of Formation</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Proof of business existence and official state recognition.</p>
</div>
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">account_balance_wallet</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Free Bank Account</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Required documentation allowing you to easily open your business bank account.</p>
</div>
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">library_books</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Operations Manual</h3>
<p class="font-body-md text-body-md text-on-surface-variant">A complete business guide and meeting minutes for organizational documentation.</p>
</div>
<div class="bg-surface-pure rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow group">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined">real_estate_agent</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">First Year Registered Agent and Business Address</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Professional representation and a premium business address included.</p>
</div>
</div>
</div>
</section>
<!-- Annual Requirements Section -->
<section class="w-full bg-inverse-surface py-section-gap-lg text-on-primary overflow-hidden relative">
<div class="absolute inset-0 w-full h-full opacity-10 pointer-events-none">
<svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
<defs>
<pattern height="40" id="grid-pattern" patternunits="userSpaceOnUse" width="40">
<circle cx="2" cy="2" fill="currentColor" r="1.5"></circle>
</pattern>
</defs>
<rect fill="url(#grid-pattern)" height="100%" width="100%"></rect>
</svg>
</div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
<div>
<span class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-widest mb-4 block">Ongoing Maintenance</span>
<h2 class="font-headline-xl text-headline-xl mb-6">Transparency in Annual Fees.</h2>
<p class="font-body-lg text-body-lg opacity-80 mb-8 leading-relaxed">
            Many do business in Wyoming due to the relatively minimal state requirements and fees. Maintaining the company in future years is simple and cost-effective.
          </p>
<ul class="flex flex-col gap-4">
<li class="flex items-center gap-4 bg-on-surface/40 p-4 rounded-xl border border-on-primary/10">
<div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
<span class="material-symbols-outlined">event_repeat</span>
</div>
<div>
<p class="font-label-md text-label-md text-on-primary">$60 Annual Report</p>
<p class="font-body-md text-body-md opacity-70">Mandatory fee paid to the Wyoming Secretary of State.</p>
</div>
</li>
<li class="flex items-center gap-4 bg-on-surface/40 p-4 rounded-xl border border-on-primary/10">
<div class="w-10 h-10 rounded-full bg-surface-container text-on-surface flex items-center justify-center shrink-0">
<span class="material-symbols-outlined">shield_person</span>
</div>
<div>
<p class="font-label-md text-label-md text-on-primary">Registered Agent Renewal</p>
<p class="font-body-md text-body-md opacity-70">Low annual renewal to maintain legal standing and privacy.</p>
</div>
</li>
</ul>
</div>
<div class="relative h-[500px] rounded-2xl overflow-hidden shadow-2xl">
<div class="bg-cover bg-center w-full h-full mix-blend-luminosity opacity-80" data-alt="A clean, highly organized, modern office desk with architectural blueprints, a sleek laptop showing financial charts, and warm natural sunlight streaming in, projecting a sense of professional corporate stability." style="background-image: url('<?php echo escape_html(asset_url('Storage/images/dashboard-estate-planning.jpg')); ?>')"></div>
<div class="absolute inset-0 bg-gradient-to-t from-inverse-surface via-inverse-surface/50 to-transparent"></div>
<div class="absolute bottom-8 left-8 right-8">
<div class="bg-surface-pure/10 backdrop-blur-md p-6 rounded-xl border border-on-primary/20">
<p class="font-body-md text-body-md text-on-primary italic">"They ensure maintaining a company in good standing is efficient and hassle-free. Overall, a great experience."</p>
<p class="font-label-sm text-label-sm text-primary-fixed-dim mt-4 uppercase tracking-wider">— Robert V. (Trustpilot, 5/5)</p>
</div>
</div>
</div>
</div>
</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>
