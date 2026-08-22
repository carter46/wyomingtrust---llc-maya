<?php
// Optional: $logo_class for wrapper, $logo_text_class for wordmark, $logo_img_class for image
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$logo_class = $logo_class ?? 'flex items-center gap-2 group';
$logo_text_class = $logo_text_class ?? 'font-headline-md text-headline-md text-primary tracking-tight';
$logo_img_class = $logo_img_class ?? 'w-10 h-10 object-contain rounded-xl';
$logo_href = $logo_href ?? asset_url('index.php');
$logo_src = asset_url('Storage/images/logo_ant.webp');
?>
<a href="<?php echo escape_html($logo_href); ?>" class="<?php echo escape_html($logo_class); ?>">
<img src="<?php echo escape_html($logo_src); ?>" alt="<?php echo escape_html($site_name); ?>" class="<?php echo escape_html($logo_img_class); ?>"/>
<span class="<?php echo escape_html($logo_text_class); ?>"><?php echo escape_html($site_name); ?></span>
</a>
