<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Theme\Domain\DomainRegistry;
use Modules\Theme\Domain\LoginAppearance;

final class DomainController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->can('read-settings-company'), 403);

        $companyId = (int) company_id();
        $domains = Schema::hasTable('lcc_theme_domains')
            ? DB::table('lcc_theme_domains')->where('company_id', $companyId)
                ->orderByDesc('is_primary')->orderBy('id')->get()
            : collect([]);

        return view('theme::domains', [
            'companyName' => company($companyId)->name,
            'baseHost' => LoginAppearance::options()['base_host'],
            'domains' => $domains,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('update-settings-company'), 403);
        $data = $request->validate(['host' => 'required|string|max:253']);
        $host = DomainRegistry::normalize($data['host']);
        $base = LoginAppearance::options()['base_host'];

        if (! DomainRegistry::validSubdomain($host, $base, LoginAppearance::options()['alias_zone'])) {
            throw ValidationException::withMessages(['host' =>
                trans('theme::general.invalid_company_domain')]);
        }
        if (DB::table('lcc_theme_domains')->where('host', $host)->exists()) {
            throw ValidationException::withMessages(['host' =>
                trans('theme::general.duplicate_company_domain')]);
        }

        DB::table('lcc_theme_domains')->insert([
            'company_id' => (int) company_id(),
            'host' => $host,
            'verification_token' => bin2hex(random_bytes(24)),
            'status' => 'pending',
            'is_primary' => false,
            'verified_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('theme.domains.index')
            ->with('success', trans('theme::general.domain_pending'));
    }

    public function verify(int $domain): RedirectResponse
    {
        abort_unless(auth()->user()?->can('update-settings-company'), 403);
        $record = $this->ownedDomain($domain);
        $base = LoginAppearance::options()['base_host'];

        if (! DomainRegistry::validSubdomain($record->host, $base, LoginAppearance::options()['alias_zone']) ||
            ! DomainRegistry::verifyDns($record->host, $record->verification_token)) {
            throw ValidationException::withMessages(['host' =>
                trans('theme::general.domain_dns_unverified')]);
        }
        if (! DomainRegistry::verifyTls($record->host)) {
            throw ValidationException::withMessages(['host' =>
                trans('theme::general.domain_https_unverified')]);
        }
        if (! DomainRegistry::verifyHostRoute($record->host, $record->verification_token)) {
            throw ValidationException::withMessages(['host' =>
                trans('theme::general.domain_origin_unverified')]);
        }

        DB::transaction(static function () use ($record): void {
            // Serialize primary selection for parallel verification requests.
            DB::table('lcc_theme_domains')->where('company_id', $record->company_id)
                ->lockForUpdate()->get();
            $alreadyPrimary = DB::table('lcc_theme_domains')
                ->where('company_id', $record->company_id)
                ->where('is_primary', true)->exists();
            DB::table('lcc_theme_domains')->where('id', $record->id)->update([
                'status' => 'active',
                'verified_at' => now(),
                'is_primary' => ! $alreadyPrimary,
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('theme.domains.index')
            ->with('success', trans('theme::general.domain_activated'));
    }

    public function primary(int $domain): RedirectResponse
    {
        abort_unless(auth()->user()?->can('update-settings-company'), 403);
        $record = $this->ownedDomain($domain);
        abort_unless($record->status === 'active', 422);

        DB::transaction(static function () use ($record): void {
            DB::table('lcc_theme_domains')->where('company_id', $record->company_id)
                ->lockForUpdate()->get();
            DB::table('lcc_theme_domains')
                ->where('company_id', $record->company_id)->update(['is_primary' => false]);
            DB::table('lcc_theme_domains')->where('id', $record->id)
                ->update(['is_primary' => true, 'updated_at' => now()]);
        });
        return redirect()->route('theme.domains.index')
            ->with('success', trans('theme::general.saved'));
    }

    public function destroy(int $domain): RedirectResponse
    {
        abort_unless(auth()->user()?->can('update-settings-company'), 403);
        $record = $this->ownedDomain($domain);
        DB::transaction(static function () use ($record): void {
            DB::table('lcc_theme_domains')->where('company_id', $record->company_id)
                ->lockForUpdate()->get();
            DB::table('lcc_theme_domains')->where('id', $record->id)
                ->where('company_id', $record->company_id)->delete();
            if ($record->is_primary) {
                $next = DB::table('lcc_theme_domains')
                    ->where('company_id', $record->company_id)
                    ->where('status', 'active')->orderBy('id')->first();
                if ($next) {
                    DB::table('lcc_theme_domains')->where('id', $next->id)
                        ->update(['is_primary' => true, 'updated_at' => now()]);
                }
            }
        });

        return redirect()->route('theme.domains.index')
            ->with('success', trans('theme::general.saved'));
    }

    private function ownedDomain(int $domain): object
    {
        return DB::table('lcc_theme_domains')
            ->where('company_id', (int) company_id())
            ->where('id', $domain)->firstOrFail();
    }
}
