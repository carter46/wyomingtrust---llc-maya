<?php
require_once __DIR__ . '/../../api/helpers.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Email Settings';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

// Get SMTP config for display
require_once __DIR__ . '/../../api/config.php';
$smtpConfig = getSMTPConfig();

function renderEmailSettingsContent() {
    global $smtpConfig;
?>

<!-- SMTP Configuration Display -->
<section class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6 sm:mb-8">
    <h2 class="text-xl sm:text-2xl font-bold text-navy-900 dark:text-white mb-4 sm:mb-6 flex items-center gap-2">
        <span class="material-icons-outlined text-primary text-lg sm:text-xl">settings</span>
        <span>SMTP Configuration</span>
    </h2>
    
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <span class="material-icons-outlined text-blue-600 dark:text-blue-400 text-xl mt-0.5">info</span>
            <div class="flex-1">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">Configuration Location</p>
                <p class="text-xs text-blue-700 dark:text-blue-400">
                    SMTP settings are configured in <code class="bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded">api/config.php</code>. 
                    Edit the <code class="bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded">getSMTPConfig()</code> function to update these values.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        <!-- Host -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">SMTP Host</label>
            <div class="relative">
                <input 
                    type="text" 
                    value="<?php echo htmlspecialchars($smtpConfig['host']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- Port -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">SMTP Port</label>
            <div class="relative">
                <input 
                    type="text" 
                    value="<?php echo htmlspecialchars($smtpConfig['port']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- Encryption -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Encryption</label>
            <div class="relative">
                <input 
                    type="text" 
                    value="<?php echo strtoupper(htmlspecialchars($smtpConfig['encryption'])); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- Username -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Username</label>
            <div class="relative">
                <input 
                    type="text" 
                    value="<?php echo htmlspecialchars($smtpConfig['username']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- Password (masked) -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">Password</label>
            <div class="relative">
                <input 
                    type="password" 
                    value="<?php echo htmlspecialchars($smtpConfig['password']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Password is hidden for security</p>
        </div>

        <!-- From Email -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">From Email</label>
            <div class="relative">
                <input 
                    type="email" 
                    value="<?php echo htmlspecialchars($smtpConfig['from_email']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- From Name -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-navy-900 dark:text-white mb-2">From Name</label>
            <div class="relative">
                <input 
                    type="text" 
                    value="<?php echo htmlspecialchars($smtpConfig['from_name']); ?>" 
                    readonly
                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed"
                >
            </div>
        </div>
    </div>
</section>

<!-- Email Test Section -->
<section class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
    <h2 class="text-xl sm:text-2xl font-bold text-navy-900 dark:text-white mb-4 sm:mb-6 flex items-center gap-2">
        <span class="material-icons-outlined text-primary text-lg sm:text-xl">mail</span>
        <span>Test Email Configuration</span>
    </h2>
    
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
        Send a test email to verify your SMTP configuration is working correctly.
    </p>

    <div class="space-y-4">
        <!-- Test Email Input -->
        <div>
            <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Test Email Address</label>
            <div class="flex gap-3">
                <input 
                    type="email" 
                    id="testEmailInput" 
                    placeholder="your-email@example.com"
                    class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                >
                <button 
                    onclick="sendTestEmail()" 
                    id="testEmailBtn"
                    class="bg-primary text-navy-900 px-6 py-2 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 transition-opacity flex items-center gap-2"
                >
                    <span class="material-icons-outlined text-sm">send</span>
                    <span>Send Test Email</span>
                </button>
            </div>
        </div>

        <!-- Test Results -->
        <div id="testEmailResults" class="hidden"></div>
    </div>
</section>

<script src="includes/modal.js"></script>
<script>
    async function sendTestEmail() {
        const emailInput = document.getElementById('testEmailInput');
        const testBtn = document.getElementById('testEmailBtn');
        const resultsDiv = document.getElementById('testEmailResults');
        
        const email = emailInput.value.trim();
        
        if (!email) {
            showToast('Please enter an email address', 'warning');
            emailInput.focus();
            return;
        }
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('Please enter a valid email address', 'warning');
            emailInput.focus();
            return;
        }
        
        // Show loading state
        testBtn.disabled = true;
        testBtn.innerHTML = '<span class="material-icons-outlined text-sm animate-spin">hourglass_empty</span><span>Sending...</span>';
        resultsDiv.classList.add('hidden');
        
        try {
            const response = await fetch('../../api/admin/test-email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    email: email
                })
            });

            // Check if response is OK
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText || 'Server error'}`);
            }

            const data = await response.json();
            
            // Reset button
            testBtn.disabled = false;
            testBtn.innerHTML = '<span class="material-icons-outlined text-sm">send</span><span>Send Test Email</span>';
            
            // Show results
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                resultsDiv.innerHTML = `
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-400 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <span class="material-icons-outlined text-green-600 dark:text-green-400 text-xl mt-0.5">check_circle</span>
                            <div class="flex-1">
                                <p class="font-semibold text-green-900 dark:text-green-300 mb-1">Test Email Sent Successfully!</p>
                                <p class="text-sm text-green-700 dark:text-green-400">
                                    A test email has been sent to <strong>${escapeHtml(email)}</strong>. 
                                    Please check your inbox (and spam folder) for the test email.
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-500 mt-2">
                                    Sent at: ${data.timestamp || new Date().toLocaleString()}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                showToast('Test email sent successfully!', 'success');
            } else {
                resultsDiv.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <span class="material-icons-outlined text-red-600 dark:text-red-400 text-xl mt-0.5">error</span>
                            <div class="flex-1">
                                <p class="font-semibold text-red-900 dark:text-red-300 mb-1">Failed to Send Test Email</p>
                                <p class="text-sm text-red-700 dark:text-red-400 mb-2">
                                    ${escapeHtml(data.message || 'Unknown error occurred')}
                                </p>
                                ${data.suggestion ? `<p class="text-xs text-red-600 dark:text-red-500 mt-2"><strong>Suggestion:</strong> ${escapeHtml(data.suggestion)}</p>` : ''}
                                ${data.error ? `<p class="text-xs text-red-600 dark:text-red-500 mt-1"><strong>Error:</strong> ${escapeHtml(data.error)}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                showToast('Failed to send test email', 'error');
            }
        } catch (error) {
            // Reset button
            testBtn.disabled = false;
            testBtn.innerHTML = '<span class="material-icons-outlined text-sm">send</span><span>Send Test Email</span>';
            
            // Show error
            resultsDiv.classList.remove('hidden');
            resultsDiv.innerHTML = `
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-400 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-icons-outlined text-red-600 dark:text-red-400 text-xl mt-0.5">error</span>
                        <div class="flex-1">
                            <p class="font-semibold text-red-900 dark:text-red-300 mb-1">Network Error</p>
                            <p class="text-sm text-red-700 dark:text-red-400">
                                An error occurred while sending the test email. Please try again.
                            </p>
                        </div>
                    </div>
                </div>
            `;
            showToast('Error sending test email', 'error');
            console.error('Test email error:', error);
        }
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Allow Enter key to send test email
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('testEmailInput');
        if (emailInput) {
            emailInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendTestEmail();
                }
            });
        }
    });
</script>

<?php
}

// Render the layout with email settings content
renderAdminLayout($page_title, 'email-settings', 'renderEmailSettingsContent');
?>
