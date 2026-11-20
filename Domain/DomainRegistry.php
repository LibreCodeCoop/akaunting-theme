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

    public static function validSubdomain(string $host, string $baseHost, string $aliasZone = ''): bool
    {
        $host = self::normalize($host);
        $baseHost = self::normalize($baseHost);
        $aliasZone = self::normalize($aliasZone);

        // A company may use either an immediate child of the instance host
        // or, with an administrator-configured trust zone, a sibling hostname
        // under the same organizational domain. Never infer eTLD+1.
        if (self::oneLabelBelow($host, $baseHost)) {
            return true;
        }
        return $aliasZone !== '' && self::oneLabelBelow($baseHost, $aliasZone)
            && $host !== $baseHost && self::oneLabelBelow($host, $aliasZone);
    }

    private static function oneLabelBelow(string $host, string $parent): bool
    {
        if (! preg_match('/^(?:[a-z0-9-]+\.)+[a-z]{2,}$/D', $parent)) {
            return false;
        }
        $suffix = '.' . $parent;
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
        if (! self::validSubdomain($host, LoginAppearance::options()['base_host'], LoginAppearance::options()['alias_zone'])) {
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

    public static function verifyHostRoute(string $host, string $token): bool
    {
        $settings = LoginAppearance::options();
        if (! self::validSubdomain($host, $settings['base_host'], $settings['alias_zone']) ||
            ! preg_match('/^[a-f0-9]{48}$/D', $token)) {
            return false;
        }

        // Verify the same application responds through this hostname, not
        // merely a different HTTPS service sharing a valid certificate.
        // DNS is resolved to a checked public IP, then pinned for the GET.
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        foreach ($records ?: [] as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! is_string($ip) || ! filter_var($ip, FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                continue;
            }
            $address = str_contains($ip, ':') ? '[' . $ip . ']' : $ip;
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Host: {$host}\r\nConnection: close\r\n",
                    'follow_location' => 0,
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'peer_name' => $host,
                    'SNI_enabled' => true,
                    'SNI_server_name' => $host,
                ],
            ]);
            $url = 'https://' . $address . '/theme/domain-proof/' . $token;
            $stream = @fopen($url, 'rb', false, $context);
            if (! is_resource($stream)) {
                continue;
            }
            $meta = stream_get_meta_data($stream);
            $body = stream_get_contents($stream, 128);
            fclose($stream);
            $status = (string) ($meta['wrapper_data'][0] ?? '');
            if (preg_match('#^HTTP/\S+ 200(?: |$)#', $status) &&
                is_string($body) &&
                hash_equals('akaunting-theme-origin=' . $token, $body)) {
                return true;
            }
        }
        return false;
    }

    public static function verifyTls(string $host): bool
    {
        if (! self::validSubdomain($host, LoginAppearance::options()['base_host'], LoginAppearance::options()['alias_zone'])) {
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
