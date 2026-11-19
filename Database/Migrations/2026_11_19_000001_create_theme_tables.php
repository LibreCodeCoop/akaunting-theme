<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lcc_theme_companies', function (Blueprint $table): void {
            $table->unsignedInteger('company_id')->primary();
            $table->json('options');
            $table->timestamps();
        });
        Schema::create('lcc_theme_preferences', function (Blueprint $table): void {
            $table->unsignedInteger('user_id')->primary();
            $table->string('mode', 6)->default('system');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lcc_theme_preferences');
        Schema::dropIfExists('lcc_theme_companies');
    }
};
