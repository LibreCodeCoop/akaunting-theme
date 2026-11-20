<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Domain;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LoginAppearance
{
    public const DEFAULTS = [
        'base_host' => '',
        'alias_zone' => '',
        'brand_name' => '',
        'footer_text' => '',
        'footer_url' => '',
        'logo' => '',
        'favicon' => '',
        'login_background' => '',
        'background_opacity' => 10,
        'login_slider' => true,
        'slides' => [],
    ];

    public static function options(): array
    {
        if (! Schema::hasTable('lcc_theme_login_defaults')) {
            return self::DEFAULTS;
        }

        $row = DB::table('lcc_theme_login_defaults')->where('id', 1)->first();
        $value = $row ? json_decode($row->options, true) : null;

        return array_replace(self::DEFAULTS, is_array($value)
            ? array_intersect_key($value, self::DEFAULTS) : []);
    }

    public static function update(array $changes): void
    {
        $value = array_replace(self::options(), array_intersect_key($changes, self::DEFAULTS));
        DB::table('lcc_theme_login_defaults')->upsert([[
            'id' => 1,
            'options' => json_encode($value, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['id'], ['options', 'updated_at']);
    }

    public static function allowedViewer(): bool
    {
        $user = auth()->user();

        // This is public branding, not a secret. Company-settings readers
        // may inspect the instance defaults without permission to edit them.
        return (bool) ($user && ($user->can('read-settings-company')
            || self::allowedEditor()));
    }

    public static function allowedEditor(): bool
    {
        $user = auth()->user();
        // Akaunting treats the Company management capability as a privileged,
        // instance-wide operation, unlike normal company-settings permission.
        return (bool) ($user && $user->can('create-common-companies')
            && $user->can('update-common-companies')
            && $user->can('delete-common-companies'));
    }

    public static function forHost(string $requestHost): ?array
    {
        $options = self::options();
        $host = strtolower(rtrim($requestHost, '.'));
        $baseHost = strtolower(trim($options['base_host']));

        if ($baseHost === '' || $host === '') {
            // Preserve legacy opt-in until the instance-admin configures a
            // canonical host. Never infer the tenant from a request parameter.
            $legacyId = (int) config('theme.login_company_id', 0);
            return $legacyId > 0 ? Appearance::options($legacyId) : null;
        }
        if ($host === $baseHost) {
            return $options;
        }
        $domain = DomainRegistry::activeForHost($host);
        if (! $domain || ! DomainRegistry::validSubdomain($host, $baseHost, (string) $options['alias_zone'])) {
            return null;
        }

        $company = Appearance::options((int) $domain->company_id);
        $merged = $options;
        foreach (['brand_name', 'logo', 'favicon', 'login_background'] as $key) {
            if ($company[$key] !== '') {
                $merged[$key] = $company[$key];
            }
        }
        if (trim((string) ($company['footer_credit'] ?? '')) !== '') {
            $merged['footer_text'] = $company['footer_credit'];
            // Never inherit an unrelated global link for a tenant credit.
            $merged['footer_url'] = $company['footer_url'];
        }
        $merged['login_slider'] = $company['login_inherit_slider']
            ? (bool) $options['login_slider'] : (bool) $company['login_slider'];
        $merged['slides'] = $company['login_slides'] ?: $options['slides'];

        return $merged;
    }
}
