<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Domain;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CompanyTransfer
{
    private const EXPIRY_SECONDS = 45;

    public static function canonicalHost(int $companyId): ?string
    {
        $base = LoginAppearance::options()['base_host'];
        if ($base === '') {
            return null;
        }

        $domain = DomainRegistry::primaryForCompany($companyId);

        return $domain ? $domain->host : $base;
    }

    public static function allowed(User $user, int $companyId): bool
    {
        if ($companyId <= 0 || ! $user->enabled) {
            return false;
        }
        return $user->withoutEvents(static function () use ($user, $companyId): bool {
            return $user->companies()->enabled()->where('companies.id', $companyId)->exists();
        });
    }

    public static function issue(User $user, int $companyId, string $host): string
    {
        if (! config('theme.domain_routing_enabled', false)) {
            throw new \RuntimeException('Cross-domain sessions are not enabled.');
        }
        $host = DomainRegistry::normalize($host);
        if (! self::allowed($user, $companyId) ||
            $host !== self::canonicalHost($companyId) ||
            ! Schema::hasTable('lcc_theme_handoffs')) {
            throw new \RuntimeException('Unauthorized destination for cross-domain session transfer.');
        }
        // No password or session cookie is transferred. Only a short-lived,
        // single-use, audience-bound random code travels to the second origin.
        $ticket = bin2hex(random_bytes(32));
        DB::table('lcc_theme_handoffs')->insert([
            'token_hash' => hash('sha256', $ticket),
            'user_id' => $user->id,
            'company_id' => $companyId,
            'destination_host' => $host,
            'expires_at' => now()->addSeconds(self::EXPIRY_SECONDS),
            'consumed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $ticket;
    }

    public static function consume(string $ticket, string $host): ?array
    {
        if (! preg_match('/^[a-f0-9]{64}$/D', $ticket)) {
            return null;
        }
        if (! Schema::hasTable('lcc_theme_handoffs')) {
            return null;
        }
        $host = DomainRegistry::normalize($host);

        // Serializable consumption relies on row locking; only one request
        // can redeem the ticket. No ticket is accepted on a different host.
        return DB::transaction(static function () use ($ticket, $host): ?array {
            $row = DB::table('lcc_theme_handoffs')
                ->where('token_hash', hash('sha256', $ticket))
                ->where('destination_host', $host)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()->first();
            if (! $row) {
                return null;
            }
            DB::table('lcc_theme_handoffs')->where('id', $row->id)
                ->whereNull('consumed_at')->update(['consumed_at' => now()]);

            $user = User::find((int) $row->user_id);
            if (! $user || ! self::allowed($user, (int) $row->company_id) ||
                $host !== self::canonicalHost((int) $row->company_id)) {
                return null;
            }

            return ['user' => $user, 'company_id' => (int) $row->company_id];
        });
    }

    public static function redirectUrl(string $host, string $ticket): string
    {
        if (self::canonicalHostForHost($host) === null ||
            ! preg_match('/^[a-f0-9]{64}$/D', $ticket)) {
            throw new \InvalidArgumentException('Invalid transfer destination.');
        }
        return 'https://' . $host . '/theme/transfer/redeem?ticket=' . $ticket;
    }

    private static function canonicalHostForHost(string $host): ?string
    {
        $host = DomainRegistry::normalize($host);
        $base = LoginAppearance::options()['base_host'];

        if ($host === $base && $base !== '') {
            return $host;
        }
        $domain = DomainRegistry::activeForHost($host);
        return $domain && DomainRegistry::validSubdomain($host, $base, LoginAppearance::options()['alias_zone']) ? $host : null;
    }
}
