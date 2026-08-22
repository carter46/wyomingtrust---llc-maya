<?php
$site_settings = isset($site_settings) && is_array($site_settings) ? $site_settings : get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$login_href = asset_url('login.php');
$onboarding_href = asset_url('onboarding/onboarding.php');
?>
</main>
<footer class="w-full bg-inverse-surface py-20 text-on-primary">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
<div class="flex flex-col gap-6">
<?php
$logo_class = 'flex items-center gap-2';
$logo_text_class = 'font-headline-md text-headline-md tracking-tight text-on-primary';
$logo_img_class = 'w-8 h-8 object-contain rounded-lg bg-on-primary p-0.5';
include __DIR__ . '/components/site-logo.php';
?>
<p class="text-label-md opacity-70 leading-relaxed">Premium asset protection and business formation services tailored for modern entrepreneurs.</p>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm uppercase tracking-widest opacity-50">Services</span>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html(asset_url('about_us.php')); ?>">LLC Formation</a>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html($login_href); ?>">Registered Agent</a>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html($login_href); ?>">Virtual Office</a>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html($login_href); ?>">Compliance</a>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm uppercase tracking-widest opacity-50">Legal</span>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html(asset_url('privacy-policy.php')); ?>">Privacy Policy</a>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html(asset_url('terms-of-service.php')); ?>">Terms of Service</a>
<a class="text-body-md opacity-80 hover:opacity-100 transition-opacity" href="<?php echo escape_html($login_href); ?>">Legal Disclaimer</a>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-sm font-label-sm uppercase tracking-widest opacity-50">Contact</span>
<p class="text-body-md opacity-80">1603 Capitol Avenue<br/>Cheyenne, WY 82001</p>
<a class="text-body-md text-secondary-fixed hover:underline" href="mailto:support@wyomingtrust.com">support@wyomingtrust.com</a>
</div>
</div>
<div class="pt-8 border-t border-on-primary/10 flex flex-col md:flex-row justify-between items-center gap-4">
<p class="text-label-sm opacity-50">&copy; <?php echo date('Y'); ?> <?php echo escape_html($site_name); ?>. All rights reserved.</p>
<div class="flex gap-6">
<span class="material-symbols-outlined opacity-50 hover:opacity-100 cursor-pointer">public</span>
<span class="material-symbols-outlined opacity-50 hover:opacity-100 cursor-pointer">business_center</span>
<span class="material-symbols-outlined opacity-50 hover:opacity-100 cursor-pointer">shield_person</span>
</div>
</div>
</div>
</footer>
</body>
</html>
