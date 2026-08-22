<?php
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Pricing Plans Management';

// Include shared layout
require_once __DIR__ . '/includes/layout.php';

function renderPricingContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Pricing Plans Management</h1>
    <button onclick="showCreatePricingModal()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto">Add New Plan</button>
</div>
<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
<div id="pricingContainer" class="p-4 sm:p-6"><div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading pricing plans...</div></div>
</div>

<script src="includes/modal.js"></script>
<script>
let allPricing = [];
async function loadPricing() {
    try {
        const response = await fetch('../../api/admin/pricing.php');
        const data = await response.json();
        if (data.success && data.plans) {
            allPricing = data.plans;
            renderPricing(data.plans);
        } else {
            document.getElementById('pricingContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load pricing plans</div>';
        }
    } catch (error) {
        console.error('Error loading pricing:', error);
        document.getElementById('pricingContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading pricing plans</div>';
    }
}

function renderPricing(plans) {
    const container = document.getElementById('pricingContainer');
    if (!plans || plans.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No pricing plans found</div>';
        return;
    }
    const html = `
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Plan Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Price</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Features</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${plans.map(plan => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 font-semibold text-sm">${escapeHtml(plan.plan_name)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">$${parseFloat(plan.price || 0).toFixed(2)}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500">${Array.isArray(plan.features) ? plan.features.length + ' features' : '0 features'}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" ${plan.is_active ? 'checked' : ''} onchange="togglePricingStatus(${plan.id}, this.checked)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="editPricing(${plan.id})" class="text-primary hover:underline text-xs sm:text-sm">Edit</button>
                                    <button onclick="deletePricing(${plan.id})" class="text-red-600 hover:underline text-xs sm:text-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            ${plans.map(plan => `
                <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-navy-900 dark:text-white truncate">${escapeHtml(plan.plan_name)}</h3>
                            <p class="text-sm font-semibold text-navy-900 dark:text-white mt-1">$${parseFloat(plan.price || 0).toFixed(2)}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-2">
                            <input type="checkbox" ${plan.is_active ? 'checked' : ''} onchange="togglePricingStatus(${plan.id}, this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-3">
                        <span>${Array.isArray(plan.features) ? plan.features.length + ' features' : '0 features'}</span>
                    </div>
                    <div class="flex gap-2 pt-3 border-t border-slate-200 dark:border-slate-600">
                        <button onclick="editPricing(${plan.id})" class="text-primary hover:underline text-xs">Edit</button>
                        <button onclick="deletePricing(${plan.id})" class="text-red-600 hover:underline text-xs">Delete</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function showCreatePricingModal() {
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Plan Name *</label>
                <input type="text" name="plan_name" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Price *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                    <input type="number" name="price" step="0.01" min="0" value="0.00" required
                           class="w-full pl-7 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Features (comma-separated)</label>
                <textarea name="features" rows="4" 
                          class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                          placeholder="Feature 1, Feature 2, Feature 3"></textarea>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter features separated by commas</p>
            </div>
        </div>
    `;
    
    showFormModal('Add New Pricing Plan', formHtml, function(data) {
        const planName = (data.plan_name || '').trim();
        const price = parseFloat(data.price || 0);
        const featuresStr = (data.features || '').trim();
        const featuresList = featuresStr ? featuresStr.split(',').map(f => f.trim()).filter(f => f) : [];
        
        if (!planName) {
            showToast('Plan name is required', 'warning');
            return;
        }
        
        createPricing(planName, price, featuresList);
    });
}

async function createPricing(name, price, features) {
    try {
        const response = await fetch('../../api/admin/pricing.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ plan_name: name, price, features })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Pricing plan created successfully', 'success');
            loadPricing();
        } else {
            showToast(data.message || 'Failed to create plan', 'error');
        }
    } catch (error) {
        console.error('Error creating plan:', error);
        showToast('Error creating plan', 'error');
    }
}

async function togglePricingStatus(id, isActive) {
    try {
        const response = await fetch('../../api/admin/pricing.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, is_active: isActive ? 1 : 0 })
        });
        const data = await response.json();
        if (!data.success) {
            showToast(data.message || 'Failed to update status', 'error');
            loadPricing();
        } else {
            showToast(`Plan ${isActive ? 'activated' : 'deactivated'} successfully`, 'success');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showToast('Error updating status', 'error');
        loadPricing();
    }
}

function editPricing(id) {
    const plan = allPricing.find(p => p.id == id);
    if (!plan) {
        showToast('Pricing plan not found', 'error');
        return;
    }
    
    const featuresStr = Array.isArray(plan.features) ? plan.features.join(', ') : '';
    
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Plan Name *</label>
                <input type="text" name="plan_name" value="${escapeHtml(plan.plan_name)}" required 
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Price *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                    <input type="number" name="price" step="0.01" min="0" value="${parseFloat(plan.price || 0).toFixed(2)}" required
                           class="w-full pl-7 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Features (comma-separated)</label>
                <textarea name="features" rows="4" 
                          class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" 
                          placeholder="Feature 1, Feature 2, Feature 3">${escapeHtml(featuresStr)}</textarea>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter features separated by commas</p>
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" ${plan.is_active ? 'checked' : ''} 
                           class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <span class="text-sm font-semibold text-navy-900 dark:text-white">Active</span>
                </label>
            </div>
        </div>
    `;
    
    showFormModal('Edit Pricing Plan', formHtml, function(data) {
        const planName = (data.plan_name || '').trim();
        const price = parseFloat(data.price || 0);
        const featuresStr = (data.features || '').trim();
        const featuresList = featuresStr ? featuresStr.split(',').map(f => f.trim()).filter(f => f) : [];
        const isActive = data.is_active === true || data.is_active === 'on';
        
        if (!planName) {
            showToast('Plan name is required', 'warning');
            return;
        }
        
        updatePricing(id, planName, price, featuresList, isActive);
    });
}

async function updatePricing(id, name, price, features, isActive) {
    try {
        const response = await fetch('../../api/admin/pricing.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                id: parseInt(id),
                plan_name: name,
                price: price,
                features: features,
                is_active: isActive ? 1 : 0
            })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Pricing plan updated successfully', 'success');
            loadPricing();
        } else {
            showToast(data.message || 'Failed to update plan', 'error');
        }
    } catch (error) {
        console.error('Error updating pricing:', error);
        showToast('Error updating plan', 'error');
    }
}

function deletePricing(id) {
    const plan = allPricing.find(p => p.id == id);
    if (!plan) {
        showToast('Pricing plan not found', 'error');
        return;
    }
    
    showConfirmModal(
        'Delete Pricing Plan',
        `Are you sure you want to delete "${escapeHtml(plan.plan_name)}"? This action cannot be undone.`,
        async function() {
    try {
        const response = await fetch(`../../api/admin/pricing.php?id=${id}`, { method: 'DELETE' });
        const data = await response.json();
            if (data.success) {
                showToast('Plan deleted successfully', 'success');
                loadPricing();
            } else {
                showToast(data.message || 'Failed to delete', 'error');
            }
        } catch (error) {
            console.error('Error deleting plan:', error);
            showToast('Error deleting plan', 'error');
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

// Load pricing on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadPricing);
} else {
    loadPricing();
}
</script>

<?php
}

// Render the layout with pricing content
renderAdminLayout($page_title, 'pricing', 'renderPricingContent');
?>
