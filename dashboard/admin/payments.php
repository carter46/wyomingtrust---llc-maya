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
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Payment Methods Management</h1>
        <p class="text-slate-600 dark:text-slate-400 text-sm mt-1 max-w-2xl">LLC formation payment options (crypto, bank, PayPal). For crypto, pick from addresses already set under <a href="addresses.php" class="text-primary font-semibold hover:underline">Wallet Addresses</a> — QR codes are generated automatically.</p>
    </div>
    <button onclick="showPaymentTypeSelector()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto flex items-center gap-2 shrink-0">
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
let walletAddresses = [];

async function loadWalletAddresses() {
    try {
        const addrRes = await fetch('../../api/admin/addresses.php');
        const addrData = await addrRes.json();
        walletAddresses = (addrData.success && addrData.addresses) ? addrData.addresses : [];
    } catch (e) {
        console.error('Failed loading wallet addresses', e);
        walletAddresses = [];
    }
}

function getUsedWalletKeys(excludePaymentId = null) {
    const usedIds = new Set();
    const usedAddresses = new Set();
    allPayments.forEach((m) => {
        if (m.method_type !== 'crypto') return;
        if (excludePaymentId != null && Number(m.id) === Number(excludePaymentId)) return;
        const c = m.config_data || {};
        if (c.wallet_address_id) usedIds.add(Number(c.wallet_address_id));
        const addr = String(c.wallet_address || '').trim().toLowerCase();
        if (addr) usedAddresses.add(addr);
    });
    return { usedIds, usedAddresses };
}

function getAvailableWalletAddresses(excludePaymentId = null) {
    const { usedIds, usedAddresses } = getUsedWalletKeys(excludePaymentId);
    return walletAddresses.filter((a) => {
        if (usedIds.has(Number(a.id))) return false;
        const addr = String(a.address || '').trim().toLowerCase();
        if (addr && usedAddresses.has(addr)) return false;
        return true;
    });
}

function onWalletAddressSelected() {
    const select = document.getElementById('walletAddressSelect');
    const preview = document.getElementById('walletAddressPreview');
    const qrPreview = document.getElementById('walletQrPreview');
    if (!select || !preview) return;
    const addr = walletAddresses.find(a => String(a.id) === String(select.value));
    if (!addr) {
        preview.innerHTML = '<p class="text-xs text-slate-500">Select a configured wallet address.</p>';
        if (qrPreview) qrPreview.innerHTML = '';
        return;
    }
    preview.innerHTML = `
        <div class="space-y-1 text-sm">
            <p><span class="text-slate-500">Coin:</span> <strong>${escapeHtml(addr.display_name || '')}</strong> (${escapeHtml(addr.symbol || '')})</p>
            <p class="font-mono text-xs break-all"><span class="text-slate-500">Address:</span> ${escapeHtml(addr.address || '')}</p>
        </div>
    `;
    if (qrPreview) {
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=8&data=' + encodeURIComponent(addr.address || '');
        qrPreview.innerHTML = `
            <p class="text-xs font-semibold text-slate-500 mb-2">QR will be auto-generated on add</p>
            <img src="${qrUrl}" alt="QR preview" class="w-40 h-40 border border-slate-200 dark:border-slate-600 rounded-lg p-2 bg-white">
        `;
    }
}

async function loadPayments() {
    try {
        await loadWalletAddresses();
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
                            const shortAddr = (config.wallet_address || '').length > 18
                                ? `${config.wallet_address.slice(0, 10)}…${config.wallet_address.slice(-6)}`
                                : (config.wallet_address || '');
                            details = `${config.coin_name || method.method_name || ''} · ${shortAddr}`;
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
                    const shortAddr = (config.wallet_address || '').length > 18
                        ? `${config.wallet_address.slice(0, 10)}…${config.wallet_address.slice(-6)}`
                        : (config.wallet_address || '');
                    details = `${config.coin_name || method.method_name || ''} · ${shortAddr}`;
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

// Step 2 handled by unified showPaymentTypeForm(paymentType, existingPayment)

// Handle form submission with file upload support
async function handlePaymentFormSubmit(paymentType, formElement) {
    const formData = new FormData(formElement);
    const configData = {};
    let methodName = '';
    let walletAddressId = null;
    
    if (paymentType === 'crypto') {
        walletAddressId = parseInt(formElement.querySelector('#walletAddressSelect')?.value || formData.get('wallet_address_id') || '0', 10);
        if (!walletAddressId) {
            showToast('Please select a wallet address', 'error');
            return;
        }
        try {
            const createResponse = await fetch('../../api/admin/payments.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    method_type: 'crypto',
                    wallet_address_id: walletAddressId,
                    is_active: 1
                })
            });
            const createData = await createResponse.json();
            if (!createData.success) {
                showToast(createData.message || 'Failed to create payment method', 'error');
                return;
            }
            showToast('Crypto payment method added. QR code generated automatically.', 'success');
            closeModal();
            loadPayments();
        } catch (error) {
            console.error('Error creating payment method:', error);
            showToast('Error creating payment method', 'error');
        }
        return;
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

// Unified create/edit form
async function showPaymentTypeForm(paymentType, existingPayment = null) {
    const config = existingPayment?.config_data || {};
    const isEdit = !!existingPayment;
    let formHtml = '';

    if (paymentType === 'crypto') {
        await loadWalletAddresses();
        if (isEdit) {
            const qrSrc = config.qr_code
                ? `../../${escapeHtml(config.qr_code)}`
                : (config.wallet_address
                    ? `https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=8&data=${encodeURIComponent(config.wallet_address)}`
                    : '');
            formHtml = `
                <div class="space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-navy-900/40 border border-slate-200 dark:border-slate-600 rounded-lg p-3">Crypto payment methods are linked to Wallet Addresses. Address details cannot be reconfigured here — edit the wallet address instead, or delete and re-add this method.</p>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-4 space-y-2 text-sm">
                        <p><span class="text-slate-500">Coin:</span> <strong>${escapeHtml(config.coin_name || existingPayment.method_name || '')}</strong> ${config.coin_symbol ? `(${escapeHtml(config.coin_symbol)})` : ''}</p>
                        <p class="font-mono text-xs break-all"><span class="text-slate-500">Address:</span> ${escapeHtml(config.wallet_address || '—')}</p>
                    </div>
                    ${qrSrc ? `<div class="flex flex-col items-start gap-2"><p class="text-xs font-semibold text-slate-500">QR Code</p><img src="${qrSrc}" alt="QR Code" class="w-40 h-40 border border-slate-200 rounded-lg p-2 bg-white"></div>` : ''}
                </div>
            `;
        } else {
            const available = getAvailableWalletAddresses();
            if (!walletAddresses.length) {
                formHtml = `
                    <div class="space-y-3">
                        <p class="text-sm text-slate-600 dark:text-slate-300">No wallet addresses configured yet.</p>
                        <a href="addresses.php" class="inline-flex items-center gap-1 text-primary font-semibold text-sm hover:underline">Go to Wallet Addresses →</a>
                    </div>
                `;
            } else if (!available.length) {
                formHtml = `
                    <div class="space-y-3">
                        <p class="text-sm text-slate-600 dark:text-slate-300">All configured wallet addresses are already added as payment methods.</p>
                        <a href="addresses.php" class="inline-flex items-center gap-1 text-primary font-semibold text-sm hover:underline">Add another wallet address →</a>
                    </div>
                `;
            } else {
                const options = available.map(a =>
                    `<option value="${a.id}">${escapeHtml(a.display_name || '')} (${escapeHtml(a.symbol || '')}) — ${escapeHtml((a.address || '').slice(0, 10))}…${escapeHtml((a.address || '').slice(-6))}</option>`
                ).join('');
                formHtml = `
                    <div class="space-y-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-navy-900/40 border border-slate-200 dark:border-slate-600 rounded-lg p-3">Select a wallet address already configured under Wallet Addresses. Addresses already used as payment methods are hidden. The QR code is generated automatically.</p>
                        <div>
                            <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Wallet Address *</label>
                            <select id="walletAddressSelect" name="wallet_address_id" required onchange="onWalletAddressSelected()"
                                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                                <option value="">Select wallet address</option>
                                ${options}
                            </select>
                        </div>
                        <div id="walletAddressPreview" class="rounded-xl border border-slate-200 dark:border-slate-600 p-4 bg-slate-50 dark:bg-navy-900/30">
                            <p class="text-xs text-slate-500">Select a configured wallet address.</p>
                        </div>
                        <div id="walletQrPreview"></div>
                    </div>
                `;
            }
        }
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
    
    const hasWalletSelect = !!(formHtml && formHtml.includes('walletAddressSelect'));
    const cryptoBlocked = paymentType === 'crypto' && !isEdit && !hasWalletSelect;

    const content = formElement.outerHTML + (isEdit ? `
        <div class="mt-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" form="paymentMethodForm" ${existingPayment.is_active ? 'checked' : ''} 
                       class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                <span class="text-sm font-semibold text-navy-900 dark:text-white">Active</span>
            </label>
        </div>
    ` : '');
    
    const modalActions = [
        {
            label: cryptoBlocked ? 'Close' : 'Cancel',
            onclick: () => closeModal(),
            class: 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600'
        }
    ];

    if (!cryptoBlocked) {
        modalActions.push({
            label: isEdit ? 'Save' : (paymentType === 'crypto' ? 'Add Payment Method' : 'Create Payment Method'),
            onclick: () => {
                const form = document.getElementById('paymentMethodForm');
                if (!form) return;
                if (form.checkValidity()) {
                    if (isEdit) {
                        handlePaymentFormUpdate(paymentType, form, existingPayment.id);
                    } else {
                        handlePaymentFormSubmit(paymentType, form);
                    }
                } else {
                    form.reportValidity();
                }
            },
            class: 'bg-primary text-navy-900 border border-primary',
            icon: 'check'
        });
    }
    
    showModal(
        isEdit ? `Edit ${paymentType === 'crypto' ? 'Cryptocurrency' : paymentType === 'bank_transfer' ? 'Bank Transfer' : 'PayPal'} Payment Method` : `Add ${paymentType === 'crypto' ? 'Cryptocurrency' : paymentType === 'bank_transfer' ? 'Bank Transfer' : 'PayPal'} Payment Method`,
        content,
        modalActions
    );
}

async function handlePaymentFormUpdate(paymentType, formElement, paymentId) {
    const formData = new FormData(formElement);
    const existingPayment = allPayments.find(p => p.id == paymentId);
    const activeEl = formElement.querySelector('input[name="is_active"]') || document.querySelector('input[name="is_active"]');
    const isActive = activeEl ? (activeEl.checked ? 1 : 0) : (existingPayment?.is_active ? 1 : 0);

    let methodName = existingPayment?.method_name || '';
    let configData = existingPayment?.config_data || {};

    if (paymentType === 'crypto') {
        // Keep linked wallet config; only status is editable here
        methodName = existingPayment?.method_name || configData.coin_name || methodName;
        if (!configData.qr_code && configData.wallet_address) {
            // leave as-is; checkout can still show live QR
        }
    } else if (paymentType === 'bank_transfer') {
        const bankName = formData.get('bank_name');
        methodName = `${bankName} Bank Transfer`;
        configData = {
            bank_name: bankName,
            account_name: formData.get('account_name'),
            account_number: formData.get('account_number'),
            routing_number: formData.get('routing_number') || '',
            swift_code: formData.get('swift_code') || '',
            additional_details: formData.get('additional_details') || '',
        };
    } else if (paymentType === 'paypal') {
        const paypalEmail = formData.get('paypal_email');
        methodName = 'PayPal';
        configData = {
            paypal_email: paypalEmail,
            paypal_tag: paypalEmail.startsWith('@') ? paypalEmail : null,
        };
    }
    
    try {
        const updateResponse = await fetch('../../api/admin/payments.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: paymentId,
                method_name: methodName,
                method_type: paymentType,
                config_data: configData,
                is_active: isActive ? 1 : 0
            })
        });
        
        const updateData = await updateResponse.json();
        
        if (!updateData.success) {
            showToast(updateData.message || 'Failed to update payment method', 'error');
            return;
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
