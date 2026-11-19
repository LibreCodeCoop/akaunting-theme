<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static bool $exists = true;
        public static function hasTable(string $table): bool
        {
            return self::$exists;
        }
    }

    final class DB
    {
        public static array $companies = [];
        public static array $preferences = [];

        public static function table(string $table): FakeQuery
        {
            return new FakeQuery($table);
        }
    }

    final class FakeQuery
    {
        private string $field = '';
        private int $id = 0;

        public function __construct(private string $table) {}

        public function where(string $field, int $id): self
        {
            $this->field = $field;
            $this->id = $id;

            return $this;
        }

        public function first(): ?object
        {
            return isset(DB::$companies[$this->id])
                ? (object) DB::$companies[$this->id] : null;
        }

        public function value(string $field): ?string
        {
            return DB::$preferences[$this->id][$field] ?? null;
        }

        public function upsert(array $values, array $unique, array $update): void
        {
            foreach ($values as $item) {
                if ($this->table === 'lcc_theme_companies') {
                    $id = $item['company_id'];
                    DB::$companies[$id] = array_replace(DB::$companies[$id] ?? [], $item);
                } else {
                    $id = $item['user_id'];
                    DB::$preferences[$id] = array_replace(DB::$preferences[$id] ?? [], $item);
                }
            }
        }
    }
}

namespace {
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Modules\Theme\Domain\Appearance;

    function now(): string
    {
        return '2026-01-01T00:00:00Z';
    }

    require dirname(__DIR__) . '/Domain/Appearance.php';

    function check(bool $value, string $message): void
    {
        if (! $value) {
            throw new \RuntimeException($message);
        }
    }

    Schema::$exists = false;
    check(Appearance::options(3) === Appearance::DEFAULTS, 'No migration should return defaults.');
    check(Appearance::mode(9) === 'system', 'No migration should fall back to system.');

    Schema::$exists = true;
    Appearance::update(1, ['brand_name' => 'Alpha', 'unknown' => 'ignore']);
    Appearance::update(2, ['brand_name' => 'Beta']);
    check(Appearance::options(1)['brand_name'] === 'Alpha', 'Company A brand missing.');
    check(Appearance::options(2)['brand_name'] === 'Beta', 'Company B brand missing.');
    check(! array_key_exists('unknown', Appearance::options(1)), 'Unknown option leaked.');

    Appearance::setMode(11, 'dark');
    Appearance::setMode(12, 'light');
    check(Appearance::mode(11) === 'dark', 'User preference must be independent.');
    check(Appearance::mode(12) === 'light', 'User preference leaked.');

    try {
        Appearance::setMode(11, 'root');
        throw new \RuntimeException('Invalid preference accepted.');
    } catch (\InvalidArgumentException $exception) {
        // expected
    }

    try {
        Appearance::update(0, ['brand_name' => 'invalid']);
        throw new \RuntimeException('Invalid company accepted.');
    } catch (\InvalidArgumentException $exception) {
        // expected
    }
    echo "PASS: tenant isolation, user isolation, invalid inputs, defaults\n";
}
