<?php
/**
 * Predefined trust asset categories and field schemas.
 * Used by admin (enable/configure) and user dashboard (add assets).
 */

function get_trust_asset_category_catalog(): array {
    return [
        'real_estate' => [
            'label' => 'Real Estate',
            'icon' => 'home',
            'default_description' => 'Primary residence, vacation homes, rental property, land, and commercial buildings.',
            'subtypes' => [
                'primary_residence' => 'Primary residence',
                'vacation_home' => 'Vacation home',
                'rental_property' => 'Rental property',
                'commercial_building' => 'Commercial building',
                'vacant_land' => 'Vacant land',
                'agricultural_land' => 'Agricultural land / Farm',
                'condo_apartment' => 'Condominium or apartment',
            ],
            'fields' => [
                ['key' => 'property_name', 'label' => 'Property name (optional)', 'type' => 'text', 'required' => false],
                ['key' => 'property_address', 'label' => 'Property address', 'type' => 'text', 'required' => true],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => true],
                ['key' => 'ownership_percentage', 'label' => 'Ownership percentage', 'type' => 'number', 'required' => false, 'suffix' => '%'],
            ],
        ],
        'bank_cash' => [
            'label' => 'Bank & Cash Assets',
            'icon' => 'payments',
            'default_description' => 'Checking, savings, CDs, money market accounts, and cash holdings.',
            'subtypes' => [
                'checking' => 'Checking account',
                'savings' => 'Savings account',
                'cd' => 'Fixed deposit / Certificate of Deposit (CD)',
                'money_market' => 'Money market account',
                'cash' => 'Cash holdings',
            ],
            'fields' => [
                ['key' => 'institution_name', 'label' => 'Institution name', 'type' => 'text', 'required' => true],
                ['key' => 'account_last4', 'label' => 'Last 4 digits of account number', 'type' => 'text', 'required' => false],
                ['key' => 'estimated_balance', 'label' => 'Estimated balance', 'type' => 'currency', 'required' => true],
                ['key' => 'currency', 'label' => 'Currency', 'type' => 'text', 'required' => false, 'placeholder' => 'USD'],
            ],
        ],
        'investments' => [
            'label' => 'Investments',
            'icon' => 'trending',
            'default_description' => 'Stocks, bonds, mutual funds, ETFs, brokerage and retirement accounts.',
            'subtypes' => [
                'stocks' => 'Stocks',
                'bonds' => 'Bonds',
                'mutual_funds' => 'Mutual funds',
                'etfs' => 'ETFs',
                'brokerage' => 'Brokerage account',
                'retirement' => 'Retirement account',
            ],
            'fields' => [
                ['key' => 'institution', 'label' => 'Institution', 'type' => 'text', 'required' => true],
                ['key' => 'investment_type', 'label' => 'Investment type', 'type' => 'text', 'required' => true],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => true],
            ],
        ],
        'cryptocurrency' => [
            'label' => 'Cryptocurrency',
            'icon' => 'wallet',
            'default_description' => 'Digital assets held within the trust.',
            'subtypes' => [
                'bitcoin' => 'Bitcoin',
                'ethereum' => 'Ethereum',
                'other_crypto' => 'Other cryptocurrency',
            ],
            'fields' => [
                ['key' => 'asset_name', 'label' => 'Asset name', 'type' => 'text', 'required' => true],
                ['key' => 'quantity', 'label' => 'Quantity / Amount', 'type' => 'text', 'required' => true],
                ['key' => 'estimated_value', 'label' => 'Estimated value (optional)', 'type' => 'currency', 'required' => false],
            ],
        ],
        'business_interests' => [
            'label' => 'Business Interests',
            'icon' => 'group',
            'default_description' => 'LLC ownership, company shares, partnerships, and franchise interests.',
            'subtypes' => [
                'llc' => 'LLC ownership',
                'shares' => 'Company shares',
                'partnership' => 'Partnership interest',
                'sole_prop' => 'Sole proprietorship assets',
                'franchise' => 'Franchise ownership',
            ],
            'fields' => [
                ['key' => 'business_name', 'label' => 'Business name', 'type' => 'text', 'required' => true],
                ['key' => 'ownership_percentage', 'label' => 'Ownership percentage', 'type' => 'number', 'required' => false, 'suffix' => '%'],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => true],
            ],
        ],
        'vehicles' => [
            'label' => 'Vehicles',
            'icon' => 'car',
            'default_description' => 'Cars, trucks, boats, aircraft, and recreational vehicles.',
            'subtypes' => [
                'car' => 'Car',
                'truck' => 'Truck',
                'motorcycle' => 'Motorcycle',
                'boat' => 'Boat',
                'yacht' => 'Yacht',
                'rv' => 'RV',
                'aircraft' => 'Aircraft',
            ],
            'fields' => [
                ['key' => 'make', 'label' => 'Make', 'type' => 'text', 'required' => true],
                ['key' => 'model', 'label' => 'Model', 'type' => 'text', 'required' => true],
                ['key' => 'year', 'label' => 'Year', 'type' => 'number', 'required' => false],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => true],
                ['key' => 'registration_number', 'label' => 'Registration number (optional)', 'type' => 'text', 'required' => false],
            ],
        ],
        'valuable_personal_property' => [
            'label' => 'Valuable Personal Property',
            'icon' => 'star',
            'default_description' => 'Jewelry, watches, precious metals, artwork, antiques, and collectibles.',
            'subtypes' => [
                'jewelry' => 'Jewelry',
                'watches' => 'Watches',
                'precious_metals' => 'Precious metals',
                'artwork' => 'Artwork',
                'antiques' => 'Antiques',
                'collectibles' => 'Collectibles',
                'luxury_handbags' => 'Luxury handbags',
                'rare_coins' => 'Rare coins',
            ],
            'fields' => [
                ['key' => 'item_name', 'label' => 'Item name / description', 'type' => 'text', 'required' => true],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => true],
            ],
        ],
        'intellectual_property' => [
            'label' => 'Intellectual Property',
            'icon' => 'edit',
            'default_description' => 'Copyrights, patents, trademarks, royalties, and media rights.',
            'subtypes' => [
                'copyright' => 'Copyrights',
                'patent' => 'Patents',
                'trademark' => 'Trademarks',
                'royalties' => 'Royalties',
                'book_rights' => 'Book rights',
                'music_rights' => 'Music rights',
            ],
            'fields' => [
                ['key' => 'asset_name', 'label' => 'Asset name', 'type' => 'text', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => false],
            ],
        ],
        'insurance' => [
            'label' => 'Insurance',
            'icon' => 'shield',
            'default_description' => 'Life insurance policies and annuities held by the trust.',
            'subtypes' => [
                'life_insurance' => 'Life insurance policy',
                'annuity' => 'Annuity',
            ],
            'fields' => [
                ['key' => 'insurance_company', 'label' => 'Insurance company', 'type' => 'text', 'required' => true],
                ['key' => 'policy_number', 'label' => 'Policy number', 'type' => 'text', 'required' => false],
                ['key' => 'coverage_amount', 'label' => 'Coverage amount', 'type' => 'currency', 'required' => true],
            ],
        ],
        'other' => [
            'label' => 'Other Assets',
            'icon' => 'inventory',
            'default_description' => 'Livestock, equipment, precious stones, and other valuable property.',
            'subtypes' => [
                'livestock' => 'Livestock',
                'farm_equipment' => 'Farm equipment',
                'construction_equipment' => 'Construction equipment',
                'precious_stones' => 'Precious stones',
                'storage_units' => 'Storage units',
                'other' => 'Other valuable property',
            ],
            'fields' => [
                ['key' => 'asset_name', 'label' => 'Asset name', 'type' => 'text', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'currency', 'required' => false],
            ],
        ],
    ];
}

function get_default_asset_category_keys_for_trust_type(string $trustType): array {
    if (!trust_type_supports_asset_catalog($trustType)) {
        return [];
    }
    return array_keys(get_trust_asset_category_catalog());
}

function trust_type_supports_asset_catalog(string $trustType): bool {
    return in_array($trustType, ['irrevocable_trust', 'revocable_living_trust'], true);
}

function is_irrevocable_trust_type(string $trustType): bool {
    return $trustType === 'irrevocable_trust';
}

function is_revocable_trust_type(string $trustType): bool {
    return $trustType === 'revocable_living_trust';
}

function trust_allows_liquidation(string $trustType): bool {
    return !is_irrevocable_trust_type($trustType);
}

function get_default_asset_category_config(string $trustType): array {
    if (!trust_type_supports_asset_catalog($trustType)) {
        return [];
    }
    $catalog = get_trust_asset_category_catalog();
    $config = [];
    foreach (array_keys($catalog) as $key) {
        $config[] = [
            'key' => $key,
            'enabled' => true,
            'requires_document' => in_array($key, ['real_estate', 'business_interests', 'insurance'], true),
            'description' => $catalog[$key]['default_description'] ?? '',
        ];
    }
    return $config;
}

function normalize_asset_category_config($input, string $trustType = ''): array {
    $catalog = get_trust_asset_category_catalog();
    if ($input === null || $input === '') {
        return $trustType !== '' ? get_default_asset_category_config($trustType) : [];
    }
    if (is_string($input)) {
        $decoded = json_decode($input, true);
        $input = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
    }
    if (!is_array($input)) {
        return [];
    }

    // Legacy: flat string array from old asset_types column
    if (!empty($input) && is_string($input[0] ?? null)) {
        return get_default_asset_category_config($trustType);
    }

    $out = [];
    $seen = [];
    foreach ($input as $item) {
        if (!is_array($item)) {
            continue;
        }
        $key = sanitize_text($item['key'] ?? '');
        if ($key === '' || !isset($catalog[$key]) || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = [
            'key' => $key,
            'enabled' => !empty($item['enabled']),
            'requires_document' => !empty($item['requires_document']),
            'description' => sanitize_text($item['description'] ?? ($catalog[$key]['default_description'] ?? '')),
        ];
    }
    return $out;
}

function encode_asset_category_config_json(array $config): ?string {
    if (empty($config)) {
        return null;
    }
    return json_encode(array_values($config), JSON_UNESCAPED_UNICODE);
}

function decode_asset_category_config(?string $json, string $trustType = ''): array {
    if ($json === null || $json === '') {
        return get_default_asset_category_config($trustType);
    }
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return get_default_asset_category_config($trustType);
    }
    return normalize_asset_category_config($decoded, $trustType);
}

function get_enabled_asset_categories_for_service(string $trustType, ?string $configJson): array {
    if (!trust_type_supports_asset_catalog($trustType)) {
        return [];
    }
    $catalog = get_trust_asset_category_catalog();
    $config = decode_asset_category_config($configJson, $trustType);
    $enabled = [];
    foreach ($config as $item) {
        if (empty($item['enabled'])) {
            continue;
        }
        $key = $item['key'] ?? '';
        if (!isset($catalog[$key])) {
            continue;
        }
        $cat = $catalog[$key];
        $enabled[] = [
            'key' => $key,
            'label' => $cat['label'],
            'icon' => $cat['icon'],
            'description' => $item['description'] ?: ($cat['default_description'] ?? ''),
            'requires_document' => !empty($item['requires_document']),
            'subtypes' => $cat['subtypes'] ?? [],
            'fields' => $cat['fields'] ?? [],
        ];
    }
    return $enabled;
}

function build_trust_service_meta(array $serviceRow): array {
    $trustType = $serviceRow['service_key'] ?? '';
    $configJson = $serviceRow['asset_category_config'] ?? ($serviceRow['asset_types'] ?? null);
    $liquidationFee = isset($serviceRow['liquidation_fee']) ? (float) $serviceRow['liquidation_fee'] : 0.0;

    return [
        'trust_type' => $trustType,
        'is_irrevocable' => is_irrevocable_trust_type($trustType),
        'is_revocable' => is_revocable_trust_type($trustType),
        'is_crypto' => is_crypto_trust_type($trustType),
        'supports_assets' => trust_type_supports_asset_catalog($trustType),
        'allows_liquidation' => trust_allows_liquidation($trustType),
        'liquidation_fee' => trust_allows_liquidation($trustType) ? $liquidationFee : 0.0,
        'asset_categories' => get_enabled_asset_categories_for_service($trustType, is_string($configJson) ? $configJson : null),
        'asset_category_config' => decode_asset_category_config(is_string($configJson) ? $configJson : null, $trustType),
    ];
}

function normalize_trust_asset_entry(array $asset, array $enabledCategories): ?array {
    $byKey = [];
    foreach ($enabledCategories as $cat) {
        $byKey[$cat['key']] = $cat;
    }
    $categoryKey = sanitize_text($asset['category_key'] ?? '');
    if ($categoryKey === '' || !isset($byKey[$categoryKey])) {
        return null;
    }
    $cat = $byKey[$categoryKey];
    $subtype = sanitize_text($asset['subtype'] ?? '');
    $subtypes = $cat['subtypes'] ?? [];
    if ($subtype !== '' && !isset($subtypes[$subtype])) {
        $subtype = '';
    }

    $fieldsIn = is_array($asset['fields'] ?? null) ? $asset['fields'] : [];
    $fieldsOut = [];
    foreach ($cat['fields'] as $fieldDef) {
        $fKey = $fieldDef['key'];
        $val = sanitize_text($fieldsIn[$fKey] ?? '');
        if (!empty($fieldDef['required']) && $val === '') {
            return null;
        }
        if ($val !== '') {
            $fieldsOut[$fKey] = $val;
        }
    }

    $label = sanitize_text($asset['label'] ?? '');
    if ($label === '') {
        $label = $fieldsOut['property_name'] ?? $fieldsOut['business_name'] ?? $fieldsOut['item_name'] ?? $fieldsOut['asset_name'] ?? $fieldsOut['institution_name'] ?? $cat['label'];
    }

    $doc = null;
    if (!empty($asset['document']) && is_array($asset['document'])) {
        $doc = [
            'filename' => sanitize_text($asset['document']['filename'] ?? ''),
            'path' => sanitize_text($asset['document']['path'] ?? ''),
        ];
    }

    return [
        'id' => sanitize_text($asset['id'] ?? uniqid('asset_', true)),
        'category_key' => $categoryKey,
        'subtype' => $subtype,
        'label' => $label,
        'fields' => $fieldsOut,
        'document' => $doc,
        'created_at' => sanitize_text($asset['created_at'] ?? date('c')),
        'updated_at' => date('c'),
    ];
}

function compute_trust_assets_summary(array $assets): array {
    $totalEstimated = 0.0;
    $totalFunded = 0.0;
    $totalPending = 0.0;
    $totalUnfunded = 0.0;

    foreach ($assets as $asset) {
        $usd = get_trust_asset_usd_value($asset);
        $totalEstimated += $usd;
        $status = sanitize_text($asset['funding_status'] ?? 'unfunded');
        if ($status === 'funded') {
            $totalFunded += (float) ($asset['funded_amount_usd'] ?? $usd);
        } elseif ($status === 'pending') {
            $totalPending += (float) ($asset['funding_amount_usd'] ?? $usd);
        } else {
            $totalUnfunded += $usd;
        }
    }

    return [
        'count' => count($assets),
        'total_estimated_value' => round($totalEstimated, 2),
        'total_funded_value' => round($totalFunded, 2),
        'total_pending_value' => round($totalPending, 2),
        'total_unfunded_value' => round($totalUnfunded, 2),
    ];
}

function get_trust_asset_usd_value(array $asset): float {
    $fields = $asset['fields'] ?? [];
    foreach (['estimated_value', 'estimated_balance', 'coverage_amount'] as $key) {
        if (isset($fields[$key]) && $fields[$key] !== '') {
            return (float) preg_replace('/[^0-9.]/', '', (string) $fields[$key]);
        }
    }
    return 0.0;
}

/**
 * Declared lump-sum funding applies when the trust has a declared value amount,
 * independent of catalog assets (they are tracked separately).
 */
function trust_declared_value_funding_applies(array $trustData): bool {
    $funding = get_trust_declared_value_funding($trustData);

    return (float) ($funding['amount_usd'] ?? 0) > 0;
}

function get_trust_declared_value_funding(array $trustData): array {
    $funding = is_array($trustData['declared_value_funding'] ?? null)
        ? $trustData['declared_value_funding']
        : [];
    $declared = isset($trustData['total_estimated_value'])
        ? (float) $trustData['total_estimated_value']
        : 0.0;

    if (empty($funding) && $declared > 0) {
        return [
            'amount_usd' => $declared,
            'status' => 'unfunded',
            'funded_amount_usd' => 0.0,
            'transaction_id' => null,
        ];
    }

    return [
        'amount_usd' => (float) ($funding['amount_usd'] ?? $declared),
        'status' => sanitize_text($funding['status'] ?? 'unfunded'),
        'funded_amount_usd' => (float) ($funding['funded_amount_usd'] ?? 0),
        'transaction_id' => !empty($funding['transaction_id']) ? (int) $funding['transaction_id'] : null,
    ];
}

function get_trust_declared_funded_value(array $trustData): float {
    $funding = get_trust_declared_value_funding($trustData);
    if (($funding['status'] ?? '') === 'funded') {
        return (float) ($funding['funded_amount_usd'] ?? $funding['amount_usd'] ?? 0);
    }
    return 0.0;
}

function get_trust_declared_unverified_value(array $trustData): float {
    if (!trust_declared_value_funding_applies($trustData)) {
        return 0.0;
    }
    $funding = get_trust_declared_value_funding($trustData);
    if (($funding['status'] ?? '') === 'funded') {
        return 0.0;
    }
    return round((float) ($funding['amount_usd'] ?? 0), 2);
}

function get_trust_verified_funded_value(array $trustData): float {
    $assets = is_array($trustData['assets'] ?? null) ? $trustData['assets'] : [];
    $summary = compute_trust_assets_summary($assets);
    return round(get_trust_declared_funded_value($trustData) + (float) ($summary['total_funded_value'] ?? 0), 2);
}

function can_approve_free_trust_registration(array $trust): bool {
    $isFree = (int) ($trust['is_free'] ?? 0) === 1;
    $status = strtolower((string) ($trust['status'] ?? ''));
    $paymentStatus = strtolower((string) ($trust['payment_status'] ?? ''));
    return $isFree && $status === 'pending' && $paymentStatus === 'completed';
}

function enrich_user_trust_row(array $trust): array {
    $trustData = is_array($trust['trust_data'] ?? null) ? $trust['trust_data'] : [];
    if (!is_array($trust['trust_data'] ?? null) && !empty($trust['trust_data'])) {
        $trustData = json_decode($trust['trust_data'], true) ?? [];
    }
    $trust['trust_data'] = $trustData;
    $trust['trust_name'] = $trustData['trust_name'] ?? null;
    $trust['total_estimated_value'] = isset($trustData['total_estimated_value'])
        ? (float) $trustData['total_estimated_value']
        : null;
    $trust['business_info'] = is_array($trustData['business_info'] ?? null) ? $trustData['business_info'] : [];
    $trust['personal_info'] = is_array($trustData['personal_info'] ?? null) ? $trustData['personal_info'] : [];
    $trust['trust_type'] = $trustData['trust_type'] ?? ($trust['service_key'] ?? null);
    $trust['beneficiaries'] = $trustData['beneficiaries'] ?? [];
    $trust['assets'] = $trustData['assets'] ?? [];
    $trust['entrusted_coins'] = $trustData['entrusted_coins'] ?? [];
    $trust['service_meta'] = build_trust_service_meta($trust);
    $trust['assets_summary'] = compute_trust_assets_summary($trust['assets']);
    $trust['declared_value_funding'] = trust_declared_value_funding_applies($trustData)
        ? get_trust_declared_value_funding($trustData)
        : ['amount_usd' => 0, 'status' => 'not_applicable', 'funded_amount_usd' => 0, 'transaction_id' => null];
    $trust['declared_funded_value'] = get_trust_declared_funded_value($trustData);
    $trust['declared_unverified_value'] = get_trust_declared_unverified_value($trustData);
    $trust['verified_funded_value'] = get_trust_verified_funded_value($trustData);
    $trust['can_approve_registration'] = can_approve_free_trust_registration($trust);
    return $trust;
}

function find_trust_asset_index(array $assets, string $assetId): ?int {
    foreach ($assets as $index => $asset) {
        if (($asset['id'] ?? '') === $assetId) {
            return $index;
        }
    }
    return null;
}
