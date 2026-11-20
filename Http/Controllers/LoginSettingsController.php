<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Theme\Domain\DomainRegistry;
use Modules\Theme\Domain\LoginAppearance;

final class LoginSettingsController extends Controller
{
    public function index(): View
    {
        abort_unless(LoginAppearance::allowedEditor(), 403);

        return view('theme::global-login', ['options' => LoginAppearance::options()]);
    }

    public function save(Request $request): RedirectResponse
    {
        abort_unless(LoginAppearance::allowedEditor(), 403);

        $data = $request->validate([
            'base_host' => 'required|string|max:253|regex:/^(?:[a-z0-9-]+\\.)+[a-z]{2,}$/D',
            'brand_name' => 'nullable|string|max:80',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:512',
            'login_background' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
            'login_slider' => 'sometimes|boolean',
            'remove_logo' => 'sometimes|boolean',
            'remove_favicon' => 'sometimes|boolean',
            'remove_login_background' => 'sometimes|boolean',
            'slide_title' => 'nullable|array|max:6',
            'slide_title.*' => 'nullable|string|max:120',
            'slide_caption' => 'nullable|array|max:6',
            'slide_caption.*' => 'nullable|string|max:280',
        ]);
        $baseHost = DomainRegistry::normalize($data['base_host']);
        $current = LoginAppearance::options();
        $requestHost = DomainRegistry::normalize($request->getHost());

        // Changing the instance's trust anchor is a privileged, sensitive
        // transition. It must be made from its existing main origin and not
        // while company-host mappings are active.
        if ($current['base_host'] !== '' && $current['base_host'] !== $baseHost) {
            abort_unless($requestHost === $current['base_host'], 403);
            abort_unless(! \Illuminate\Support\Facades\DB::table('lcc_theme_domains')
                ->where('status', 'active')->exists(), 422, 'Deactivate company domains first.');
        } elseif ($current['base_host'] === '' && $requestHost !== $baseHost) {
            abort(422, 'Use the main instance domain as the canonical login host.');
        }

        $changes = [
            'base_host' => $baseHost,
            'brand_name' => $data['brand_name'] ?? '',
            'login_slider' => $request->boolean('login_slider'),
        ];
        foreach (['logo', 'favicon', 'login_background'] as $name) {
            if ($request->hasFile($name)) {
                $changes[$name] = $request->file($name)->store('akaunting-theme/global', 'public');
            } elseif ($request->boolean('remove_' . $name)) {
                $changes[$name] = '';
            }
        }

        $previous = $current['slides'];
        $slides = [];
        for ($i = 0; $i < 6; $i++) {
            $title = trim((string) ($data['slide_title'][$i] ?? ''));
            $caption = trim((string) ($data['slide_caption'][$i] ?? ''));
            $image = $previous[$i]['image'] ?? '';
            $file = 'slide_image_' . $i;

            $request->validate([
                $file => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
                'remove_slide_' . $i => 'sometimes|boolean',
            ]);
            if ($request->hasFile($file)) {
                $image = $request->file($file)->store('akaunting-theme/global/slides', 'public');
            } elseif ($request->boolean('remove_slide_' . $i)) {
                $image = '';
            }
            if ($title !== '' && $image !== '') {
                $slides[$i] = ['title' => $title, 'caption' => $caption, 'image' => $image];
            }
        }
        $changes['slides'] = $slides;
        LoginAppearance::update($changes);

        return redirect()->route('theme.login.global.index')
            ->with('success', trans('theme::general.saved'));
    }
}
