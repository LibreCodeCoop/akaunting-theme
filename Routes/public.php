<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Support\Facades\Route;
use Modules\Theme\Http\Controllers\TransferController;
use Modules\Theme\Http\Controllers\PublicDomainProofController;

Route::middleware(['web', 'throttle:12,1'])->get('theme/transfer/redeem',
    [TransferController::class, 'redeem'])->name('theme.public.transfer.redeem');

Route::middleware(['web', 'throttle:12,1'])->get('theme/domain-proof/{token}',
    [PublicDomainProofController::class, 'show'])->name('theme.public.domain-proof');
