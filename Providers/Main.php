<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Providers;

use App\Events\Menu\SettingsCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Theme\Domain\Appearance;

final class Main extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/theme.php', 'theme');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'theme');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'theme');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Event::listen(SettingsCreated::class, static function (SettingsCreated $event): void {
            if (auth()->check() && auth()->user()->can('read-settings-company')) {
                $event->menu->route('theme.settings.index', trans('theme::general.name'), [], 191, [
                    'icon' => 'palette',
                    'search_keywords' => trans('theme::general.description'),
                ]);
            }
        });

        View::composer('components.layouts.admin.head', static function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $options = Appearance::options((int) company_id());
            $view->getFactory()->startPush('head_end', view('theme::components.head', [
                'options' => $options,
                'mode' => Appearance::mode((int) auth()->id()),
            ])->render());
        });

        View::composer([
            'components.layouts.admin.footer',
            'components.layouts.portal.footer',
            'components.layouts.signed.footer',
            'components.layouts.preview.footer',
        ], static function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $view->getFactory()->startPush('footer_end', view('theme::components.links', [
                'options' => Appearance::options((int) company_id()),
            ])->render());
        });

        View::composer('components.layouts.admin.menu', static function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $view->getFactory()->startPush('profile_menu_profile_end', view('theme::components.toggle')->render());
        });

        View::composer('auth.users.edit', static function ($view): void {
            if (! auth()->check() || request()->route()?->getName() !== 'profile.edit') {
                return;
            }

            if ((int) ($view->getData()['user']->id ?? 0) !== (int) auth()->id()) {
                return;
            }

            $view->getFactory()->startPush('body_end', view('theme::components.profile', [
                'mode' => Appearance::mode((int) auth()->id()),
            ])->render());
        });

        // The publicly visible login is instance-scoped, never chosen from user-controlled company IDs.
        View::composer('components.layouts.auth.head', static function ($view): void {
            $loginCompanyId = (int) config('theme.login_company_id', 0);
            if ($loginCompanyId <= 0) {
                return;
            }

            $view->getFactory()->startPush('head_end', view('theme::components.login', [
                'options' => Appearance::options($loginCompanyId),
            ])->render());
        });
    }
}
