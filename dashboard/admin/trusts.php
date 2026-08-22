<?php
require_once __DIR__ . '/../../api/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Trust Services Management';

require_once __DIR__ . '/includes/layout.php';

function renderTrustsContent() {
?>

<div class="mb-4 sm:mb-6 lg:mb-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl sm:text-3xl font-bold text-navy-900 dark:text-white">Trust Services</h1>
        <button id="addTrustBtn" onclick="showCreateTrustModal()" class="bg-primary text-navy-900 px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold text-sm sm:text-base hover:opacity-90 w-full sm:w-auto flex items-center justify-center gap-2 shrink-0">
            <span class="material-icons-outlined text-sm">add</span>
            <span>Add Trust Service</span>
        </button>
    </div>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-2xl">Configure trust offerings by category and display name.</p>
</div>

<div id="messageContainer" class="mb-3 sm:mb-4"></div>
<div class="bg-white dark:bg-navy-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div id="trustsContainer" class="p-4 sm:p-6">
        <div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">Loading trust services...</div>
    </div>
</div>

<script src="includes/modal.js"></script>
<script>
let allTrusts = [];
let trustTypeOptions = {};
let assetCategoryCatalog = {};

async function loadTrusts() {
    try {
        const response = await fetch('../../api/admin/trusts.php');
        const data = await response.json();
        if (data.success && data.trusts) {
            allTrusts = data.trusts;
            trustTypeOptions = data.trust_type_options || {};
            assetCategoryCatalog = data.asset_category_catalog || {};
            renderSchemaDiagnostics(data.schema_diagnostics);
            renderTrusts(data.trusts);
        } else {
            document.getElementById('trustsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Failed to load trust services</div>';
        }
    } catch (error) {
        console.error('Error loading trusts:', error);
        document.getElementById('trustsContainer').innerHTML = '<div class="text-center py-10 text-red-500">Error loading trust services</div>';
    }
}

function renderSchemaDiagnostics(diagnostics) {
    const container = document.getElementById('messageContainer');
    if (!container || !diagnostics) return;

    if (diagnostics.allows_multiple_per_category) {
        container.innerHTML = '';
        return;
    }

    const indexes = Array.isArray(diagnostics.unique_service_key_indexes)
        ? diagnostics.unique_service_key_indexes
        : [];
    const dropSql = indexes.map(name => `ALTER TABLE trust_services DROP INDEX \`${name}\`;`).join(' ');
    container.innerHTML = `
        <div class="rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 px-4 py-3 text-sm text-amber-900 dark:text-amber-200">
            <p class="font-semibold mb-1">Database still limits one row per trust category</p>
            <p class="mb-2">Unique index(es) on <code>service_key</code>: ${escapeHtml(indexes.join(', ') || 'unknown')}</p>
            <p class="text-xs break-all">${escapeHtml(dropSql || 'SHOW INDEX FROM trust_services;')}</p>
            <p class="text-xs mt-2">After dropping, recreate lookup index: <code>ALTER TABLE trust_services ADD KEY idx_service_key (service_key);</code></p>
        </div>
    `;
}

function renderTrusts(trusts) {
    const container = document.getElementById('trustsContainer');
    if (!trusts || trusts.length === 0) {
        container.innerHTML = '<div class="text-center py-8 sm:py-10 text-slate-500 text-sm sm:text-base">No trust services configured yet. Click <strong>Add Trust Service</strong> to get started.</div>';
        return;
    }

    const html = `
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-navy-700">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Category</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Display Name</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Asset Types</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Price</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-xs font-bold uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-navy-700">
                    ${trusts.map(trust => renderTrustRow(trust)).join('')}
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-4">
            ${trusts.map(trust => renderTrustCard(trust)).join('')}
        </div>
    `;
    container.innerHTML = html;
}

function renderAssetTypesSummary(trust) {
    if (trust.is_crypto) {
        return '<span class="text-xs text-slate-500">User deposits crypto</span>';
    }
    if (!trust.supports_asset_catalog) {
        return '<span class="text-xs text-slate-500">N/A</span>';
    }
    const config = Array.isArray(trust.asset_category_config) ? trust.asset_category_config : [];
    const enabled = config.filter(c => c.enabled).length;
    if (enabled === 0) {
        return '<span class="text-xs text-amber-600">No categories enabled</span>';
    }
    return `<span class="text-xs text-slate-600 dark:text-slate-300">${enabled} categories enabled</span>`;
}

function renderTrustRow(trust) {
    return `
        <tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50">
            <td class="px-4 sm:px-6 py-3 sm:py-4">
                <p class="font-semibold text-sm">${escapeHtml(trust.trust_type_label || trust.service_key)}</p>
                <p class="text-xs text-slate-500 mt-0.5">${escapeHtml(trust.service_key)}</p>
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-medium">${escapeHtml(trust.service_name)}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">${renderAssetTypesSummary(trust)}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm">${trust.is_free ? '<span class="text-green-600 font-bold">FREE</span>' : '$' + parseFloat(trust.price || 0).toFixed(2)}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">${renderStatusToggle(trust)}</td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">${renderActions(trust)}</td>
        </tr>
    `;
}

function renderTrustCard(trust) {
    return `
        <div class="bg-slate-50 dark:bg-navy-700/50 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-sm text-navy-900 dark:text-white">${escapeHtml(trust.service_name)}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${escapeHtml(trust.trust_type_label || trust.service_key)}</p>
                </div>
                ${renderStatusToggle(trust)}
            </div>
            <div class="text-xs text-slate-500 mb-2">${renderAssetTypesSummary(trust)}</div>
            <div class="flex items-center justify-between text-xs sm:text-sm mb-3">
                <span class="${trust.is_free ? 'text-green-600 font-bold' : 'text-slate-500 dark:text-slate-400'}">${trust.is_free ? 'FREE' : '$' + parseFloat(trust.price || 0).toFixed(2)}</span>
            </div>
            <div class="flex gap-2 pt-3 border-t border-slate-200 dark:border-slate-600">${renderActions(trust, true)}</div>
        </div>
    `;
}

function renderStatusToggle(trust) {
    return `
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" ${trust.is_active ? 'checked' : ''} onchange="toggleTrustStatus(${trust.id}, this.checked)" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
        </label>
    `;
}

function renderActions(trust, mobile = false) {
    const size = mobile ? 'text-xs' : 'text-xs sm:text-sm';
    return `
        <button onclick="editTrust(${trust.id})" class="text-primary hover:underline ${size} flex items-center gap-1">
            <span class="material-icons-outlined ${mobile ? 'text-xs' : 'text-sm'}">edit</span>
            <span>Edit</span>
        </button>
        <button onclick="deleteTrust(${trust.id})" class="text-red-600 hover:underline ${size} flex items-center gap-1">
            <span class="material-icons-outlined ${mobile ? 'text-xs' : 'text-sm'}">delete</span>
            <span>Delete</span>
        </button>
    `;
}

function buildTrustTypeSelect(selectedKey = '', disabled = false) {
    const options = Object.entries(trustTypeOptions).map(([key, label]) =>
        `<option value="${escapeHtml(key)}" ${key === selectedKey ? 'selected' : ''}>${escapeHtml(label)}</option>`
    ).join('');
    return `
        <select name="trust_type" id="trustTypeSelect" required ${disabled ? 'disabled' : ''} onchange="onTrustTypeChange()"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            <option value="">Select trust type...</option>
            ${options}
        </select>
    `;
}

function supportsAssetCatalog(trustType) {
    return trustType === 'irrevocable_trust' || trustType === 'revocable_living_trust';
}

function allowsLiquidation(trustType) {
    return trustType !== 'irrevocable_trust';
}

function buildAssetCategorySection(config = [], trustType = '') {
    const hiddenClass = supportsAssetCatalog(trustType) ? '' : 'hidden';
    const configMap = {};
    (config || []).forEach(c => { configMap[c.key] = c; });
    const catalog = assetCategoryCatalog || {};
    const rows = Object.entries(catalog).map(([key, cat]) => {
        const item = configMap[key] || { key, enabled: supportsAssetCatalog(trustType), requires_document: false, description: cat.default_description || '' };
        return `
            <div class="border border-slate-200 dark:border-slate-600 rounded-lg p-3 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-semibold text-sm">
                    <input type="checkbox" name="cat_enabled_${key}" ${item.enabled ? 'checked' : ''} class="rounded text-primary">
                    ${escapeHtml(cat.label || key)}
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 dark:text-slate-400 ml-6">
                    <input type="checkbox" name="cat_doc_${key}" ${item.requires_document ? 'checked' : ''} class="rounded text-primary">
                    Require supporting document upload
                </label>
                <textarea name="cat_desc_${key}" rows="2" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-xs ml-0"
                          placeholder="Description shown to users">${escapeHtml(item.description || cat.default_description || '')}</textarea>
            </div>
        `;
    }).join('');

    return `
        <div id="assetTypesSection" class="${hiddenClass} border-t border-slate-200 dark:border-slate-700 pt-4 mt-2">
            <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Asset Categories</label>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Select which asset types users can add. Configure document requirements and descriptions per category.</p>
            <div class="space-y-3 max-h-64 overflow-y-auto">${rows || '<p class="text-xs text-slate-500">Loading catalog...</p>'}</div>
        </div>
    `;
}

function onTrustTypeChange() {
    const select = document.getElementById('trustTypeSelect');
    const section = document.getElementById('assetTypesSection');
    const liqSection = document.getElementById('liquidationFeeSection');
    if (!select) return;

    const key = select.value;
    const showAssets = supportsAssetCatalog(key);
    const showLiq = allowsLiquidation(key);

    if (section) section.classList.toggle('hidden', !showAssets);
    if (liqSection) liqSection.classList.toggle('hidden', !showLiq);
}

function buildPricingFields(isFree = false, price = '0.00', liquidationFee = '0.00', trustType = '') {
    const showLiq = allowsLiquidation(trustType);
    return `
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_free" id="modalIsFree" ${isFree ? 'checked' : ''} onchange="toggleModalPriceField()"
                       class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                <span class="text-sm font-semibold text-navy-900 dark:text-white">Mark as Free Service</span>
            </label>
            <p class="text-xs text-slate-500 ml-6 mt-1">Setup/onboarding price — separate from liquidation fee.</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Setup Price</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                <input type="number" name="price" id="modalPriceInput" step="0.01" min="0" value="${isFree ? '0.00' : price}" required
                       class="w-full pl-7 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary"
                       ${isFree ? 'disabled' : ''}>
            </div>
        </div>
        <div id="liquidationFeeSection" class="${showLiq ? '' : 'hidden'}">
            <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Liquidation Fee</label>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Fee charged when a user liquidates this trust type. Not applicable to irrevocable trusts.</p>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                <input type="number" name="liquidation_fee" step="0.01" min="0" value="${parseFloat(liquidationFee || 0).toFixed(2)}"
                       class="w-full pl-7 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
        </div>
    `;
}

function toggleModalPriceField() {
    const checkbox = document.getElementById('modalIsFree');
    const priceInput = document.getElementById('modalPriceInput');
    if (checkbox && priceInput) {
        if (checkbox.checked) {
            priceInput.value = '0.00';
            priceInput.disabled = true;
        } else {
            priceInput.disabled = false;
        }
    }
}

function showCreateTrustModal() {
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Trust Category *</label>
                ${buildTrustTypeSelect()}
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Controls onboarding behavior (asset catalog, liquidation rules, crypto flow). You can create multiple offerings per category.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Display Name *</label>
                <input type="text" name="service_name" id="serviceNameInput" required
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary"
                       placeholder="e.g. Premium Revocable Living Trust">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary"
                          placeholder="Brief description shown on onboarding and marketing pages"></textarea>
            </div>
            ${buildPricingFields()}
            ${buildAssetCategorySection([], '')}
        </div>
    `;

    showFormModal('Add Trust Service', formHtml, function(data) {
        const trustType = (data.trust_type || '').trim();
        const serviceName = (data.service_name || '').trim();
        const description = (data.description || '').trim();
        const isFree = data.is_free === true || data.is_free === 'on';
        const price = isFree ? 0 : parseFloat(data.price || 0);
        const liquidationFee = parseFloat(data.liquidation_fee || 0);

        if (!trustType || !serviceName) {
            showToast('Trust type and display name are required', 'warning');
            return;
        }

        createTrust({
            trust_type: trustType,
            service_name: serviceName,
            description,
            price,
            is_free: isFree ? 1 : 0,
            liquidation_fee: liquidationFee,
            asset_category_config: data.asset_category_config || [],
        });
    });

    setTimeout(onTrustTypeChange, 50);
}

async function createTrust(payload) {
    try {
        const response = await fetch('../../api/admin/trusts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ...payload, is_active: 1 })
        });
        const raw = await response.text();
        let data = null;
        try {
            data = raw ? JSON.parse(raw) : null;
        } catch (parseError) {
            console.error('Create trust non-JSON response:', raw);
            showToast('Server error while creating trust service. Check the browser console for details.', 'error');
            return;
        }
        if (data.success) {
            showToast('Trust service created successfully', 'success');
            loadTrusts();
        } else {
            console.error('Create trust failed:', data);
            showToast(data.message || 'Failed to create trust service', 'error');
            if (data.schema_diagnostics) {
                renderSchemaDiagnostics(data.schema_diagnostics);
            }
        }
    } catch (error) {
        console.error('Error creating trust:', error);
        showToast('Error creating trust service', 'error');
    }
}

async function toggleTrustStatus(id, isActive) {
    try {
        const response = await fetch('../../api/admin/trusts.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, is_active: isActive ? 1 : 0 })
        });
        const data = await response.json();
        if (!data.success) {
            showToast(data.message || 'Failed to update status', 'error');
            loadTrusts();
        } else {
            showToast(`Service ${isActive ? 'activated' : 'deactivated'} successfully`, 'success');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showToast('Error updating status', 'error');
        loadTrusts();
    }
}

function editTrust(id) {
    const trust = allTrusts.find(t => t.id == id);
    if (!trust) {
        showToast('Trust service not found', 'error');
        return;
    }

    const assetConfig = Array.isArray(trust.asset_category_config) ? trust.asset_category_config : [];
    const formHtml = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Trust Category</label>
                <input type="text" value="${escapeHtml(trust.trust_type_label || trust.service_key)}" disabled
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-navy-900 text-slate-600 dark:text-slate-400 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Display Name *</label>
                <input type="text" name="service_name" value="${escapeHtml(trust.service_name)}" required
                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-navy-900 dark:text-white mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-navy-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">${escapeHtml(trust.description || '')}</textarea>
            </div>
            ${buildPricingFields(!!trust.is_free, trust.is_free ? '0.00' : parseFloat(trust.price || 0).toFixed(2), parseFloat(trust.liquidation_fee || 0).toFixed(2), trust.service_key)}
            ${buildAssetCategorySection(assetConfig, trust.service_key)}
        </div>
    `;

    showFormModal('Edit Trust Service', formHtml, function(data) {
        const serviceName = (data.service_name || '').trim();
        const description = (data.description || '').trim();
        const isFree = data.is_free === true || data.is_free === 'on';
        const price = isFree ? 0 : parseFloat(data.price || 0);
        const payload = {
            id: parseInt(id, 10),
            service_name: serviceName,
            description,
            price,
            is_free: isFree ? 1 : 0,
            liquidation_fee: parseFloat(data.liquidation_fee || 0),
            asset_category_config: data.asset_category_config || [],
        };
        if (!supportsAssetCatalog(trust.service_key)) {
            delete payload.asset_category_config;
        }
        updateTrust(payload);
    });
}

async function updateTrust(payload) {
    try {
        const response = await fetch('../../api/admin/trusts.php', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            showToast('Trust type updated successfully', 'success');
            loadTrusts();
        } else {
            showToast(data.message || 'Failed to update trust type', 'error');
        }
    } catch (error) {
        console.error('Error updating trust:', error);
        showToast('Error updating trust type', 'error');
    }
}

async function deleteTrust(id) {
    const trust = allTrusts.find(t => t.id == id);
    if (!trust) {
        showToast('Trust service not found', 'error');
        return;
    }

    showConfirmModal(
        'Delete Trust Service',
        `Are you sure you want to delete "${escapeHtml(trust.service_name)}"? This action cannot be undone.`,
        async function() {
            try {
                const response = await fetch(`../../api/admin/trusts.php?id=${id}`, { method: 'DELETE' });
                const data = await response.json();
                if (data.success) {
                    showToast('Trust type deleted successfully', 'success');
                    loadTrusts();
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            } catch (error) {
                console.error('Error deleting trust:', error);
                showToast('Error deleting trust type', 'error');
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadTrusts);
} else {
    loadTrusts();
}
</script>

<?php
}

renderAdminLayout($page_title, 'trusts', 'renderTrustsContent');
?>
