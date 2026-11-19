<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Support\Facades\Route;
use Modules\Theme\Http\Controllers\ThemeController;

Route::admin('theme', function (): void {
    Route::get('settings', [ThemeController::class, 'index'])->name('settings.index');
    Route::post('settings', [ThemeController::class, 'save'])->name('settings.save');
    Route::patch('preference', [ThemeController::class, 'preference'])->name('preference.save');
});
