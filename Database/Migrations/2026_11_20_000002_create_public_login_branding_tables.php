<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lcc_theme_login_defaults', static function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->json('options');
            $table->timestamps();
        });
        Schema::create('lcc_theme_domains', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->index();
            $table->string('host', 253)->unique();
            $table->string('verification_token', 64);
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'is_primary'], 'lcc_theme_domains_company_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lcc_theme_domains');
        Schema::dropIfExists('lcc_theme_login_defaults');
    }
};
