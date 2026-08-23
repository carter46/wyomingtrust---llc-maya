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
            cls = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
        } else if (s === 'active') {
            cls = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        } else if (s === 'inactive') {
            cls = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
        }
        return `<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold capitalize ${cls}">${escapeHtml(label.replace(/_/g, ' '))}</span>`;
    }

    function fieldItem(label, valueHtml) {
        return `
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">${escapeHtml(label)}</p>
                <div class="text-sm font-medium text-navy-900 dark:text-white break-words">${valueHtml}</div>
            </div>
        `;
    }

    function sectionCard(title, icon, bodyHtml) {
        return `
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-navy-800/80 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-navy-700/50">
                    <span class="material-icons-outlined text-primary text-lg">${escapeHtml(icon)}</span>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-white">${escapeHtml(title)}</h4>
                </div>
                <div class="p-4">
                    ${bodyHtml}
                </div>
            </div>
        `;
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
        const addressLine = [personalInfo.street, personalInfo.city, personalInfo.state, personalInfo.zip].filter(Boolean).join(', ');

        const llcCard = sectionCard('LLC Information', 'business', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${fieldItem('LLC ID', `<span class="font-mono">#${escapeHtml(trust.id)}</span>`)}
                ${fieldItem('LLC Name', escapeHtml(trustName))}
                ${fieldItem('Service', escapeHtml(trust.service_name || 'N/A'))}
                ${fieldItem('Type', isFree ? 'Free' : 'Paid')}
                ${fieldItem('Formation Fee', `<span class="font-semibold">${isFree ? 'Free' : formatUsd(trust.price || 0)}</span>`)}
                ${fieldItem('LLC Status', statusBadge(trust.status, trust.payment_status))}
                ${fieldItem('Payment Status', escapeHtml((trust.payment_status || 'N/A').replace(/_/g, ' ')))}
                ${fieldItem('Created', escapeHtml(createdAt))}
                ${!isFree && trust.payment_method_name ? fieldItem('Payment Method', escapeHtml(trust.payment_method_name)) : ''}
            </div>
        `);

        const userCard = opts.showUserInfo ? sectionCard('User Information', 'person', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${fieldItem('Account Name', escapeHtml(trust.user_name || 'N/A'))}
                ${fieldItem('Account Email', escapeHtml(trust.user_email || 'N/A'))}
            </div>
        `) : '';

        const businessCard = hasBusinessBlock ? sectionCard('Business Information', 'apartment', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${businessInfo.company_name ? fieldItem('Company Name', escapeHtml(businessInfo.company_name)) : ''}
                ${endingLabel ? fieldItem('Business Ending', escapeHtml(endingLabel)) : ''}
                ${companyDisplay ? fieldItem('Display Name', escapeHtml(companyDisplay)) : ''}
                ${businessInfo.formation_state ? fieldItem('Formation State / Jurisdiction', escapeHtml(businessInfo.formation_state)) : ''}
                ${(trust.total_estimated_value != null || trustData.total_estimated_value != null)
                    ? fieldItem('Declared Asset Value', formatUsd(trust.total_estimated_value ?? trustData.total_estimated_value ?? 0))
                    : ''}
            </div>
        `) : '';

        const personalCard = hasPersonalBlock ? sectionCard('Personal Information', 'badge', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${personFirst || personLast ? `
                    ${fieldItem('First Name', escapeHtml(personFirst || 'N/A'))}
                    ${fieldItem('Last Name', escapeHtml(personLast || 'N/A'))}
                ` : fieldItem('Name', escapeHtml(personName || 'N/A'))}
                ${personalInfo.email ? fieldItem('Email', escapeHtml(personalInfo.email)) : ''}
                ${personalInfo.phone ? fieldItem('Phone', escapeHtml(personalInfo.phone)) : ''}
                ${addressLine ? `<div class="sm:col-span-2">${fieldItem('Address', escapeHtml(addressLine))}</div>` : ''}
            </div>
        `) : '';

        const valueCard = opts.showValueSplit ? sectionCard('Assets / Value', 'account_balance_wallet', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/15 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Declared / Unverified</p>
                    <p class="text-xl font-bold text-navy-900 dark:text-white mt-2">${formatUsd(declaredUnverified)}</p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-2">${declaredUnverified > 0 ? 'Unverified — not yet deposited' : 'No unverified declared value'}</p>
                </div>
                <div class="rounded-xl border border-green-200 dark:border-green-800/50 bg-green-50 dark:bg-green-900/15 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-green-800 dark:text-green-300">Verified / Funded</p>
                    <p class="text-xl font-bold text-navy-900 dark:text-white mt-2">${formatUsd(verifiedFunded)}</p>
                    <p class="text-xs text-green-700 dark:text-green-400 mt-2">Verified assets</p>
                </div>
            </div>
        `) : '';

        const shareHoldersCard = beneficiaries.length > 0 ? sectionCard(`Share Holders (${beneficiaries.length})`, 'groups', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                ${beneficiaries.map((ben) => `
                    <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-navy-700/40 p-4">
                        <p class="font-semibold text-navy-900 dark:text-white truncate">${escapeHtml(ben.name || 'N/A')}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${escapeHtml(ben.relationship || 'N/A')}</p>
                        <p class="text-sm font-bold text-primary mt-2">${parseFloat(ben.allocation || 0).toFixed(1)}% allocation</p>
                        ${ben.email ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate">${escapeHtml(ben.email)}</p>` : ''}
                    </div>
                `).join('')}
            </div>
        `) : '';

        const paymentCard = (opts.showPaymentDetails && paymentInfo.amount) ? sectionCard('Payment Details', 'payments', `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${fieldItem('Amount', formatUsd(paymentInfo.amount))}
                ${fieldItem('Confirmed', paymentInfo.user_confirmed ? 'Yes' : 'No')}
                ${paymentInfo.confirmed_at ? fieldItem('Confirmed At', escapeHtml(new Date(paymentInfo.confirmed_at).toLocaleString())) : ''}
            </div>
        `) : '';

        return `
            <div class="space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    ${llcCard}
                    ${userCard}
                    ${businessCard}
                    ${personalCard}
                </div>
                ${valueCard}
                ${shareHoldersCard}
                ${paymentCard}
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
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-navy-700/40 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-navy-900 dark:text-white truncate">${escapeHtml(trust.trust_name || 'Untitled LLC')}</p>
                                    <div class="mt-2 flex flex-wrap gap-2 items-center">
                                        ${statusBadge(trust.status, trust.payment_status)}
                                        <span class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(trust.service_name || 'LLC')} · ${isFree ? 'Free' : 'Paid'} · #${escapeHtml(trust.id)}</span>
                                    </div>
                                    ${declared > 0 ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Declared: ${formatUsd(declared)}</p>` : ''}
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Created: ${escapeHtml(trust.created_at ? new Date(trust.created_at).toLocaleDateString() : 'N/A')}</p>
                                </div>
                                <button type="button" onclick="openAdminTrustDetailModal(${trust.id}, ${user.id})" class="shrink-0 px-4 py-2.5 rounded-lg bg-primary text-navy-900 text-sm font-semibold hover:opacity-90">View Details</button>
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
