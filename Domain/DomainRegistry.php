<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Domain;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DomainRegistry
{
    public static function normalize(string $host): string
    {
        return strtolower(rtrim(trim($host), '.'));
    }

    public static function validSubdomain(string $host, string $baseHost): bool
    {
        $host = self::normalize($host);
        $baseHost = self::normalize($baseHost);
        if ($baseHost === '' || ! preg_match('/^(?:[a-z0-9-]+\.)+[a-z]{2,}$/D', $baseHost)) {
            return false;
        }

        // Exactly one label below the configured instance host. No arbitrary
        // customer-supplied redirect domains, IP literals or wildcard hosts.
        $suffix = '.' . $baseHost;
        if (! str_ends_with($host, $suffix)) {
            return false;
        }
        $label = substr($host, 0, -strlen($suffix));

        return (bool) preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/D', $label);
    }

    public static function activeForHost(string $host): ?object
    {
        if (! Schema::hasTable('lcc_theme_domains')) {
            return null;
        }
        $domain = DB::table('lcc_theme_domains')
            ->where('host', self::normalize($host))
            ->where('status', 'active')->first();
        if (! $domain) {
            return null;
        }

        // An inactive or deleted company must never retain a branded login
        // even if a historic domain record remains marked active.
        $enabled = DB::table('companies')->where('id', (int) $domain->company_id)
            ->where('enabled', 1)->whereNull('deleted_at')->exists();

        return $enabled ? $domain : null;
    }

    public static function primaryForCompany(int $companyId): ?object
    {
        if ($companyId <= 0 || ! Schema::hasTable('lcc_theme_domains')) {
            return null;
        }
        return DB::table('lcc_theme_domains')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderByDesc('is_primary')->orderBy('id')->first();
    }

    public static function verifyDns(string $host, string $token): bool
    {
        if (! self::validSubdomain($host, LoginAppearance::options()['base_host'])) {
            return false;
        }
        $records = dns_get_record('_akaunting-theme.' . $host, DNS_TXT);
        foreach ($records ?: [] as $record) {
            if (hash_equals('akaunting-theme-verification=' . $token, (string) ($record['txt'] ?? ''))) {
                return true;
            }
        }
        return false;
    }

    public static function verifyTls(string $host): bool
    {
        if (! self::validSubdomain($host, LoginAppearance::options()['base_host'])) {
            return false;
        }
        // Resolve and reject private/reserved destinations before opening TLS.
        // Connect to the checked IP to prevent DNS rebinding during verification.
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        $publicIps = [];
        foreach ($records ?: [] as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $publicIps[] = $ip;
            }
        }
        if ($publicIps === []) {
            return false;
        }
        $ip = $publicIps[0];
        $address = str_contains($ip, ':') ? '[' . $ip . ']' : $ip;

        $context = stream_context_create(['ssl' => [
            'verify_peer' => true, 'verify_peer_name' => true,
            'peer_name' => $host, 'SNI_enabled' => true,
            'SNI_server_name' => $host,
        ]]);
        $client = @stream_socket_client('tls://' . $address . ':443', $errorNo, $error,
            5, STREAM_CLIENT_CONNECT, $context);
        if (! is_resource($client)) {
            return false;
        }
        fclose($client);
        return true;
    }
}
