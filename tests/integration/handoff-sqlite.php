<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

// This script is explicitly opt-in. It must run in a local, test-owned SQLite
// database; even if a production database URL is available it refuses to run.
if (getenv('DB_CONNECTION') !== 'sqlite' ||
    ! str_contains((string) getenv('DB_DATABASE'), 'qa-theme-isolated-')) {
    fwrite(STDERR, "Refusing to modify a non-test database.\n");
    exit(2);
}

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Theme\Domain\LoginAppearance;
use Modules\Theme\Domain\CompanyTransfer;

if (config('database.default') !== 'sqlite' ||
    ! str_contains((string) config('database.connections.sqlite.database'), 'qa-theme-isolated-')) {
    throw new RuntimeException('Test database isolation check failed.');
}

function requireTrue(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}
foreach (['users', 'companies', 'user_companies'] as $name) {
    requireTrue(! Schema::hasTable($name), $name . ' already exists; fixture is not disposable.');
}

Schema::create('users', static function (Blueprint $table): void {
    $table->increments('id');
    $table->string('email')->nullable();
    $table->boolean('enabled')->default(true);
    $table->string('landing_page')->nullable();
    $table->timestamp('deleted_at')->nullable();
    $table->timestamps();
});
Schema::create('companies', static function (Blueprint $table): void {
    $table->increments('id');
    $table->string('name');
    $table->boolean('enabled')->default(true);
    $table->timestamp('deleted_at')->nullable();
    $table->timestamps();
});
Schema::create('user_companies', static function (Blueprint $table): void {
    $table->increments('id');
    $table->unsignedInteger('user_id');
    $table->unsignedInteger('company_id');
    $table->timestamps();
});
DB::table('users')->insert([
    'id' => 1, 'email' => 'synthetic@example.invalid',
    'enabled' => true, 'landing_page' => 'dashboard',
]);
DB::table('companies')->insert([
    ['id' => 1, 'name' => 'Alpha', 'enabled' => true],
    ['id' => 2, 'name' => 'Beta', 'enabled' => true],
]);
DB::table('user_companies')->insert(['user_id' => 1, 'company_id' => 1]);
LoginAppearance::update(['base_host' => 'akaunting.example.coop']);
DB::table('lcc_theme_domains')->insert([
    'company_id' => 1, 'host' => 'alpha.akaunting.example.coop',
    'status' => 'active', 'is_primary' => true,
    'verification_token' => 'synthetic-token',
    'verified_at' => now(), 'created_at' => now(), 'updated_at' => now(),
]);
config(['theme.domain_routing_enabled' => true]);

$user = App\Models\Auth\User::findOrFail(1);
requireTrue(CompanyTransfer::allowed($user, 1), 'Authorized company not allowed.');
requireTrue(! CompanyTransfer::allowed($user, 2), 'Cross-company access was permitted.');
$ticket = CompanyTransfer::issue($user, 1, 'alpha.akaunting.example.coop');
requireTrue(strlen($ticket) === 64, 'Ticket entropy wrong.');
requireTrue(DB::table('lcc_theme_handoffs')->where('token_hash', hash('sha256', $ticket))->exists(),
    'Only the ticket hash may be persisted.');
requireTrue(CompanyTransfer::consume($ticket, 'akaunting.example.coop') === null,
    'Ticket redeemed on wrong hostname.');
$result = CompanyTransfer::consume($ticket, 'alpha.akaunting.example.coop');
requireTrue($result && $result['company_id'] === 1, 'Ticket not redeemed.');
requireTrue(CompanyTransfer::consume($ticket, 'alpha.akaunting.example.coop') === null,
    'Ticket replay accepted.');
$expired = CompanyTransfer::issue($user, 1, 'alpha.akaunting.example.coop');
DB::table('lcc_theme_handoffs')->where('token_hash', hash('sha256', $expired))
    ->update(['expires_at' => now()->subMinute()]);
requireTrue(CompanyTransfer::consume($expired, 'alpha.akaunting.example.coop') === null,
    'Expired ticket accepted.');

echo "PASS: SQLite migration, tenant ACL, single-use, wrong-host and expired handoff\n";
