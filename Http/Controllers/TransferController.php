<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Theme\Domain\CompanyTransfer;

final class TransferController extends Controller
{
    public function redeem(Request $request): RedirectResponse
    {
        abort_unless(config('theme.domain_routing_enabled', false), 404);
        // Never process access tokens outside a registered canonical/company
        // host. Request host comes from TrustHosts + TrustedProxies config.
        $value = $request->query('ticket', '');
        $record = CompanyTransfer::consume(is_string($value) ? $value : '', $request->getHost());
        abort_unless($record !== null, 403);

        auth()->login($record['user']);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // The destination host was verified and company membership checked.
        // Use a local path; never replay an untrusted user-supplied URL.
        $suffix = $record['user']->isCustomer() ? '/portal' : '';
        $response = redirect('/' . $record['company_id'] . $suffix);
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
