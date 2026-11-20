<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lcc_theme_handoffs', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('token_hash', 64)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id');
            $table->string('destination_host', 253);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lcc_theme_handoffs');
    }
};
