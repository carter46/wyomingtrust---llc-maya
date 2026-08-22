/**
 * Shared admin trust detail HTML renderer.
 */
(function (global) {
    const ENDING_LABELS = {
        none: 'Prefer no ending',
        llc: 'LLC',
        limited_liability_company: 'Limited Liability Company',
        corp: 'Corp',
        corporation: 'Corporation',
        inc: 'Inc',
        incorporated: 'Incorporated',
    };

    function escapeHtml(text) {
        if (text == null) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    function formatUsd(value) {
        const n = parseFloat(value) || 0;
        return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function statusBadge(status, paymentStatus) {
        const s = (status || '').toString().toLowerCase();
        const ps = (paymentStatus || '').toString().toLowerCase();
        let label = s || 'unknown';
        let cls = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
        if (ps === 'rejected') {
            label = 'payment rejected';
            cls = 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400';
        } else if (s === 'pending') {
            cls = 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400';
        } else if (s === 'active') {
            cls = 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400';
        } else if (s === 'inactive') {
            cls = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
        }
        return `<span class="px-2 py-0.5 rounded text-xs font-semibold ${cls}">${escapeHtml(label.replace(/_/g, ' '))}</span>`;
    }

    function renderTrustDetailHtml(trust, options = {}) {
        const opts = Object.assign({
            showUserInfo: true,
            showPaymentDetails: true,
            showValueSplit: true,
        }, options);

        const trustData = trust.trust_data || {};
        const personalInfo = trust.personal_info || trustData.personal_info || {};
        const businessInfo = trust.business_info || trustData.business_info || {};
        const beneficiaries = trust.beneficiaries || trustData.beneficiaries || [];
        const paymentInfo = trustData.payment_info || {};

        const personFirst = personalInfo.first_name || '';
        const personLast = personalInfo.last_name || '';
        const personName = [personFirst, personLast].filter(Boolean).join(' ').trim() || personalInfo.full_name || '';
        const hasPersonalBlock = !!(personName || personalInfo.email || personalInfo.phone || personalInfo.street);
        const hasBusinessBlock = !!(
            trust.trust_name ||
            trustData.trust_name ||
            businessInfo.company_name ||
            businessInfo.formation_state ||
            businessInfo.business_ending ||
            trust.total_estimated_value != null ||
            trustData.total_estimated_value != null
        );

        const endingLabel = ENDING_LABELS[businessInfo.business_ending] || businessInfo.business_ending || '';
        const companyDisplay = businessInfo.company_name
            ? (businessInfo.business_ending && businessInfo.business_ending !== 'none' && endingLabel
                ? `${businessInfo.company_name} ${endingLabel}`
                : businessInfo.company_name)
            : '';

        const trustName = trust.trust_name || trustData.trust_name || 'Untitled LLC';
        const isFree = Number(trust.is_free) === 1;
        const createdAt = trust.created_at ? new Date(trust.created_at).toLocaleString() : 'N/A';
        const declaredUnverified = parseFloat(trust.declared_unverified_value ?? 0) || 0;
        const verifiedFunded = parseFloat(trust.verified_funded_value ?? 0) || 0;

        return `
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">LLC Information</h4>
                    <div class="space-y-1 text-sm">
                        <div><span class="text-slate-500 dark:text-slate-400">LLC ID:</span> <span class="font-mono">#${escapeHtml(trust.id)}</span></div>
                        <div><span class="text-slate-500 dark:text-slate-400">LLC Name:</span> ${escapeHtml(trustName)}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Service:</span> ${escapeHtml(trust.service_name || 'N/A')}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Type:</span> ${isFree ? 'Free' : 'Paid'}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Formation Fee:</span> <span class="font-semibold">${isFree ? 'Free' : formatUsd(trust.price || 0)}</span></div>
                        <div><span class="text-slate-500 dark:text-slate-400">LLC Status:</span> ${statusBadge(trust.status, trust.payment_status)}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Payment Status:</span> ${escapeHtml((trust.payment_status || 'N/A').replace(/_/g, ' '))}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Created:</span> ${escapeHtml(createdAt)}</div>
                        ${!isFree && trust.payment_method_name ? `<div><span class="text-slate-500 dark:text-slate-400">Payment Method:</span> ${escapeHtml(trust.payment_method_name)}</div>` : ''}
                    </div>
                </div>

                ${opts.showUserInfo ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">User Information</h4>
                    <div class="space-y-1 text-sm">
                        <div><span class="text-slate-500 dark:text-slate-400">Name:</span> ${escapeHtml(trust.user_name || 'N/A')}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Email:</span> ${escapeHtml(trust.user_email || 'N/A')}</div>
                    </div>
                </div>
                ` : ''}

                ${hasBusinessBlock ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Business Information</h4>
                    <div class="space-y-1 text-sm">
                        ${businessInfo.company_name ? `<div><span class="text-slate-500 dark:text-slate-400">Company Name:</span> ${escapeHtml(businessInfo.company_name)}</div>` : ''}
                        ${endingLabel ? `<div><span class="text-slate-500 dark:text-slate-400">Business Ending:</span> ${escapeHtml(endingLabel)}</div>` : ''}
                        ${companyDisplay ? `<div><span class="text-slate-500 dark:text-slate-400">Display Name:</span> ${escapeHtml(companyDisplay)}</div>` : ''}
                        ${businessInfo.formation_state ? `<div><span class="text-slate-500 dark:text-slate-400">Formation State / Jurisdiction:</span> ${escapeHtml(businessInfo.formation_state)}</div>` : ''}
                        ${(trust.total_estimated_value != null || trustData.total_estimated_value != null) ? `<div><span class="text-slate-500 dark:text-slate-400">Declared Asset Value:</span> ${formatUsd(trust.total_estimated_value ?? trustData.total_estimated_value ?? 0)}</div>` : ''}
                    </div>
                </div>
                ` : ''}

                ${hasPersonalBlock ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Personal Information</h4>
                    <div class="space-y-1 text-sm">
                        ${personFirst || personLast ? `
                            <div><span class="text-slate-500 dark:text-slate-400">First Name:</span> ${escapeHtml(personFirst || 'N/A')}</div>
                            <div><span class="text-slate-500 dark:text-slate-400">Last Name:</span> ${escapeHtml(personLast || 'N/A')}</div>
                        ` : `
                            <div><span class="text-slate-500 dark:text-slate-400">Name:</span> ${escapeHtml(personName || 'N/A')}</div>
                        `}
                        ${personalInfo.email ? `<div><span class="text-slate-500 dark:text-slate-400">Email:</span> ${escapeHtml(personalInfo.email)}</div>` : ''}
                        ${personalInfo.phone ? `<div><span class="text-slate-500 dark:text-slate-400">Phone:</span> ${escapeHtml(personalInfo.phone)}</div>` : ''}
                        ${personalInfo.street ? `<div><span class="text-slate-500 dark:text-slate-400">Address:</span> ${escapeHtml([personalInfo.street, personalInfo.city, personalInfo.state, personalInfo.zip].filter(Boolean).join(', '))}</div>` : ''}
                    </div>
                </div>
                ` : ''}

                ${opts.showValueSplit ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Assets / Value</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-3 rounded-lg border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-900/10">
                            <p class="text-xs font-bold uppercase text-amber-800 dark:text-amber-300">Declared / Unverified</p>
                            <p class="text-lg font-bold text-navy-900 dark:text-white mt-1">${formatUsd(declaredUnverified)}</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">${declaredUnverified > 0 ? 'Unverified — not yet deposited' : 'No unverified declared value'}</p>
                        </div>
                        <div class="p-3 rounded-lg border border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-900/10">
                            <p class="text-xs font-bold uppercase text-green-800 dark:text-green-300">Verified / Funded</p>
                            <p class="text-lg font-bold text-navy-900 dark:text-white mt-1">${formatUsd(verifiedFunded)}</p>
                            <p class="text-xs text-green-700 dark:text-green-400 mt-1">Verified assets</p>
                        </div>
                    </div>
                </div>
                ` : ''}

                ${beneficiaries.length > 0 ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Beneficiaries (${beneficiaries.length})</h4>
                    <div class="space-y-2 text-sm">
                        ${beneficiaries.map((ben) => `
                            <div class="p-2 bg-slate-50 dark:bg-navy-700/50 rounded">
                                <div class="font-medium">${escapeHtml(ben.name || 'N/A')}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(ben.relationship || 'N/A')} - ${parseFloat(ben.allocation || 0).toFixed(1)}%</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                ${opts.showPaymentDetails && paymentInfo.amount ? `
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white mb-2">Payment Details</h4>
                    <div class="space-y-1 text-sm">
                        <div><span class="text-slate-500 dark:text-slate-400">Amount:</span> ${formatUsd(paymentInfo.amount)}</div>
                        <div><span class="text-slate-500 dark:text-slate-400">Confirmed:</span> ${paymentInfo.user_confirmed ? 'Yes' : 'No'}</div>
                        ${paymentInfo.confirmed_at ? `<div><span class="text-slate-500 dark:text-slate-400">Confirmed At:</span> ${escapeHtml(new Date(paymentInfo.confirmed_at).toLocaleString())}</div>` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }

    function renderTrustPickerHtml(user, trusts) {
        return `
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Select an LLC to view details for <strong>${escapeHtml(user.full_name || user.email || 'this user')}</strong>.</p>
            <div class="space-y-3 max-h-[60vh] overflow-y-auto">
                ${trusts.map((trust) => {
                    const isFree = Number(trust.is_free) === 1;
                    const declared = parseFloat(trust.declared_unverified_value ?? trust.total_estimated_value ?? 0) || 0;
                    return `
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-navy-700/40">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-navy-900 dark:text-white truncate">${escapeHtml(trust.trust_name || 'Untitled LLC')}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${escapeHtml(trust.service_name || 'LLC')} · ${isFree ? 'Free' : 'Paid'} · #${escapeHtml(trust.id)}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Status: ${escapeHtml((trust.status || 'pending').replace(/_/g, ' '))} · Payment: ${escapeHtml((trust.payment_status || 'N/A').replace(/_/g, ' '))}</p>
                                    ${declared > 0 ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Declared: ${formatUsd(declared)}</p>` : ''}
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Created: ${escapeHtml(trust.created_at ? new Date(trust.created_at).toLocaleDateString() : 'N/A')}</p>
                                </div>
                                <button type="button" onclick="openAdminTrustDetailModal(${trust.id}, ${user.id})" class="shrink-0 px-4 py-2 rounded-lg bg-primary text-navy-900 text-sm font-semibold hover:opacity-90">View</button>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    global.TrustDetailRender = {
        renderTrustDetailHtml,
        renderTrustPickerHtml,
        escapeHtml,
        formatUsd,
    };
})(window);
