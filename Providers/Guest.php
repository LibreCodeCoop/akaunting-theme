<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Theme\Domain\LoginAppearance;
use Modules\Theme\Http\Middleware\HostContext;

/**
 * Early, standalone Laravel provider for public login and session routing.
 *
 * Akaunting loads normal company modules on demand after IdentifyCompany.
 * Anonymous login requests never reach that middleware, so the normal module
 * provider cannot customize public login. This provider is intentionally
 * separate from Main: it must not register Akaunting's company route macros.
 *
 * Install through Laravel package discovery at deployment time, without
 * modifying Akaunting's PHP source code.
 */
final class Guest extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/theme.php', 'theme');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/public.php');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'theme');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'theme');

        app('router')->pushMiddlewareToGroup('web', HostContext::class);

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
