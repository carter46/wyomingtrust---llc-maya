/**
 * Trust asset management UI for irrevocable/revocable trusts.
 */
(function (global) {
    const CATEGORY_ICONS = {
        real_estate: '🏠',
        bank_cash: '🏦',
        investments: '📈',
        cryptocurrency: '₿',
        business_interests: '🏢',
        vehicles: '🚗',
        valuable_personal_property: '💍',
        intellectual_property: '📜',
        insurance: '📄',
        other: '📦',
    };

    function formatCurrency(val) {
        const n = parseFloat(String(val || '').replace(/[^0-9.]/g, ''));
        if (Number.isNaN(n)) return null;
        return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function getAssetDisplayValue(asset) {
        const fields = asset.fields || {};
        for (const k of ['estimated_value', 'estimated_balance', 'coverage_amount', 'quantity']) {
            if (fields[k]) {
                if (k === 'quantity') return fields[k];
                const fmt = formatCurrency(fields[k]);
                if (fmt) return fmt;
            }
        }
        return '—';
    }

    function renderAssetList(assets, categories, containerId, onRemove, trustId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        if (!assets.length) {
            container.innerHTML = '<div class="text-center py-8 text-on-surface-variant text-sm">No assets added yet.</div>';
            return;
        }
        const catLabels = {};
        categories.forEach(c => { catLabels[c.key] = c.label; });
        container.innerHTML = assets.map(asset => {
            const icon = CATEGORY_ICONS[asset.category_key] || '📁';
            const catLabel = catLabels[asset.category_key] || asset.category_key;
            const value = getAssetDisplayValue(asset);
            const valueLabel = (asset.fields || {}).quantity ? 'Amount' : 'Estimated Value';
            return `
                <div class="flex items-start justify-between gap-4 p-4 border border-outline-variant rounded-xl bg-surface-container-lowest">
                    <div class="flex gap-3 min-w-0">
                        <span class="text-2xl shrink-0" aria-hidden="true">${icon}</span>
                        <div class="min-w-0">
                            <p class="font-bold text-primary truncate">${escapeHtml(asset.label || catLabel)}</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">${escapeHtml(catLabel)}${asset.subtype ? ' · ' + escapeHtml(asset.subtype.replace(/_/g, ' ')) : ''}</p>
                            <p class="text-sm font-semibold text-on-surface mt-1">${valueLabel}: ${escapeHtml(String(value))}</p>
                            ${renderFundingBadge(asset)}
                            ${asset.document?.filename ? `<p class="text-xs text-secondary mt-1">📎 ${escapeHtml(asset.document.filename)}</p>` : ''}
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        ${renderFundingAction(asset, trustId)}
                        <button type="button" class="text-error text-sm font-bold hover:underline" data-remove-asset="${escapeHtml(asset.id)}">Remove</button>
                    </div>
                </div>
            `;
        }).join('');
        container.querySelectorAll('[data-remove-asset]').forEach(btn => {
            btn.addEventListener('click', () => onRemove(btn.getAttribute('data-remove-asset')));
        });
        container.querySelectorAll('[data-fund-asset]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-fund-asset');
                if (!trustId || !id) return;
                window.location.href = `checkout.php?type=asset_funding&trust_id=${encodeURIComponent(trustId)}&asset_id=${encodeURIComponent(id)}`;
            });
        });
    }

    function renderFundingBadge(asset) {
        const status = asset.funding_status || 'unfunded';
        const amount = parseFloat(asset.funding_amount_usd || 0);
        if (amount <= 0) return '';
        if (status === 'funded') {
            return '<p class="text-xs font-bold text-deep-forest mt-1">Funded</p>';
        }
        if (status === 'pending') {
            return '<p class="text-xs font-bold text-secondary mt-1">Deposit pending approval</p>';
        }
        if (status === 'rejected') {
            return '<p class="text-xs font-bold text-error mt-1">Deposit rejected — resubmit payment</p>';
        }
        return '<p class="text-xs font-bold text-amber-700 mt-1">Deposit required to fund this value</p>';
    }

    function renderFundingAction(asset, trustId) {
        const status = asset.funding_status || 'unfunded';
        const amount = parseFloat(asset.funding_amount_usd || 0);
        if (!trustId || amount <= 0 || status === 'funded' || status === 'pending') return '';
        return `<button type="button" data-fund-asset="${escapeHtml(asset.id)}" class="text-secondary text-sm font-bold hover:underline">Deposit Value</button>`;
    }

    function buildFieldInput(field) {
        const req = field.required ? 'required' : '';
        const ph = field.placeholder ? ` placeholder="${escapeHtml(field.placeholder)}"` : '';
        const suffix = field.suffix ? `<span class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">${escapeHtml(field.suffix)}</span>` : '';
        const pad = field.suffix ? ' pr-10' : '';
        if (field.type === 'textarea') {
            return `<textarea name="field_${field.key}" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm${pad}" ${req}${ph}></textarea>`;
        }
        const type = field.type === 'currency' || field.type === 'number' ? 'text' : (field.type || 'text');
        return `<div class="relative"><input type="${type}" name="field_${field.key}" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm${pad}" ${req}${ph}>${suffix}</div>`;
    }

    function showAddAssetModal(categories, trustId, onSaved) {
        if (!categories.length) {
            alert('No asset categories are enabled for this trust type.');
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[70] flex items-center justify-center p-4 bg-primary/30 backdrop-blur-sm';
        overlay.innerHTML = `
            <div class="bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-outline-variant">
                <div class="p-6 border-b border-outline-variant flex justify-between items-center">
                    <h3 class="font-headline-md text-primary">Add Asset</h3>
                    <button type="button" id="closeAssetModal" class="p-2 hover:bg-surface-container rounded-full">✕</button>
                </div>
                <form id="addAssetForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">Category *</label>
                        <div class="space-y-2" id="categoryPicker">
                            ${categories.map(c => `
                                <label class="flex items-start gap-3 p-3 border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container has-[:checked]:border-secondary has-[:checked]:bg-secondary/5">
                                    <input type="radio" name="category_key" value="${escapeHtml(c.key)}" class="mt-1" required>
                                    <span class="text-xl">${CATEGORY_ICONS[c.key] || '📁'}</span>
                                    <span>
                                        <span class="font-semibold text-primary block">${escapeHtml(c.label)}</span>
                                        <span class="text-xs text-on-surface-variant">${escapeHtml(c.description || '')}</span>
                                    </span>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                    <div id="assetDetailFields" class="space-y-3 hidden"></div>
                    <div id="documentUploadWrap" class="hidden">
                        <label class="block text-sm font-bold mb-2">Supporting Document *</label>
                        <input type="file" id="assetDocument" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-sm">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" id="cancelAssetBtn" class="flex-1 py-2.5 rounded-lg border border-outline-variant font-bold">Cancel</button>
                        <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary text-on-primary font-bold">Save Asset</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(overlay);

        const form = overlay.querySelector('#addAssetForm');
        const detailEl = overlay.querySelector('#assetDetailFields');
        const docWrap = overlay.querySelector('#documentUploadWrap');
        let selectedCat = null;

        function renderCategoryFields(key) {
            selectedCat = categories.find(c => c.key === key);
            if (!selectedCat) {
                detailEl.classList.add('hidden');
                docWrap.classList.add('hidden');
                return;
            }
            detailEl.classList.remove('hidden');
            const subtypes = selectedCat.subtypes || {};
            const subtypeKeys = Object.keys(subtypes);
            let html = '';
            if (subtypeKeys.length) {
                html += `<div><label class="block text-sm font-bold mb-1">Type</label><select name="subtype" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm"><option value="">Select...</option>${subtypeKeys.map(k => `<option value="${escapeHtml(k)}">${escapeHtml(subtypes[k])}</option>`).join('')}</select></div>`;
            }
            (selectedCat.fields || []).forEach(f => {
                html += `<div><label class="block text-sm font-bold mb-1">${escapeHtml(f.label)}${f.required ? ' *' : ''}</label>${buildFieldInput(f)}</div>`;
            });
            detailEl.innerHTML = html;
            if (selectedCat.requires_document) {
                docWrap.classList.remove('hidden');
            } else {
                docWrap.classList.add('hidden');
            }
        }

        overlay.querySelectorAll('input[name="category_key"]').forEach(r => {
            r.addEventListener('change', () => renderCategoryFields(r.value));
        });

        function close() { overlay.remove(); }
        overlay.querySelector('#closeAssetModal').onclick = close;
        overlay.querySelector('#cancelAssetBtn').onclick = close;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!selectedCat) return;
            const fd = new FormData(form);
            const categoryKey = fd.get('category_key');
            const fields = {};
            (selectedCat.fields || []).forEach(f => {
                const v = fd.get('field_' + f.key);
                if (v) fields[f.key] = String(v).trim();
            });
            let documentMeta = null;
            const fileInput = overlay.querySelector('#assetDocument');
            if (selectedCat.requires_document && fileInput?.files?.[0]) {
                const uploadFd = new FormData();
                uploadFd.append('trust_id', trustId);
                uploadFd.append('document', fileInput.files[0]);
                const upRes = await fetch('../../api/user/trust-asset-document.php', { method: 'POST', body: uploadFd, credentials: 'same-origin' });
                const upData = await upRes.json();
                if (!upData.success) {
                    alert(upData.message || 'Document upload failed');
                    return;
                }
                documentMeta = upData.document;
            } else if (selectedCat.requires_document) {
                alert('Please upload a supporting document.');
                return;
            }

            const asset = {
                category_key: categoryKey,
                subtype: fd.get('subtype') || '',
                fields,
                document: documentMeta,
            };

            const res = await fetch('../../api/user/trust-assets.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trust_id: trustId, asset }),
            });
            const data = await res.json();
            if (data.success) {
                close();
                onSaved(data);
            } else {
                alert(data.message || 'Failed to save asset');
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    global.TrustAssetUI = {
        renderAssetList,
        showAddAssetModal,
        formatCurrency,
        getAssetDisplayValue,
        CATEGORY_ICONS,
    };
})(window);
