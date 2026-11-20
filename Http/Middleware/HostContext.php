<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Theme\Domain\CompanyTransfer;
use Modules\Theme\Domain\DomainRegistry;
use Modules\Theme\Domain\LoginAppearance;
use Symfony\Component\HttpFoundation\Response;

final class HostContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('theme.domain_routing_enabled', false)) {
            return $next($request);
        }
        $base = LoginAppearance::options()['base_host'];
        if ($base === '') {
            // No configured mappings: preserve existing Akaunting behavior.
            return $next($request);
        }

        $host = DomainRegistry::normalize($request->getHost());
        $isCanonical = hash_equals($base, $host);
        $domain = $isCanonical ? null : DomainRegistry::activeForHost($host);

        if (! $isCanonical &&
            (! $domain || ! DomainRegistry::validSubdomain($host, $base, LoginAppearance::options()['alias_zone']))) {
            abort(404, 'Unknown instance domain.');
        }

        // Do not rewrite public handoff, instance-global settings, management,
        // API or background routes. These are instance/cross-company actions.
        $route = (string) ($request->route()?->getName() ?? '');
        $exempt = str_starts_with($route, 'theme.public.') ||
            str_starts_with($route, 'theme.login.global.') ||
            str_starts_with($route, 'companies.index') ||
            str_starts_with($route, 'companies.edit') ||
            str_starts_with($route, 'companies.update');

        $routeCompany = $request->route('company_id');
        $companyId = is_numeric($routeCompany) ? (int) $routeCompany : 0;

        if (! $exempt && $companyId > 0 && auth()->check()) {
            $desiredHost = CompanyTransfer::canonicalHost($companyId);
            if ($desiredHost && $host !== $desiredHost) {
                abort_unless(CompanyTransfer::allowed(auth()->user(), $companyId), 403);
                if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                    abort(409, 'Switch to the company domain before sending changes.');
                }
                // The destination route/path is NOT supplied by the visitor.
                return $this->handoff($request, $companyId, $desiredHost);
            }
        }

        $response = $next($request);

        // The Core login endpoint responds with JSON containing a redirect.
        // If the login was branded for A, but membership only permits B,
        // choose B's canonical domain. If both are accessible, prefer A.
        if ($route === 'login.store' && $response instanceof JsonResponse &&
            auth()->check()) {
            $body = $response->getData(true);
            if (! ($body['success'] ?? false)) {
                return $response;
            }
            $target = $this->companyFromUrl((string) ($body['redirect'] ?? ''));
            if ($domain && CompanyTransfer::allowed(auth()->user(), (int) $domain->company_id)) {
                $target = (int) $domain->company_id;
            }
            if ($target && CompanyTransfer::allowed(auth()->user(), $target)) {
                $targetHost = CompanyTransfer::canonicalHost($target);
                if ($targetHost !== null && $targetHost !== $host) {
                    $body['redirect'] = $this->handoffUrl($request, $target, $targetHost);
                } else {
                    // Preserve the native portal/customer and landing page
                    // suffix rather than incorrectly sending everyone to /ID.
                    $corePath = (string) (parse_url((string) ($body['redirect'] ?? ''), PHP_URL_PATH) ?? '');
                    $coreCompany = $this->companyFromUrl((string) ($body['redirect'] ?? ''));
                    $suffix = '';
                    if ($coreCompany > 0 && str_starts_with($corePath, '/' . $coreCompany)) {
                        $suffix = substr($corePath, strlen('/' . $coreCompany));
                        if (! preg_match('#^/[a-zA-Z0-9_./-]*$#D', $suffix)) {
                            $suffix = '';
                        }
                    }
                    $body['redirect'] = 'https://' . $host . '/' . $target . $suffix;
                    $request->session()->regenerate();
                }
                $response->setData($body);
            }

            return $response;
        }

        // Core's company-switch endpoint returns a redirect on the origin
        // host. Inspect only local, generated redirect URLs and re-home the
        // session when the target company has a different canonical host.
        if (! $exempt && $response instanceof RedirectResponse &&
            auth()->check() && $request->isMethod('GET')) {
            $target = $this->companyFromUrl($response->getTargetUrl());
            if ($target && CompanyTransfer::allowed(auth()->user(), $target)) {
                $targetHost = CompanyTransfer::canonicalHost($target);
                if ($targetHost !== null && $targetHost !== $host) {
                    return redirect()->away($this->handoffUrl($request, $target, $targetHost))
                        ->withHeaders([
                            'Cache-Control' => 'no-store, private',
                            'Referrer-Policy' => 'no-referrer',
                        ]);
                }
            }
        }

        return $response;
    }

    private function handoff(Request $request, int $companyId, string $host): RedirectResponse
    {
        return redirect()->away($this->handoffUrl($request, $companyId, $host))
            ->withHeaders([
                'Cache-Control' => 'no-store, private',
                'Referrer-Policy' => 'no-referrer',
            ]);
    }

    private function handoffUrl(Request $request, int $companyId, string $host): string
    {
        $ticket = CompanyTransfer::issue(auth()->user(), $companyId, $host);

        // Never share authentication cookies with sibling or custom domains.
        // Destroy the origin session only AFTER the audience-bound code exists.
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return CompanyTransfer::redirectUrl($host, $ticket);
    }

    private function companyFromUrl(string $url): int
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('#^/([1-9][0-9]*)(?:/|$)#D', $path, $matches)) {
            return 0;
        }
        return (int) $matches[1];
    }
}
