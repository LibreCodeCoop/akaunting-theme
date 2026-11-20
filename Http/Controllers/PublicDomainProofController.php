<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Theme\Domain\DomainRegistry;
use Modules\Theme\Domain\LoginAppearance;
use Symfony\Component\HttpFoundation\Response;

final class PublicDomainProofController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $host = DomainRegistry::normalize($request->getHost());
        $opts = LoginAppearance::options();

        if (! preg_match('/^[a-f0-9]{48}$/D', $token) ||
            ! DomainRegistry::validSubdomain($host, $opts['base_host'], $opts['alias_zone']) ||
            ! Schema::hasTable('lcc_theme_domains')) {
            abort(404);
        }

        // Publicly proves that this exact host reaches the same Akaunting
        // instance whose database owns the pending registration.
        $exists = DB::table('lcc_theme_domains')
            ->where('host', $host)
            ->where('status', 'pending')
            ->where('verification_token', $token)->exists();
        abort_unless($exists, 404);

        return response('akaunting-theme-origin=' . $token, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
