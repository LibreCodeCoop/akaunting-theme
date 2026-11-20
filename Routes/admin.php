<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Support\Facades\Route;
use Modules\Theme\Http\Controllers\ThemeController;
use Modules\Theme\Http\Controllers\LoginSettingsController;
use Modules\Theme\Http\Controllers\DomainController;

Route::admin('theme', function (): void {
    Route::get('companies', [ThemeController::class, 'companies'])->name('companies.index');
    Route::get('login/global', [LoginSettingsController::class, 'index'])->name('login.global.index');
    Route::post('login/global', [LoginSettingsController::class, 'save'])->name('login.global.save');
    Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
    Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
    Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])->name('domains.verify');
    Route::post('domains/{domain}/primary', [DomainController::class, 'primary'])->name('domains.primary');
    Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
    Route::get('settings', [ThemeController::class, 'index'])->name('settings.index');
    Route::post('settings', [ThemeController::class, 'save'])->name('settings.save');
    Route::patch('preference', [ThemeController::class, 'preference'])->name('preference.save');
});
