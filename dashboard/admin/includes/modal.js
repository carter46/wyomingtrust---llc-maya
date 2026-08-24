/**
 * Reusable Modal Component for Admin Dashboard
 * Replaces browser alerts, confirms, and prompts with styled modals
 */

// Modal container element
let modalContainer = null;

/**
 * Initialize modal system
 */
function initModalSystem() {
    if (modalContainer) return;
    
    modalContainer = document.createElement('div');
    modalContainer.id = 'modalContainer';
    modalContainer.className = 'fixed inset-0 z-50 hidden';
    modalContainer.innerHTML = `
        <div class="modal-backdrop fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal()"></div>
        <div class="modal-content fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="modal-dialog bg-white dark:bg-navy-800 rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto pointer-events-auto transform transition-all" style="opacity: 0; transform: scale(0.95);">
                <div class="modal-header flex items-center justify-between p-5 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="modal-title text-lg sm:text-xl font-bold text-navy-900 dark:text-white pr-2"></h3>
                    <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-navy-700 transition-colors shrink-0" aria-label="Close">
                        <span class="material-icons-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body p-6"></div>
                <div class="modal-footer flex items-center justify-end gap-3 p-6 border-t border-slate-200 dark:border-slate-700"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modalContainer);
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalContainer.classList.contains('hidden')) {
            closeModal();
        }
    });
}

/**
 * Show a generic modal
 * @param {string} title - Modal title
 * @param {string} content - HTML content for modal body
 * @param {Array} actions - Array of action buttons [{label, onclick, class, icon}]
 * @param {Object} options - Optional { wide: boolean }
 */
function showModal(title, content, actions = [], options = {}) {
    initModalSystem();
    
    const dialog = modalContainer.querySelector('.modal-dialog');
    const wide = !!(options && options.wide);
    dialog.classList.toggle('max-w-md', !wide);
    dialog.classList.toggle('max-w-3xl', false);
    dialog.classList.toggle('max-w-4xl', wide);
    dialog.classList.toggle('max-w-2xl', false);

    modalContainer.querySelector('.modal-title').textContent = title;
    modalContainer.querySelector('.modal-body').innerHTML = content;
    
    const footer = modalContainer.querySelector('.modal-footer');
    footer.innerHTML = '';
    footer.className = 'modal-footer flex flex-col-reverse sm:flex-row sm:flex-wrap items-stretch sm:items-center justify-end gap-3 p-5 sm:p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-navy-900/40';
    
    const baseBtn = 'inline-flex w-full sm:w-auto items-center justify-center gap-1.5 px-5 py-3 rounded-lg text-sm font-semibold transition-opacity hover:opacity-90 shadow-sm border border-transparent';

    const normalizeOnClick = (handler) => {
        if (typeof handler === 'function') return handler;
        // Legacy string handlers (e.g. "closeModal()")
        if (typeof handler === 'string' && handler.indexOf('closeModal') !== -1) {
            return () => closeModal();
        }
        return () => closeModal();
    };

    if (actions.length === 0) {
        footer.innerHTML = `
            <button type="button" onclick="closeModal()" class="${baseBtn} bg-primary text-navy-900 min-w-[6.5rem]">
                Close
            </button>
        `;
    } else {
        actions.forEach(action => {
            const button = document.createElement('button');
            button.type = 'button';
            const custom = (action.class || '').trim();
            button.className = custom
                ? `${baseBtn} ${custom}`
                : `${baseBtn} bg-primary text-navy-900`;
            button.innerHTML = action.icon
                ? `<span class="material-icons-outlined text-base leading-none">${action.icon}</span><span>${action.label}</span>`
                : action.label;
            button.onclick = normalizeOnClick(action.onclick);
            footer.appendChild(button);
        });
    }
    
    modalContainer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Animate in
    setTimeout(() => {
        dialog.style.opacity = '1';
        dialog.style.transform = 'scale(1)';
    }, 10);
}

/**
 * Show a confirmation modal
 * @param {string} title - Modal title
 * @param {string} message - Confirmation message
 * @param {Function} onConfirm - Callback when confirmed
 * @param {Function} onCancel - Optional callback when cancelled
 */
function showConfirmModal(title, message, onConfirm, onCancel = null) {
    const content = `
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                <span class="material-icons-outlined text-amber-600 dark:text-amber-400 text-2xl">warning</span>
            </div>
            <div class="flex-1">
                <p class="text-slate-700 dark:text-slate-300">${escapeHtml(message)}</p>
            </div>
        </div>
    `;
    
    const actions = [
        {
            label: 'Cancel',
            onclick: () => {
                closeModal();
                if (onCancel) onCancel();
            },
            class: 'px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:opacity-90 transition-opacity'
        },
        {
            label: 'Confirm',
            onclick: () => {
                closeModal();
                if (onConfirm) onConfirm();
            },
            class: 'px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:opacity-90 transition-opacity',
            icon: 'check'
        }
    ];
    
    showModal(title, content, actions);
}

/**
 * Show a form modal
 * @param {string} title - Modal title
 * @param {string} formHtml - HTML form content
 * @param {Function} onSubmit - Callback when form is submitted (receives form data object)
 * @param {Function} onCancel - Optional callback when cancelled
 */
function showFormModal(title, formHtml, onSubmit, onCancel = null) {
    const content = `
        <form id="modalForm" onsubmit="event.preventDefault(); handleFormSubmit(event);" class="space-y-4">
            ${formHtml}
        </form>
    `;
    
    // Store callbacks in modal container
    modalContainer.dataset.onSubmit = JSON.stringify({ hasCallback: !!onSubmit });
    modalContainer._onSubmit = onSubmit;
    modalContainer._onCancel = onCancel;
    
    const actions = [
        {
            label: 'Cancel',
            onclick: () => {
                closeModal();
                if (onCancel) onCancel();
            },
            class: 'px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:opacity-90 transition-opacity'
        },
        {
            label: 'Submit',
            onclick: () => {
                const form = document.getElementById('modalForm');
                if (form.checkValidity()) {
                    handleFormSubmit(new Event('submit'));
                } else {
                    form.reportValidity();
                }
            },
            class: 'px-4 py-2 bg-primary text-navy-900 font-semibold rounded-lg hover:opacity-90 transition-opacity',
            icon: 'check'
        }
    ];
    
    showModal(title, content, actions);
}

/**
 * Handle form submission
 */
function handleFormSubmit(event) {
    event.preventDefault();
    const form = document.getElementById('modalForm');
    const formData = new FormData(form);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            const baseKey = key.slice(0, -2);
            if (!Array.isArray(data[baseKey])) {
                data[baseKey] = [];
            }
            data[baseKey].push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Also check for checkboxes and radio buttons
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            data[input.name] = input.checked;
        } else if (input.type === 'radio' && input.checked) {
            data[input.name] = input.value;
        } else if (!data[input.name] && input.value && !input.name.endsWith('[]')) {
            data[input.name] = input.value;
        }
    });

    // Normalize dynamic array fields (e.g. asset_types[])
    if (Array.isArray(data.asset_types)) {
        data.asset_types = data.asset_types.map(v => String(v || '').trim()).filter(Boolean);
    }
    
    // Admin trust form: asset category config
    const catEnabled = form.querySelectorAll('[name^="cat_enabled_"]');
    if (catEnabled.length) {
        data.asset_category_config = [];
        catEnabled.forEach(cb => {
            const key = cb.name.replace('cat_enabled_', '');
            data.asset_category_config.push({
                key,
                enabled: cb.checked,
                requires_document: !!form.querySelector('[name="cat_doc_' + key + '"]')?.checked,
                description: (form.querySelector('[name="cat_desc_' + key + '"]')?.value || '').trim(),
            });
        });
    }

    if (modalContainer._onSubmit) {
        modalContainer._onSubmit(data);
    }
    closeModal();
}

/**
 * Close the current modal
 */
function closeModal() {
    if (!modalContainer) return;
    
    const dialog = modalContainer.querySelector('.modal-dialog');
    dialog.style.opacity = '0';
    dialog.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        modalContainer.classList.add('hidden');
        document.body.style.overflow = '';
        // Reset width for next open
        dialog.classList.add('max-w-md');
        dialog.classList.remove('max-w-4xl', 'max-w-3xl', 'max-w-2xl');
        
        // Clear callbacks
        delete modalContainer._onSubmit;
        delete modalContainer._onCancel;
    }, 200);
}

/**
 * Show a toast notification
 * @param {string} message - Toast message
 * @param {string} type - 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    const icons = {
        success: 'check_circle',
        error: 'error',
        info: 'info',
        warning: 'warning'
    };
    
    const colors = {
        success: 'bg-green-50 dark:bg-green-900/20 border-green-400 text-green-700 dark:text-green-400',
        error: 'bg-red-50 dark:bg-red-900/20 border-red-400 text-red-700 dark:text-red-400',
        info: 'bg-blue-50 dark:bg-blue-900/20 border-blue-400 text-blue-700 dark:text-blue-400',
        warning: 'bg-amber-50 dark:bg-amber-900/20 border-amber-400 text-amber-700 dark:text-amber-400'
    };
    
    toast.className = `fixed top-20 right-4 ${colors[type] || colors.info} border px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 transform transition-all animate-slide-in`;
    toast.innerHTML = `
        <span class="material-icons-outlined text-sm">${icons[type] || icons.info}</span>
        <span>${escapeHtml(message)}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModalSystem);
} else {
    initModalSystem();
}
