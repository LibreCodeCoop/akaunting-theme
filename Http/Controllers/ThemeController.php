<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Theme\Domain\Appearance;

final class ThemeController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->can('read-settings-company'), 403);

        return view('theme::settings', ['options' => Appearance::options((int) company_id())]);
    }

    public function save(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('update-settings-company'), 403);

        $values = $request->validate([
            'brand_name' => 'nullable|string|max:80',
            'sidebar_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/D'],
            'menu_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/D'],
            'content_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/D'],
            'support_url' => 'nullable|url|starts_with:https://|max:400',
            'documentation_url' => 'nullable|url|starts_with:https://|max:400',
            'footer_credit' => 'nullable|string|max:160',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:512',
            'login_background' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
        ]);

        $companyId = (int) company_id();
        $options = array_intersect_key($values, array_flip([
            'brand_name', 'sidebar_color', 'menu_color', 'content_color',
            'support_url', 'documentation_url', 'footer_credit',
        ]));
        $options['login_slider'] = $request->boolean('login_slider');

        foreach (['logo', 'favicon', 'login_background'] as $key) {
            if ($request->hasFile($key)) {
                $options[$key] = $request->file($key)->store('akaunting-theme/' . $companyId, 'public');
            }
        }
        Appearance::update($companyId, $options);

        return redirect()->route('theme.settings.index')->with('success', trans('theme::general.saved'));
    }

    public function preference(Request $request): JsonResponse
    {
        abort_unless(auth()->check() && auth()->user()->can('update-auth-profile'), 403);

        $data = $request->validate(['mode' => 'required|in:system,light,dark']);
        Appearance::setMode((int) auth()->id(), $data['mode']);

        return response()->json(['mode' => $data['mode']]);
    }
}
