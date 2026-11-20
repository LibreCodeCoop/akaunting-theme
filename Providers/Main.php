<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Providers;

use App\Events\Menu\SettingsCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Theme\Console\Commands\PruneExpiredHandoffs;
use Modules\Theme\Domain\Appearance;
use Modules\Theme\Domain\LoginAppearance;
use Modules\Theme\Http\Middleware\HostContext;

final class Main extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/theme.php', 'theme');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/public.php');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'theme');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'theme');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        // Runs after session.start in the native web group, before the
        // company's IdentifyCompany controller middleware. The feature is
        // inert until a global canonical host is explicitly configured.
        app('router')->pushMiddlewareToGroup('web', HostContext::class);
        if ($this->app->runningInConsole()) {
            $this->commands([PruneExpiredHandoffs::class]);
        }

        Event::listen(SettingsCreated::class, static function (SettingsCreated $event): void {
            if (auth()->check() && auth()->user()->can('read-settings-company')) {
                $event->menu->route('theme.companies.index', trans('theme::general.name'), [], 191, [
                    'icon' => 'palette',
                    'search_keywords' => trans('theme::general.description'),
                ]);
            }
            if (auth()->user()->can('read-settings-company')) {
                $event->menu->route('theme.domains.index', trans('theme::general.domains_title'), [], 193, [
                    'icon' => 'domain',
                ]);
            }
            if (LoginAppearance::allowedEditor()) {
                $event->menu->route('theme.login.global.index', trans('theme::general.global_login_title'), [], 192, [
                    'icon' => 'web',
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

        // The public login has no selected company yet. A trusted, active
        // hostname resolves its brand; arbitrary query-string IDs do not.
        View::composer('components.layouts.auth.head', static function ($view): void {
            $options = LoginAppearance::forHost(request()->getHost());
            if ($options === null) {
                return;
            }
            $view->getFactory()->startPush('head_end', view('theme::components.login', [
                'options' => $options,
            ])->render());
        });
    }
}
