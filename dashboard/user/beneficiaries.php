<?php
require_once __DIR__ . '/../../api/helpers.php';

require_user_page_auth('../../login.php');

$userName = $_SESSION['user_name'] ?? 'User';
$page_title = 'Beneficiaries | WyomingTrust';
$active_nav = 'beneficiaries';

include __DIR__ . '/includes/layout.php';
?>

<section>
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Beneficiaries</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">View everyone assigned to your trusts, their allocation percentages, and linked trust details.</p>
</section>

<section class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm">
<div id="beneficiariesContainer" class="p-10 text-center text-on-surface-variant">Loading beneficiaries...</div>
</section>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateSafe(value) {
    if (!value) return 'N/A';
    try {
        const s = String(value).trim();
        if (s === '' || s === '0000-00-00 00:00:00' || s === '0000-00-00') return 'N/A';
        const isoish = s.includes(' ') && !s.includes('T') ? s.replace(' ', 'T') : s;
        const d = new Date(isoish);
        if (Number.isNaN(d.getTime())) return 'N/A';
        return d.toLocaleDateString();
    } catch (e) {
        return 'N/A';
    }
}

function flattenBeneficiaries(trusts) {
    const rows = [];
    trusts.forEach(trust => {
        const trustName = trust.trust_name || trust.service_name || 'Untitled Trust';
        const trustId = trust.id || 0;
        const bens = Array.isArray(trust.beneficiaries) ? trust.beneficiaries :
            (Array.isArray(trust.trust_data?.beneficiaries) ? trust.trust_data.beneficiaries : []);

        bens.forEach(ben => {
            rows.push({
                name: ben.name || ben.full_name || 'Unnamed',
                relationship: ben.relationship || '—',
                email: ben.email || '—',
                allocation: ben.allocation != null ? parseFloat(ben.allocation) : null,
                trustName,
                trustId,
                trustStatus: trust.status || 'unknown',
            });
        });
    });
    return rows.sort((a, b) => a.name.localeCompare(b.name));
}

function renderBeneficiaries(rows) {
    const container = document.getElementById('beneficiariesContainer');
    if (!rows.length) {
        container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No beneficiaries found. <a href="../../onboarding/onboarding.php" class="text-secondary font-semibold hover:underline">Create a trust</a> to add beneficiaries.</div>';
        return;
    }

    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Beneficiary</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Relationship</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Trust</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Allocation</th>
                        <th class="px-6 md:px-8 py-5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    ${rows.map(row => `
                        <tr class="hover:bg-surface transition-colors">
                            <td class="px-6 md:px-8 py-6">
                                <p class="font-bold text-primary">${escapeHtml(row.name)}</p>
                                <p class="text-sm text-on-surface-variant">${escapeHtml(row.email)}</p>
                            </td>
                            <td class="px-6 md:px-8 py-6 text-on-surface">${escapeHtml(row.relationship)}</td>
                            <td class="px-6 md:px-8 py-6">
                                <p class="font-medium text-primary">${escapeHtml(row.trustName)}</p>
                                <p class="text-xs text-on-surface-variant capitalize">${escapeHtml(row.trustStatus)}</p>
                            </td>
                            <td class="px-6 md:px-8 py-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-secondary/10 text-secondary font-bold text-sm">
                                    ${row.allocation != null && !Number.isNaN(row.allocation) ? row.allocation.toFixed(0) + '%' : '—'}
                                </span>
                            </td>
                            <td class="px-6 md:px-8 py-6 text-right">
                                <a href="manage-trust.php?id=${row.trustId}" class="inline-flex items-center gap-1 px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary/90 transition-colors">
                                    View Trust
                                    <?php echo wt_icon('arrow-forward', 'text-sm'); ?>
                                </a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

async function loadBeneficiaries() {
    const container = document.getElementById('beneficiariesContainer');
    try {
        const response = await fetch('../../api/user/trusts.php', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            container.innerHTML = `<div class="p-10 text-center text-error">Failed to load beneficiaries (HTTP ${response.status})</div>`;
            return;
        }

        const data = await response.json();
        if (data.success && Array.isArray(data.trusts)) {
            renderBeneficiaries(flattenBeneficiaries(data.trusts));
        } else {
            container.innerHTML = '<div class="p-10 text-center text-on-surface-variant">No beneficiaries found.</div>';
        }
    } catch (error) {
        console.error(error);
        container.innerHTML = '<div class="p-10 text-center text-error">Error loading beneficiaries. Please refresh.</div>';
    }
}

document.addEventListener('DOMContentLoaded', loadBeneficiaries);
</script>

<?php include __DIR__ . '/includes/layout-footer.php'; ?>
