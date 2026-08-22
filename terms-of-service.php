<?php
require_once __DIR__ . '/api/helpers.php';
$site_settings = get_site_settings();
$site_name = $site_settings['site_name'] ?? 'WyomingTrust';
$page_title = 'Terms of Service | ' . $site_name;
include 'includes/header.php';
?>
<section class="w-full bg-surface pt-16 pb-10">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<span class="text-label-sm font-label-sm uppercase tracking-widest text-primary">Legal</span>
<h1 class="font-headline-xl text-headline-xl text-on-surface mt-4 mb-3">Terms of Service</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">The terms and conditions governing use of the <?php echo escape_html($site_name); ?> platform.</p>
</div>
</section>
<section class="py-section-gap-md px-6 lg:px-12 bg-surface-container-low">
<div class="max-w-3xl mx-auto bg-surface-pure rounded-2xl border border-outline-variant/30 p-8 md:p-12 shadow-sm">
<p class="font-label-md text-label-md text-on-surface-variant mb-8">Last updated: <?php echo date('F j, Y'); ?></p>
<div class="space-y-8">
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">1. Acceptance of Terms</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
By accessing and using <?php echo escape_html($site_name); ?> ("the Platform"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these Terms of Service, you may not use the Platform.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">2. Description of Service</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
<?php echo escape_html($site_name); ?> provides a digital platform for creating and managing trust services for cryptocurrency and digital assets. Our services include:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Digital asset trust creation and management</li>
<li>Portfolio tracking and asset monitoring</li>
<li>Transaction history and reporting</li>
<li>Secure wallet linking and encryption services</li>
</ul>
<p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
<strong>Important Disclaimer:</strong> <?php echo escape_html($site_name); ?> is a technology platform and administrative service provider. We are not a bank, financial institution, or legal advisor. We do not provide investment, legal, or tax advice.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">3. Eligibility</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
You must meet the following criteria to use our services:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Be at least 18 years of age</li>
<li>Have the legal capacity to enter into binding agreements</li>
<li>Provide accurate and complete information during registration</li>
<li>Comply with all applicable laws and regulations</li>
<li>Not be located in a jurisdiction where our services are prohibited</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">4. Account Registration and Security</h2>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3">4.1 Account Creation</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
To use our services, you must create an account by providing accurate, current, and complete information. You are responsible for maintaining the confidentiality of your account credentials.
</p>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3 mt-6">4.2 Security Responsibilities</h3>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Use a strong, unique password</li>
<li>Keep your login credentials confidential</li>
<li>Notify us immediately of any unauthorized access</li>
<li>Enable two-factor authentication when available</li>
<li>Log out when using shared or public devices</li>
</ul>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3 mt-6">4.3 Wallet Security</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
<strong>Critical Security Notice:</strong>
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>We encrypt wallet data using industry-standard AES-256-CBC encryption</li>
<li>We do not store your private keys or seed phrases in plain text</li>
<li>You are solely responsible for backing up and securing your wallet recovery information</li>
<li>We cannot recover lost private keys or seed phrases</li>
<li>Always verify wallet addresses before sending transactions</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">5. Trust Services</h2>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3">5.1 Trust Creation</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
When creating a trust through our platform, you acknowledge that:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>You have legal authority to place assets into the trust</li>
<li>Trust terms and conditions are legally binding once created</li>
<li>You are responsible for compliance with applicable trust and tax laws</li>
<li>Trust fees are non-refundable unless otherwise specified</li>
</ul>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3 mt-6">5.2 Trust Management</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
You may manage your trusts through the platform, subject to the terms of each specific trust agreement. Some trusts (irrevocable) may have restrictions on modifications.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">6. Transactions and Fees</h2>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3">6.1 Transaction Processing</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
All cryptocurrency transactions are processed on their respective blockchain networks. We are not responsible for:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Network congestion or delays</li>
<li>Transaction fees charged by blockchain networks</li>
<li>Losses due to incorrect recipient addresses</li>
<li>Irreversible transactions sent to wrong addresses</li>
</ul>
<h3 class="font-label-md text-label-md font-bold text-primary mb-3 mt-6">6.2 Fees</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
Our service fees are clearly displayed before you complete any transaction. All fees are non-refundable unless required by law. Blockchain network fees are separate and are determined by the respective blockchain networks.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">7. Prohibited Activities</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
You agree not to:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Use the Platform for any illegal purpose or in violation of any laws</li>
<li>Attempt to gain unauthorized access to the Platform or other users' accounts</li>
<li>Interfere with or disrupt the Platform's security features</li>
<li>Use automated systems (bots, scrapers) to access the Platform</li>
<li>Impersonate any person or entity or falsely state your affiliation</li>
<li>Transmit viruses, malware, or any malicious code</li>
<li>Use the Platform to facilitate money laundering or terrorist financing</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">8. Intellectual Property</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
All content, features, and functionality of the Platform, including but not limited to text, graphics, logos, and software, are the exclusive property of <?php echo escape_html($site_name); ?> and are protected by international copyright, trademark, and other intellectual property laws.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">9. Disclaimer of Warranties</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
THE PLATFORM IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED. WE DISCLAIM ALL WARRANTIES, INCLUDING BUT NOT LIMITED TO:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Warranties of merchantability, fitness for a particular purpose, or non-infringement</li>
<li>Warranties that the Platform will be uninterrupted, secure, or error-free</li>
<li>Warranties regarding the accuracy, reliability, or availability of the Platform</li>
<li>Warranties regarding cryptocurrency prices, transaction speeds, or network availability</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">10. Limitation of Liability</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
TO THE MAXIMUM EXTENT PERMITTED BY LAW, <?php echo strtoupper(escape_html($site_name)); ?> SHALL NOT BE LIABLE FOR:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Indirect, incidental, special, consequential, or punitive damages</li>
<li>Loss of profits, revenue, data, or other intangible losses</li>
<li>Losses resulting from unauthorized access to your account</li>
<li>Losses resulting from blockchain network issues or failures</li>
<li>Losses resulting from incorrect transaction details provided by you</li>
</ul>
<p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
Our total liability shall not exceed the fees you paid to us in the 12 months preceding the claim.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">11. Indemnification</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
You agree to indemnify and hold harmless <?php echo escape_html($site_name); ?>, its affiliates, officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses (including legal fees) arising out of or relating to your use of the Platform, violation of these Terms, or violation of any rights of another party.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">12. Termination</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users, us, or third parties. You may terminate your account at any time by contacting us. Upon termination:
</p>
<ul class="list-disc pl-6 space-y-2 font-body-md text-body-md text-on-surface-variant">
<li>Your right to use the Platform will immediately cease</li>
<li>You remain responsible for all fees incurred before termination</li>
<li>We may delete your account and associated data in accordance with our Privacy Policy</li>
</ul>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">13. Changes to Terms</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
We reserve the right to modify these Terms at any time. We will notify users of material changes by posting the updated Terms on this page and updating the "Last updated" date. Your continued use of the Platform after such changes constitutes acceptance of the modified Terms.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">14. Governing Law</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
These Terms shall be governed by and construed in accordance with the laws of the State of Wyoming, United States, without regard to its conflict of law provisions. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts of Wyoming.
</p>
</section>
<section>
<h2 class="font-headline-md text-headline-md text-primary mb-4">15. Contact Information</h2>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
If you have any questions about these Terms of Service, please contact us:
</p>
<div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant/30">
<p class="font-body-md text-body-md text-on-surface-variant mb-2"><strong><?php echo escape_html($site_name); ?></strong></p>
<p class="font-body-md text-body-md text-on-surface-variant mb-2">Email: <a href="mailto:legal@wyomingtrust.com" class="text-secondary font-bold hover:underline">legal@wyomingtrust.com</a></p>
<p class="font-body-md text-body-md text-on-surface-variant">Website: <a href="login.php" class="text-secondary font-bold hover:underline">Log In</a></p>
</div>
</section>
</div>
</div>
</section>
<?php include 'includes/footer.php'; ?>
