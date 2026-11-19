<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Domain;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class Appearance
{
    public const DEFAULTS = [
        'brand_name' => '',
        'logo' => '',
        'favicon' => '',
        'login_background' => '',
        'loading_spinner' => '',
        'login_slider' => true,
        'sidebar_color' => '#ffffff',
        'menu_color' => '#ffffff',
        'content_color' => '#f8f9fa',
        'support_url' => '',
        'documentation_url' => '',
        'footer_credit' => '',
    ];

    public static function options(int $companyId): array
    {
        if ($companyId <= 0 || ! Schema::hasTable('lcc_theme_companies')) {
            return self::DEFAULTS;
        }

        $row = DB::table('lcc_theme_companies')->where('company_id', $companyId)->first();
        $value = $row ? json_decode($row->options, true) : null;

        return array_replace(self::DEFAULTS, is_array($value) ? array_intersect_key($value, self::DEFAULTS) : []);
    }

    public static function update(int $companyId, array $options): void
    {
        if ($companyId <= 0) {
            throw new \InvalidArgumentException('Invalid company.');
        }

        $options = array_replace(self::options($companyId), array_intersect_key($options, self::DEFAULTS));
        DB::table('lcc_theme_companies')->upsert(
            [[
                'company_id' => $companyId,
                'options' => json_encode($options, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['company_id'],
            ['options', 'updated_at']
        );
    }

    public static function mode(int $userId): string
    {
        if ($userId <= 0 || ! Schema::hasTable('lcc_theme_preferences')) {
            return 'system';
        }

        $mode = DB::table('lcc_theme_preferences')->where('user_id', $userId)->value('mode');

        return in_array($mode, ['system', 'light', 'dark'], true) ? $mode : 'system';
    }

    public static function setMode(int $userId, string $mode): void
    {
        if ($userId <= 0 || ! in_array($mode, ['system', 'light', 'dark'], true)) {
            throw new \InvalidArgumentException('Invalid theme preference.');
        }

        DB::table('lcc_theme_preferences')->upsert(
            [[
                'user_id' => $userId,
                'mode' => $mode,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['user_id'],
            ['mode', 'updated_at']
        );
    }
}
