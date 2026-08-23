<?php
// Optional: $logo_class, $logo_text_class, $logo_img_class, $logo_show_text (bool), $logo_href
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$logo_class = $logo_class ?? 'flex items-center gap-2 group';
$logo_text_class = $logo_text_class ?? 'font-headline-md text-headline-md text-primary tracking-tight';
$logo_href = $logo_href ?? asset_url('index.php');
$has_logo = !empty($site_settings['logo']);
$logo_src = $has_logo ? asset_url($site_settings['logo']) : null;
if (!isset($logo_show_text)) {
    $logo_show_text = !$has_logo;
}
$logo_img_class = $logo_img_class ?? 'h-14 w-auto max-w-[240px] object-contain';
?>
<a href="<?php echo escape_html($logo_href); ?>" class="<?php echo escape_html($logo_class); ?>" aria-label="<?php echo escape_html($site_name); ?>">
<?php if ($logo_src): ?>
<img src="<?php echo escape_html($logo_src); ?>" alt="<?php echo escape_html($site_name); ?>" class="<?php echo escape_html($logo_img_class); ?>"/>
<?php endif; ?>
<?php if ($logo_show_text): ?>
<span class="<?php echo escape_html($logo_text_class); ?>"><?php echo escape_html($site_name); ?></span>
<?php endif; ?>
</a>
