<?php
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Payment Methods Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderPaymentsContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Payment Methods Management</h1>
    <button onclick="showPaymentTypeSelector()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto flex items-center gap-2">
        <span class="material-icons-outlined text-sm">add</span>
        <span>Add New Payment Method</span>
    </button>
</div>

<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="paymentsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading payment methods...</div>
    </div>
</div>

<script src="includes/modal.js"></script>
<script>
let allPayments = [];

async function loadPayments() {
    try {
        const response = await fetch('../../api/admin/payments.php');
        const data = await response.json();
        if (data.success && data.methods) {
            allPayments = data.methods;
            renderPayments(data.methods);
        } else {
            document.getElementById('paymentsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load payment methods</div>';
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        document.getElementById('paymentsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading payment methods</div>';
    }
}

function renderPayments(methods) {
    const container = document.getElementById('paymentsContainer');
    if (!methods || methods.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No payment methods found</div>';
        return;
    }
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Method Type</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Method Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Details</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${methods.map(method => {
                        const config = method.config_data || {};
                        let details = '';
                        if (method.method_type === 'crypto') {
                            details = `${config.coin_name || ''} (${config.network_type || ''})`;
                        } else if (method.method_type === 'bank_transfer') {
                            details = `${config.bank_name || ''} - ${config.account_name || ''}`;
                        } else if (method.method_type === 'paypal') {
                            details = config.paypal_email || config.paypal_tag || '';
                        }
                        return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4"><span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">${escapeHtml(method.method_type)}</span></td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 font-semibold text-sm">${escapeHtml(method.method_name)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600 dark:text-slate-400">${escapeHtml(details)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" ${method.is_active ? 'checked' : ''} onchange="togglePaymentStatus(${method.id}, this.checked)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="editPayment(${method.id})" class="text-primary hover:underline text-xs sm:text-sm flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">edit</span>
                                        <span>Edit</span>
                                    </button>
                                    <button onclick="deletePayment(${method.id})" class="text-red-600 hover:underline text-xs sm:text-sm flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">delete</span>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${methods.map(method => {
                const config = method.config_data || {};
                let details = '';
                if (method.method_type === 'crypto') {
                    details = `${config.coin_name || ''} (${config.network_type || ''})`;
                } else if (method.method_type === 'bank_transfer') {
                    details = `${config.bank_name || ''} - ${config.account_name || ''}`;
                } else if (method.method_type === 'paypal') {
                    details = config.paypal_email || config.paypal_tag || '';
                }
                return `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white truncate">${escapeHtml(method.method_name)}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><span class="px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 text-xs">${escapeHtml(method.method_type)}</span></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${escapeHtml(details)}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-2">
                            <input type="checkbox" ${method.is_active ? 'checked' : ''} onchange="togglePaymentStatus(${method.id}, this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="flex gap-2 pt-3 border-t border-slate-200 dark:border-slate-600">
                        <button onclick="editPayment(${method.id})" class="text-primary hover:underline text-xs flex items-center gap-1">
                            <span class="material-icons-outlined text-xs">edit</span>
                            <span>Edit</span>
                        </button>
                        <button onclick="deletePayment(${method.id})" class="text-red-600 hover:underline text-xs flex items-center gap-1">
                            <span class="material-icons-outlined text-xs">delete</span>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            `;
            }).join('')}
        </div>
    `;
    container.innerHTML = html;
}

// Step 1: Show payment type selector
function showPaymentTypeSelector() {
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Select Payment Method Type *</label>
                <select name="payment_type" id="paymentTypeSelect" required 
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <option value="">Choose a payment type</option>
                    <option value="crypto">Cryptocurrency</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="paypal">PayPal</option>
                </select>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Select the type of payment method you want to add</p>
            </div>
        </div>
    `;
    
    showFormModal('Add New Payment Method', formHtml, function(data) {
        const paymentType = data.payment_type;
        if (!paymentType) {
            showToast('Please select a payment type', 'warning');
            return;
        }
        // Close current modal and show type-specific form
        closeModal();
        setTimeout(() => {
            showPaymentTypeForm(paymentType);
        }, 300);
    });
}

// Step 2: Show type-specific form
function showPaymentTypeForm(paymentType) {
    let formHtml = '';
    
    if (paymentType === 'crypto') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Coin Name *</label>
                    <input type="text" name="coin_name" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="e.g., Bitcoin, Ethereum, USDC">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Network Type *</label>
                    <select name="network_type" required 
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                        <option value="">Select network</option>
                        <option value="Bitcoin">Bitcoin</option>
                        <option value="Ethereum">Ethereum (ERC-20)</option>
                        <option value="BSC">Binance Smart Chain (BEP-20)</option>
                        <option value="Polygon">Polygon (MATIC)</option>
                        <option value="Solana">Solana</option>
                        <option value="TRON">TRON (TRC-20)</option>
                        <option value="Litecoin">Litecoin</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Wallet Address *</label>
                    <input type="text" name="wallet_address" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono text-sm" 
                           placeholder="Enter wallet address">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">QR Code Image (Optional)</label>
                    <input type="file" name="qr_code" accept="image/png,image/jpeg,image/jpg,image/svg+xml" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload QR code for the wallet address (PNG, JPG, SVG)</p>
                </div>
            </div>
        `;
    } else if (paymentType === 'bank_transfer') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Bank Name *</label>
                    <input type="text" name="bank_name" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="e.g., Bank of America, Chase">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Account Name *</label>
                    <input type="text" name="account_name" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="Account holder name">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Account Number *</label>
                    <input type="text" name="account_number" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="Enter account number">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Routing Number</label>
                    <input type="text" name="routing_number" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="Enter routing number">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">SWIFT/BIC Code</label>
                    <input type="text" name="swift_code" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="For international transfers">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Additional Details</label>
                    <textarea name="additional_details" rows="3" 
                              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                              placeholder="Any other important details..."></textarea>
                </div>
            </div>
        `;
    } else if (paymentType === 'paypal') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">PayPal Email or Tag *</label>
                    <input type="email" name="paypal_email" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="your-paypal@email.com or @yourpaypaltag">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter your PayPal email address or PayPal tag</p>
                </div>
            </div>
        `;
    }
    
    // Use a custom form submission that handles file uploads
    const formElement = document.createElement('form');
    formElement.id = 'paymentMethodForm';
    formElement.enctype = 'multipart/form-data';
    formElement.innerHTML = formHtml;
    
    // Create modal content with form
    const content = formElement.outerHTML;
    
    // Store payment type
    const originalShowFormModal = window.showFormModal;
    
    // Create a wrapper that handles file uploads
    const modalActions = [
        {
            label: 'Cancel',
            onclick: () => closeModal(),
            class: 'px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:opacity-90 transition-opacity'
        },
        {
            label: 'Create Payment Method',
            onclick: () => {
                const form = document.getElementById('paymentMethodForm');
                if (form && form.checkValidity()) {
                    handlePaymentFormSubmit(paymentType, form);
                } else {
                    form.reportValidity();
                }
            },
            class: 'px-4 py-2 bg-primary text-navy-900 font-semibold rounded-lg hover:opacity-90 transition-opacity',
            icon: 'check'
        }
    ];
    
    showModal(
        `Add ${paymentType === 'crypto' ? 'Cryptocurrency' : paymentType === 'bank_transfer' ? 'Bank Transfer' : 'PayPal'} Payment Method`,
        content,
        modalActions
    );
}

// Handle form submission with file upload support
async function handlePaymentFormSubmit(paymentType, formElement) {
    const formData = new FormData(formElement);
    const configData = {};
    let methodName = '';
    
    if (paymentType === 'crypto') {
        methodName = formData.get('coin_name');
        configData.coin_name = formData.get('coin_name');
        configData.network_type = formData.get('network_type');
        configData.wallet_address = formData.get('wallet_address');
        
        // Handle QR code file upload
        const qrFile = formData.get('qr_code');
        if (qrFile && qrFile.size > 0) {
            // Will be handled by API via separate endpoint
            configData.has_qr_code = true;
        }
    } else if (paymentType === 'bank_transfer') {
        const bankName = formData.get('bank_name');
        methodName = `${bankName} Bank Transfer`;
        configData.bank_name = bankName;
        configData.account_name = formData.get('account_name');
        configData.account_number = formData.get('account_number');
        configData.routing_number = formData.get('routing_number') || '';
        configData.swift_code = formData.get('swift_code') || '';
        configData.additional_details = formData.get('additional_details') || '';
    } else if (paymentType === 'paypal') {
        const paypalEmail = formData.get('paypal_email');
        methodName = 'PayPal';
        configData.paypal_email = paypalEmail;
        configData.paypal_tag = paypalEmail.startsWith('@') ? paypalEmail : null;
    }
    
    // First create the payment method
    try {
        const createResponse = await fetch('../../api/admin/payments.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                method_type: paymentType,
                method_name: methodName,
                config_data: configData,
                is_active: 1
            })
        });
        
        const createData = await createResponse.json();
        
        if (!createData.success) {
            showToast(createData.message || 'Failed to create payment method', 'error');
            return;
        }
        
        // If there's a QR code file, upload it separately
        const qrFile = formElement.querySelector('input[name="qr_code"]')?.files[0];
        if (qrFile && paymentType === 'crypto') {
            const uploadFormData = new FormData();
            uploadFormData.append('qr_code', qrFile);
            uploadFormData.append('payment_method_id', createData.method.id);
            
            const uploadResponse = await fetch('../../api/admin/payments.php?action=upload_qr', {
                method: 'POST',
                body: uploadFormData
            });
            
            const uploadData = await uploadResponse.json();
            if (!uploadData.success) {
                showToast('Payment method created but QR code upload failed: ' + uploadData.message, 'warning');
            }
        }
        
        showToast('Payment method created successfully', 'success');
        closeModal();
        loadPayments();
        
    } catch (error) {
        console.error('Error creating payment method:', error);
        showToast('Error creating payment method', 'error');
    }
}

async function togglePaymentStatus(id, isActive) {
    try {
        const response = await fetch('../../api/admin/payments.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, is_active: isActive ? 1 : 0 })
        });
        const data = await response.json();
        if (!data.success) {
            showToast(data.message || 'Failed to update status', 'error');
            loadPayments();
        } else {
            showToast(`Payment method ${isActive ? 'activated' : 'deactivated'} successfully`, 'success');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showToast('Error updating status', 'error');
        loadPayments();
    }
}

function editPayment(id) {
    const payment = allPayments.find(p => p.id == id);
    if (!payment) {
        showToast('Payment method not found', 'error');
        return;
    }
    
    showPaymentTypeForm(payment.method_type, payment);
}

// Modified to support editing
function showPaymentTypeForm(paymentType, existingPayment = null) {
    const config = existingPayment?.config_data || {};
    const isEdit = !!existingPayment;
    
    let formHtml = '';
    
    if (paymentType === 'crypto') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Coin Name *</label>
                    <input type="text" name="coin_name" value="${escapeHtml(config.coin_name || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="e.g., Bitcoin, Ethereum, USDC">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Network Type *</label>
                    <select name="network_type" required 
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                        <option value="">Select network</option>
                        <option value="Bitcoin" ${config.network_type === 'Bitcoin' ? 'selected' : ''}>Bitcoin</option>
                        <option value="Ethereum" ${config.network_type === 'Ethereum' ? 'selected' : ''}>Ethereum (ERC-20)</option>
                        <option value="BSC" ${config.network_type === 'BSC' ? 'selected' : ''}>Binance Smart Chain (BEP-20)</option>
                        <option value="Polygon" ${config.network_type === 'Polygon' ? 'selected' : ''}>Polygon (MATIC)</option>
                        <option value="Solana" ${config.network_type === 'Solana' ? 'selected' : ''}>Solana</option>
                        <option value="TRON" ${config.network_type === 'TRON' ? 'selected' : ''}>TRON (TRC-20)</option>
                        <option value="Litecoin" ${config.network_type === 'Litecoin' ? 'selected' : ''}>Litecoin</option>
                        <option value="Other" ${config.network_type === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Wallet Address *</label>
                    <input type="text" name="wallet_address" value="${escapeHtml(config.wallet_address || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono text-sm" 
                           placeholder="Enter wallet address">
                </div>
                ${config.qr_code ? `
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Current QR Code</label>
                    <img src="../../${config.qr_code}" alt="QR Code" class="max-w-32 max-h-32 border border-slate-300 rounded-lg p-2">
                </div>
                ` : ''}
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">${isEdit ? 'Update' : ''} QR Code Image (Optional)</label>
                    <input type="file" name="qr_code" accept="image/png,image/jpeg,image/jpg,image/svg+xml" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload QR code for the wallet address (PNG, JPG, SVG)</p>
                </div>
            </div>
        `;
    } else if (paymentType === 'bank_transfer') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Bank Name *</label>
                    <input type="text" name="bank_name" value="${escapeHtml(config.bank_name || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="e.g., Bank of America, Chase">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Account Name *</label>
                    <input type="text" name="account_name" value="${escapeHtml(config.account_name || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="Account holder name">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Account Number *</label>
                    <input type="text" name="account_number" value="${escapeHtml(config.account_number || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="Enter account number">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Routing Number</label>
                    <input type="text" name="routing_number" value="${escapeHtml(config.routing_number || '')}" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="Enter routing number">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">SWIFT/BIC Code</label>
                    <input type="text" name="swift_code" value="${escapeHtml(config.swift_code || '')}" 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary font-mono" 
                           placeholder="For international transfers">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Additional Details</label>
                    <textarea name="additional_details" rows="3" 
                              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                              placeholder="Any other important details...">${escapeHtml(config.additional_details || '')}</textarea>
                </div>
            </div>
        `;
    } else if (paymentType === 'paypal') {
        formHtml = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">PayPal Email or Tag *</label>
                    <input type="email" name="paypal_email" value="${escapeHtml(config.paypal_email || config.paypal_tag || '')}" required 
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                           placeholder="your-paypal@email.com or @yourpaypaltag">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter your PayPal email address or PayPal tag</p>
                </div>
            </div>
        `;
    }
    
    const formElement = document.createElement('form');
    formElement.id = 'paymentMethodForm';
    formElement.enctype = 'multipart/form-data';
    formElement.innerHTML = formHtml;
    
    const content = formElement.outerHTML + (isEdit ? `
        <div class="mt-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" ${existingPayment.is_active ? 'checked' : ''} 
                       class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                <span class="text-sm font-semibold text-navy-900 dark:text-white">Active</span>
            </label>
        </div>
    ` : '');
    
    const modalActions = [
        {
            label: 'Cancel',
            onclick: () => closeModal(),
            class: 'px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:opacity-90 transition-opacity'
        },
        {
            label: isEdit ? 'Update Payment Method' : 'Create Payment Method',
            onclick: () => {
                const form = document.getElementById('paymentMethodForm');
                if (form && form.checkValidity()) {
                    if (isEdit) {
                        handlePaymentFormUpdate(paymentType, form, existingPayment.id);
                    } else {
                        handlePaymentFormSubmit(paymentType, form);
                    }
                } else {
                    form.reportValidity();
                }
            },
            class: 'px-4 py-2 bg-primary text-navy-900 font-semibold rounded-lg hover:opacity-90 transition-opacity',
            icon: 'check'
        }
    ];
    
    showModal(
        isEdit ? `Edit ${paymentType === 'crypto' ? 'Cryptocurrency' : paymentType === 'bank_transfer' ? 'Bank Transfer' : 'PayPal'} Payment Method` : `Add ${paymentType === 'crypto' ? 'Cryptocurrency' : paymentType === 'bank_transfer' ? 'Bank Transfer' : 'PayPal'} Payment Method`,
        content,
        modalActions
    );
}

async function handlePaymentFormUpdate(paymentType, formElement, paymentId) {
    const formData = new FormData(formElement);
    const configData = {};
    let methodName = '';
    
    if (paymentType === 'crypto') {
        methodName = formData.get('coin_name');
        configData.coin_name = formData.get('coin_name');
        configData.network_type = formData.get('network_type');
        configData.wallet_address = formData.get('wallet_address');
        
        // Keep existing QR code if no new one uploaded
        const existingPayment = allPayments.find(p => p.id == paymentId);
        if (existingPayment?.config_data?.qr_code) {
            configData.qr_code = existingPayment.config_data.qr_code;
        }
        
        const qrFile = formData.get('qr_code');
        if (qrFile && qrFile.size > 0) {
            configData.has_qr_code = true;
        }
    } else if (paymentType === 'bank_transfer') {
        const bankName = formData.get('bank_name');
        methodName = `${bankName} Bank Transfer`;
        configData.bank_name = bankName;
        configData.account_name = formData.get('account_name');
        configData.account_number = formData.get('account_number');
        configData.routing_number = formData.get('routing_number') || '';
        configData.swift_code = formData.get('swift_code') || '';
        configData.additional_details = formData.get('additional_details') || '';
    } else if (paymentType === 'paypal') {
        const paypalEmail = formData.get('paypal_email');
        methodName = 'PayPal';
        configData.paypal_email = paypalEmail;
        configData.paypal_tag = paypalEmail.startsWith('@') ? paypalEmail : null;
    }
    
    const isActive = formElement.querySelector('input[name="is_active"]')?.checked ? 1 : 0;
    
    try {
        const updateResponse = await fetch('../../api/admin/payments.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: paymentId,
                method_name: methodName,
                method_type: paymentType,
                config_data: configData,
                is_active: isActive
            })
        });
        
        const updateData = await updateResponse.json();
        
        if (!updateData.success) {
            showToast(updateData.message || 'Failed to update payment method', 'error');
            return;
        }
        
        // Handle QR code upload if provided
        const qrFile = formElement.querySelector('input[name="qr_code"]')?.files[0];
        if (qrFile && paymentType === 'crypto' && qrFile.size > 0) {
            const uploadFormData = new FormData();
            uploadFormData.append('qr_code', qrFile);
            uploadFormData.append('payment_method_id', paymentId);
            
            const uploadResponse = await fetch('../../api/admin/payments.php?action=upload_qr', {
                method: 'POST',
                body: uploadFormData
            });
            
            const uploadData = await uploadResponse.json();
            if (!uploadData.success) {
                showToast('Payment method updated but QR code upload failed: ' + uploadData.message, 'warning');
            }
        }
        
        showToast('Payment method updated successfully', 'success');
        closeModal();
        loadPayments();
        
    } catch (error) {
        console.error('Error updating payment method:', error);
        showToast('Error updating payment method', 'error');
    }
}

async function deletePayment(id) {
    const payment = allPayments.find(p => p.id == id);
    if (!payment) {
        showToast('Payment method not found', 'error');
        return;
    }
    
    showConfirmModal(
        'Delete Payment Method',
        `Are you sure you want to delete "${escapeHtml(payment.method_name)}"? This action cannot be undone.`,
        async function() {
            try {
                const response = await fetch(`../../api/admin/payments.php?id=${id}`, { method: 'DELETE' });
                const data = await response.json();
                if (data.success) {
                    showToast('Payment method deleted successfully', 'success');
                    loadPayments();
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            } catch (error) {
                console.error('Error deleting payment:', error);
                showToast('Error deleting payment method', 'error');
            }
        }
    );
}

function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load payments on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadPayments);
} else {
    loadPayments();
}
</script>

<?php
}

// Render the layout with payments content
renderAdminLayout($page_title, 'payments', 'renderPaymentsContent');
?>
