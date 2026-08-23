<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = $site_name . ' | Register Your Business in Wyoming';
$start_href = asset_url('onboarding/onboarding.php');
$pricing_href = asset_url('pricing.php');
$why_href = asset_url('why_us.php');
include 'includes/header.php';
?>
<div class="flex flex-col w-full">

<!-- Hero Section -->
<section class="relative w-full min-h-[85vh] flex items-center bg-surface overflow-hidden">
<div class="absolute inset-0 z-0 bg-gradient-to-br from-surface via-surface to-surface-container-low/50"></div>
<div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-fixed-dim/20 rounded-full blur-[100px] opacity-50 mix-blend-multiply"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 relative z-10 items-center py-12 lg:py-16">
<div class="lg:col-span-6 flex flex-col gap-6 lg:gap-7">
<div class="flex items-center gap-3 flex-wrap">
<div class="flex items-center gap-1.5 text-secondary-container">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-label-sm font-label-sm text-on-surface">Trustpilot 4.4/5</span>
</div>
<span class="text-label-sm text-outline">Based on 544 reviews</span>
</div>
<p class="text-label-sm font-label-sm uppercase tracking-[0.12em] text-primary">Limited Liability, Limitless Potential</p>
<h1 class="text-headline-lg-mobile lg:text-headline-xl font-headline-xl text-on-surface text-balance leading-tight">
Register Your Business in Wyoming — <span class="text-on-surface-variant opacity-80">The Best State To Register Your Business</span>
</h1>
<ul class="flex flex-col gap-2.5 text-body-md text-on-surface-variant">
<li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary-container text-[20px] mt-0.5">check_circle</span><span>Only $99 + State Filing Fees.</span></li>
<li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary-container text-[20px] mt-0.5">check_circle</span><span>Private &amp; Anonymous.</span></li>
<li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary-container text-[20px] mt-0.5">check_circle</span><span>Do Business In Any State With a Wyoming LLC.</span></li>
<li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary-container text-[20px] mt-0.5">check_circle</span><span>Get Assistance From Business Formation Experts.</span></li>
</ul>
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-2">
<a href="<?php echo escape_html($start_href); ?>" class="w-full sm:w-auto bg-primary text-on-primary px-8 py-4 rounded-full text-label-md font-label-md hover:bg-primary-container transition-colors shadow-sm flex items-center justify-center gap-2 group">Start Your Business<span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span></a>
<a href="<?php echo escape_html($pricing_href); ?>" class="w-full sm:w-auto bg-transparent text-primary border border-primary px-8 py-4 rounded-full text-label-md font-label-md hover:bg-primary/5 transition-colors flex items-center justify-center">View Pricing</a>
</div>
</div>
<div class="lg:col-span-6 relative flex justify-center lg:justify-end">
<div class="relative w-full max-w-lg bg-inverse-surface text-on-primary rounded-2xl overflow-hidden shadow-xl p-6 sm:p-8 flex flex-col gap-6">
<div class="aspect-video rounded-xl overflow-hidden bg-primary-container border border-on-primary/10">
<iframe class="w-full h-full" src="https://www.youtube-nocookie.com/embed/DWlkooXW3p0?rel=0&modestbranding=1" title="<?php echo escape_html($site_name); ?> Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>
<div>
<h3 class="text-headline-md font-headline-md mb-2">Business Formations Made Safe and Easy</h3>
<p class="text-secondary-container text-3xl sm:text-4xl font-bold tracking-tight">+ 100,000</p>
<p class="text-on-primary/70 text-sm mt-1">Companies Formed</p>
</div>
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-secondary text-on-secondary px-6 py-3 rounded-full text-label-md font-bold hover:brightness-110 transition-all w-full sm:w-auto">Start Your Business</a>
</div>
</div>
</div>
</section>

<!-- Testimonials -->
<section class="w-full py-section-gap-md bg-surface-pure">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
<h2 class="text-headline-lg font-headline-lg text-on-surface">Hear Directly From Our Delighted Customers</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
<div class="bg-surface-container-low rounded-2xl p-6 sm:p-8 border border-outline-variant/20 flex flex-col gap-4">
<div class="flex items-center gap-1 text-secondary-container">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-label-sm text-on-surface-variant ml-2">5/5 · Jun 27, 2023</span>
</div>
<p class="text-body-md text-on-surface-variant leading-relaxed flex-1">"Their team has been fantastic to work with, providing excellent service as a Registered Agent. Their online platform is user-friendly, making it easy to keep all company and personal information up-to-date. They ensure maintaining a company 'in good standing' is efficient and hassle-free. Overall, a great experience."</p>
<p class="text-label-md font-bold text-on-surface">Robert V.</p>
</div>
<div class="bg-surface-container-low rounded-2xl p-6 sm:p-8 border border-outline-variant/20 flex flex-col gap-4">
<div class="flex items-center gap-1 text-secondary-container">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-label-sm text-on-surface-variant ml-2">5/5 · Jan 9, 2023</span>
</div>
<p class="text-body-md text-on-surface-variant leading-relaxed flex-1">"I have used Wyoming LLC for several years now and I love the fact that they have live customer service. They are always very helpful and professional. Their prices are also very inexpensive by comparison to others out there in this market space."</p>
<p class="text-label-md font-bold text-on-surface">Katrina K.</p>
</div>
</div>
</div>
</section>

<!-- $99 Filing Includes -->
<section class="w-full py-section-gap-md bg-surface-container-low">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
<div>
<h2 class="text-headline-lg font-headline-lg text-on-surface mb-4">File Your LLC or Corporation for Just $99 + State Fees</h2>
<p class="text-body-md text-on-surface-variant mb-6">Attorney-built formation systems without the law firm price tag. Everything you need to launch correctly the first time.</p>
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-primary text-on-primary px-8 py-3.5 rounded-full text-label-md font-bold hover:bg-primary-container transition-colors">Start Your Business</a>
</div>
<div class="bg-surface-pure rounded-2xl p-6 sm:p-8 border border-outline-variant/20 shadow-sm">
<h3 class="text-headline-md font-headline-md text-on-surface mb-5">Every filing includes:</h3>
<ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<?php
$includes = [
    'Operating Agreement',
    'Articles of Organization',
    'Certificate of Formation',
    'Free Bank Account Docs',
    'Operations Manual',
    'First Year Registered Agent & Business Address',
];
foreach ($includes as $item): ?>
<li class="flex items-start gap-2 text-body-md text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-[20px] mt-0.5">task_alt</span>
<span><?php echo escape_html($item); ?></span>
</li>
<?php endforeach; ?>
</ul>
</div>
</div>
</div>
</section>

<!-- Quick & Easy Process -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-inverse-surface text-on-primary">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-12">
<h2 class="text-headline-lg font-headline-lg mb-3">Forming A Wyoming LLC With Us Is Quick And Easy</h2>
<p class="text-body-md opacity-80">A streamlined process designed to get your entity filed fast — often same day.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="flex flex-col gap-3 items-center text-center p-6 rounded-2xl bg-white/5 border border-white/10">
<span class="material-symbols-outlined text-secondary-container text-[40px]">edit_document</span>
<h3 class="text-body-lg font-bold">Online filing forms take 10 minutes to complete.</h3>
</div>
<div class="flex flex-col gap-3 items-center text-center p-6 rounded-2xl bg-white/5 border border-white/10">
<span class="material-symbols-outlined text-secondary-container text-[40px]">bolt</span>
<h3 class="text-body-lg font-bold">24 hour guarantee for filing new companies.</h3>
</div>
<div class="flex flex-col gap-3 items-center text-center p-6 rounded-2xl bg-white/5 border border-white/10">
<span class="material-symbols-outlined text-secondary-container text-[40px]">rocket_launch</span>
<h3 class="text-body-lg font-bold">Everything you need to launch your business.</h3>
</div>
</div>
<div class="text-center mt-10">
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-secondary text-on-secondary px-8 py-3.5 rounded-full text-label-md font-bold hover:brightness-110 transition-all">Start Your Business</a>
</div>
</div>
</section>

<!-- Why Form An LLC In Wyoming -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-pure">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
<div class="lg:col-span-5">
<h2 class="text-headline-lg font-headline-lg text-on-surface mb-6">Why Form An LLC In Wyoming?</h2>
<ul class="flex flex-col gap-3 mb-8">
<?php
$why_points = [
    '100% anonymity and privacy.',
    'Close/closed LLCs are allowed.',
    'No state taxes.',
    'No additional fees for extra members.',
    'Low annual fees.',
    'No minimum capital contribution.',
    'Asset protection.',
    'File everything online.',
];
foreach ($why_points as $point): ?>
<li class="flex items-start gap-3 text-body-md text-on-surface-variant">
<span class="material-symbols-outlined text-secondary-container text-[22px]">check_circle</span>
<span><?php echo escape_html($point); ?></span>
</li>
<?php endforeach; ?>
</ul>
<div class="bg-primary text-on-primary rounded-2xl p-6 mb-6">
<p class="text-3xl sm:text-4xl font-bold text-secondary-container">100,000+</p>
<p class="text-sm mt-1 opacity-90">Businesses Formed With <?php echo escape_html($site_name); ?></p>
</div>
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-primary text-on-primary px-8 py-3.5 rounded-full text-label-md font-bold hover:bg-primary-container transition-colors">Start Your Business</a>
</div>
<div class="lg:col-span-7 flex flex-col gap-6">
<div class="bg-surface-container-low rounded-2xl p-6 sm:p-8 border border-outline-variant/20">
<h3 class="text-headline-md font-headline-md text-on-surface mb-3">Annual Fees &amp; Requirements</h3>
<p class="text-body-md text-on-surface-variant leading-relaxed">Many do business in Wyoming due to the relatively minimal state requirements and fees. To set up an LLC, you are only required to list a registered agent and to pay a filing fee to the Secretary of State. Maintaining the company in future years is simple — you are only required to file a $60 annual report with the Secretary of State.</p>
<a href="<?php echo escape_html($why_href); ?>" class="inline-flex items-center gap-1 text-label-md font-label-md text-primary mt-4 hover:underline">Read More <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
</div>
<div class="bg-surface-container-low rounded-2xl p-6 sm:p-8 border border-outline-variant/20">
<h3 class="text-headline-md font-headline-md text-on-surface mb-3">Wyoming LLC Taxes</h3>
<p class="text-body-md text-on-surface-variant leading-relaxed">Limited Liability Companies offer the ability to be taxed as partnerships, corporations, or S-corporations. Each designation maintains the limited liability benefits. This hybrid structure is partly what drives the popularity of LLCs. If you choose the partnership designation, then you will be taxed according to your personal tax rate.</p>
<a href="<?php echo escape_html($why_href); ?>" class="inline-flex items-center gap-1 text-label-md font-label-md text-primary mt-4 hover:underline">Read More <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
</div>
</div>
</div>
</div>
</section>

<!-- Turn-Key LLC Package -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-container-low">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-12">
<h2 class="text-headline-lg font-headline-lg text-on-surface mb-3">The Turn-Key LLC</h2>
<p class="text-body-md text-on-surface-variant">We provide everything you need to start your business with complete peace of mind. No hidden fees, no surprises — just professional, reliable service.</p>
</div>
<div class="bg-surface-pure rounded-2xl border border-outline-variant/20 shadow-sm p-6 sm:p-10">
<h3 class="text-headline-md font-headline-md text-on-surface mb-2">Complete Business Formation Package</h3>
<p class="text-body-md text-on-surface-variant mb-8">Every filing includes these essential documents and services:</p>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
<?php
$package = [
    ['support_agent', 'Registered Agent Service', 'Professional representation for your business'],
    ['mail', 'Free Mail Scanning', '5 pieces included monthly'],
    ['home_pin', 'Business Address', 'Use our address for everything'],
    ['description', 'Operating Agreements', 'Single & multi-member versions'],
    ['event_note', 'Meeting Minutes', 'Organizational documentation'],
    ['article', 'Articles of Organization', 'Official state filing documents'],
    ['verified', 'Certificate of Formation', 'Proof of business existence'],
    ['account_balance', 'Bank Account Resolution', 'Documentation for banking'],
    ['menu_book', 'Operations Manual', 'Complete business guide'],
];
foreach ($package as [$icon, $title, $desc]): ?>
<div class="flex items-start gap-3 p-4 rounded-xl bg-surface-container-low/80">
<span class="material-symbols-outlined text-primary text-[28px]"><?php echo escape_html($icon); ?></span>
<div>
<h4 class="text-label-md font-bold text-on-surface mb-0.5"><?php echo escape_html($title); ?></h4>
<p class="text-label-sm text-on-surface-variant"><?php echo escape_html($desc); ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="bg-primary/5 border border-primary/10 rounded-xl p-5 sm:p-6 mb-8">
<h4 class="text-label-md font-bold text-on-surface mb-2 flex items-center gap-2"><span class="material-symbols-outlined text-primary">verified_user</span> Our Professional Promise</h4>
<p class="text-body-md text-on-surface-variant">Using our service ensures your personal information is protected with no risk of errors. We handle everything correctly the first time with no surprises, getting your business up and running in less than 24 hours.</p>
</div>
<div class="text-center">
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-secondary text-on-secondary px-8 py-3.5 rounded-full text-label-md font-bold hover:brightness-110 transition-all">Start Your Business Today</a>
</div>
</div>
</div>
</section>

<!-- Wyoming LLC Benefits -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-pure">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-12">
<h2 class="text-headline-lg font-headline-lg text-on-surface mb-3">Wyoming LLC Benefits</h2>
<p class="text-body-md text-on-surface-variant">Wyoming Limited Liability Companies offer the best combination of asset protection laws, privacy benefits, and cost savings. Perfect for online stores, real estate investors, and holding companies.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
<?php
$benefits = [
    ['visibility_off', 'Private & Anonymous', 'Owners and managers are not listed in public records. Your personal information remains completely private and secure.'],
    ['devices', 'Simple Requirements', 'Everything is handled online with no need to visit Wyoming. No residency requirements or wait times.'],
    ['savings', 'Low Annual Fees', 'Starting at just $99 plus state fees. Minimal ongoing costs with only a $60 annual report required.'],
    ['money_off', 'No State Taxes', 'Wyoming has no state income tax, corporate tax, or franchise tax. Significant savings for all business owners.'],
    ['shield', 'Asset Protection', 'Strong charging order protection. Personal creditors cannot seize your LLC, and LLC creditors cannot seize personal assets.'],
    ['public', 'Operational Flexibility', 'Conduct business in all 50 states. No minimum capital required and flexible management structure options.'],
];
foreach ($benefits as [$icon, $title, $desc]): ?>
<div class="flex flex-col p-6 rounded-2xl bg-surface-container-low border border-outline-variant/20 hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-primary text-[32px] mb-4"><?php echo escape_html($icon); ?></span>
<h3 class="text-body-lg font-bold text-on-surface mb-2"><?php echo escape_html($title); ?></h3>
<p class="text-body-md text-on-surface-variant"><?php echo escape_html($desc); ?></p>
</div>
<?php endforeach; ?>
</div>
<div class="bg-inverse-surface text-on-primary rounded-2xl p-6 sm:p-10">
<h3 class="text-headline-md font-headline-md mb-6">Additional Wyoming LLC Advantages</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
<?php
$extras = [
    'Single-Member LLCs allowed',
    'No operating agreement required',
    'Close/Closed LLC structures allowed',
    'No additional fees for extra members',
    'No minimum capital contribution',
    'S-Corp tax election available',
];
foreach ($extras as $extra): ?>
<div class="flex items-center gap-2 text-sm">
<span class="material-symbols-outlined text-secondary-container text-[20px]">check_circle</span>
<span><?php echo escape_html($extra); ?></span>
</div>
<?php endforeach; ?>
</div>
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center bg-secondary text-on-secondary px-8 py-3.5 rounded-full text-label-md font-bold hover:brightness-110 transition-all">Start Your Wyoming LLC</a>
</div>
</div>
</section>

<!-- FAQs -->
<section class="w-full py-section-gap-md lg:py-section-gap-lg bg-surface-container-low">
<div class="max-w-4xl mx-auto px-6 lg:px-12">
<div class="text-center mb-10">
<h2 class="text-headline-lg font-headline-lg text-on-surface mb-2">FAQs</h2>
<p class="text-body-md text-on-surface-variant">Common questions about Wyoming LLC formation, privacy, and taxes.</p>
</div>

<h3 class="text-headline-md font-headline-md text-on-surface mb-4">Privacy &amp; Anonymity FAQs</h3>
<div class="flex flex-col gap-3 mb-10">
<?php
$privacy_faqs = [
    ['Are Wyoming LLCs private and anonymous?', 'Yes, Wyoming LLCs offer exceptional privacy and anonymity. Unlike many other states, Wyoming does not require the disclosure of member or manager information in public filings. Your personal information remains private and is not entered into any public database.'],
    ['Is my information entered into any public database with a Wyoming LLC?', 'No, your personal information is not entered into any public database when you form a Wyoming LLC. The Secretary of State only knows who organized the company (which is us as your registered agent), but your name does not appear on the formation documents and is not made public.'],
    ['Is it wrong to desire anonymity for my LLC?', 'Absolutely not. Desiring anonymity is not wrong at all. You have a legal right to keep your business affairs private, and there\'s nothing to gain by displaying your wealth or business activities publicly. Privacy is a legitimate business strategy for protection and security.'],
    ['Can I use my Wyoming LLC to own an LLC in another state?', 'Yes, you can use your anonymous Wyoming LLC as the publicly listed owner of another LLC in a different state. For example, while Florida requires public disclosure of LLC ownership, your Wyoming LLC can be listed as the owner, maintaining your personal anonymity.'],
    ['Does this setup work in all states?', 'Yes, Wyoming LLCs can conduct business in all 50 states. You can register to do business in other states as a foreign LLC while maintaining your Wyoming base and privacy protections. This provides maximum flexibility for your business operations.'],
];
foreach ($privacy_faqs as [$q, $a]): ?>
<div class="bg-surface-pure rounded-xl border border-outline-variant/20 overflow-hidden">
<button type="button" onclick="toggleFaq(this)" class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 text-label-md font-bold text-on-surface hover:bg-surface-container-low/50 transition-colors">
<span><?php echo escape_html($q); ?></span>
<span class="material-symbols-outlined text-outline shrink-0" data-faq-icon>expand_more</span>
</button>
<div class="hidden px-5 pb-4 text-body-md text-on-surface-variant" data-faq-panel><?php echo escape_html($a); ?></div>
</div>
<?php endforeach; ?>
</div>

<h3 class="text-headline-md font-headline-md text-on-surface mb-4">Wyoming LLC Frequently Asked Questions</h3>
<div class="flex flex-col gap-3">
<?php
$general_faqs = [
    ['Why use a Wyoming LLC?', 'Wyoming offers the best combination of privacy, asset protection, and low costs. With no state income tax, strong asset protection laws, complete anonymity, and low annual fees, Wyoming is considered the premier jurisdiction for LLC formation in the United States.'],
    ['How much does it cost to file an LLC in Wyoming?', 'Our formation service starts at $99 plus state fees. The Wyoming state filing fee is typically around $100–$102 when filed online. This includes all necessary documents, first-year registered agent service, and ongoing support to get your LLC up and running quickly.'],
    ['Are LLCs taxed in Wyoming?', 'No, Wyoming has no state income tax, corporate tax, or franchise tax. LLCs are pass-through entities for federal tax purposes, meaning profits and losses pass through to the individual members\' personal tax returns. This provides significant tax advantages.'],
    ['Can Wyoming LLC have an out-of-state address?', 'Yes, Wyoming LLCs can have members and managers located anywhere in the world. You don\'t need to be a Wyoming resident or have a physical presence in the state. We provide a Wyoming registered agent address to satisfy state requirements.'],
];
foreach ($general_faqs as [$q, $a]): ?>
<div class="bg-surface-pure rounded-xl border border-outline-variant/20 overflow-hidden">
<button type="button" onclick="toggleFaq(this)" class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 text-label-md font-bold text-on-surface hover:bg-surface-container-low/50 transition-colors">
<span><?php echo escape_html($q); ?></span>
<span class="material-symbols-outlined text-outline shrink-0" data-faq-icon>expand_more</span>
</button>
<div class="hidden px-5 pb-4 text-body-md text-on-surface-variant" data-faq-panel><?php echo escape_html($a); ?></div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<!-- Final CTA -->
<section class="w-full py-section-gap-md bg-primary text-on-primary">
<div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col lg:flex-row items-center justify-between gap-8">
<div class="max-w-xl text-center lg:text-left">
<h2 class="text-headline-lg font-headline-lg mb-3">Ready to register your business in Wyoming?</h2>
<p class="text-body-md opacity-90">Form your LLC for $99 + state fees with same-day filing, instant bank account docs, and no hidden fees.</p>
</div>
<a href="<?php echo escape_html($start_href); ?>" class="inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary px-8 py-4 rounded-full text-label-md font-bold hover:brightness-110 transition-all shrink-0">
<span class="material-symbols-outlined text-[20px]">domain_add</span>
<span>Start Your Business</span>
</a>
</div>
</section>

</div>

<?php include 'includes/footer.php'; ?>
