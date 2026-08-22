<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Privacy Policy | ' . $site_name;
include 'includes/header.php';
?>
<section class="w-full bg-surface pt-16 pb-10">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<span class="text-label-sm font-label-sm uppercase tracking-widest text-primary">Legal</span>
<h1 class="font-headline-xl text-headline-xl text-on-surface mt-4 mb-3">Privacy Policy</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">How we collect, use, and protect your information.</p>
</div>
</section>
<section class="py-section-gap-md px-6 lg:px-12 bg-surface-container-low">
<div class="max-w-3xl mx-auto bg-surface-pure rounded-2xl border border-outline-variant/30 p-8 md:p-12 shadow-sm">
<p class="font-label-md text-label-md text-on-surface-variant mb-8">Last updated: <?php echo date('F j, Y'); ?></p>
<div class="space-y-8">
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">1. Introduction</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
<?php echo escape_html($site_name); ?> ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our digital asset trust platform.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">2. Information We Collect</h2>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3">2.1 Personal Information</h3>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Full name and contact information (email address)</li>
<li>Account credentials (encrypted and hashed passwords)</li>
<li>Trust service preferences and configurations</li>
<li>Communication records</li>
</ul>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3 mt-6">2.2 Cryptocurrency Information</h3>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Wallet addresses and encrypted wallet data</li>
<li>Asset balances and transaction history</li>
<li>Transaction metadata (amounts, fees, timestamps)</li>
</ul>
<p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
<strong>Important:</strong> We never store your private keys or seed phrases in plain text. All sensitive wallet data is encrypted using AES-256-CBC encryption before storage.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">3. How We Use Your Information</h2>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Provide and maintain our trust services</li>
<li>Process transactions and manage your assets</li>
<li>Verify your identity and prevent fraud</li>
<li>Send administrative and service-related communications</li>
<li>Respond to your inquiries and provide customer support</li>
<li>Comply with legal obligations and regulatory requirements</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">4. Data Security</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
We implement industry-standard security measures to protect your information:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Encryption of sensitive data in transit (HTTPS/TLS) and at rest (AES-256-CBC)</li>
<li>Secure session management with HTTP-only and secure cookies</li>
<li>Regular security audits and updates</li>
<li>Access controls and authentication requirements</li>
<li>Prepared SQL statements to prevent injection attacks</li>
<li>Rate limiting to prevent abuse</li>
</ul>
<p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
Despite our efforts, no method of transmission over the Internet or electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your information, we cannot guarantee absolute security.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">5. Data Sharing and Disclosure</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li><strong>Legal Requirements:</strong> When required by law, court order, or government regulation</li>
<li><strong>Service Providers:</strong> With trusted third-party service providers who assist in operating our platform (e.g., email services, payment processors), subject to strict confidentiality agreements</li>
<li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets, with notice to affected users</li>
<li><strong>With Your Consent:</strong> When you explicitly authorize us to share your information</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">6. Your Rights and Choices</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
You have the following rights regarding your personal information:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li><strong>Access:</strong> Request access to your personal information</li>
<li><strong>Correction:</strong> Update or correct inaccurate information</li>
<li><strong>Deletion:</strong> Request deletion of your account and associated data</li>
<li><strong>Data Portability:</strong> Request a copy of your data in a portable format</li>
<li><strong>Opt-Out:</strong> Unsubscribe from marketing communications (administrative emails will still be sent)</li>
</ul>
<p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
To exercise these rights, please contact us at the information provided below.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">7. Cookies and Tracking</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
We use essential cookies for session management and security. These cookies are required for the platform to function properly and cannot be disabled. We do not use tracking cookies or third-party analytics that collect personal information.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">8. Data Retention</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
We retain your personal information for as long as necessary to provide our services and comply with legal obligations. When you delete your account, we will delete or anonymize your personal information within 30 days, except where we are required to retain it for legal or regulatory purposes.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">9. Children's Privacy</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">10. Changes to This Privacy Policy</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date. You are advised to review this Privacy Policy periodically for any changes.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">11. Contact Us</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
If you have any questions about this Privacy Policy or our data practices, please contact us:
</p>
<div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant/30">
<p class="font-body-md text-body-md text-on-surface-variant mb-2"><strong><?php echo escape_html($site_name); ?></strong></p>
<p class="font-body-md text-body-md text-on-surface-variant mb-2">Email: <a href="mailto:privacy@wyomingtrust.com" class="text-secondary font-bold hover:underline">privacy@wyomingtrust.com</a></p>
<p class="font-body-md text-body-md text-on-surface-variant">Website: <a href="login.php" class="text-secondary font-bold hover:underline">Log In</a></p>
</div>
</section>
</div>
</div>
</section>
<?php include 'includes/footer.php'; ?>
