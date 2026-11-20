<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PruneExpiredHandoffs extends Command
{
    protected $signature = 'theme:prune-handoffs';
    protected $description = 'Remove expired, already-useless company-domain session tickets';

    public function handle(): int
    {
        if (! Schema::hasTable('lcc_theme_handoffs')) {
            return self::SUCCESS;
        }

        // A short-lived ticket is never necessary after expiration. Remove
        // in bounded batches to avoid long-running production locks.
        $total = 0;
        do {
            $ids = DB::table('lcc_theme_handoffs')
                ->where('expires_at', '<', now()->subMinute())
                ->orderBy('id')->limit(250)->pluck('id')->all();
            $deleted = $ids ? DB::table('lcc_theme_handoffs')->whereIn('id', $ids)->delete() : 0;
            $total += $deleted;
        } while ($deleted > 0 && $total < 5000);

        $this->line('Expired handoff records pruned: ' . $total);
        return self::SUCCESS;
    }
}
