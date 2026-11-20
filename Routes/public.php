<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Support\Facades\Route;
use Modules\Theme\Http\Controllers\TransferController;

Route::middleware(['web', 'throttle:12,1'])->get('theme/transfer/redeem',
    [TransferController::class, 'redeem'])->name('theme.public.transfer.redeem');
