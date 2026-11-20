<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

namespace Illuminate\Support\Facades {
    final class Schema
    {
        public static function hasTable(string $name): bool { return true; }
    }
    final class DB
    {
        public static array $rows = [];

        public static function table(string $name): FakeQuery
        {
            return new FakeQuery($name);
        }
    }
    final class FakeQuery
    {
        private array $conditions = [];
        public function __construct(private string $table) {}

        public function where(string $key, mixed $value): self
        {
            $this->conditions[$key] = $value;
            return $this;
        }
        public function whereNull(string $key): self
        {
            $this->conditions[$key] = null;
            return $this;
        }
        public function exists(): bool
        {
            return $this->first() !== null;
        }
        public function first(): ?object
        {
            foreach (DB::$rows[$this->table] ?? [] as $item) {
                foreach ($this->conditions as $key => $value) {
                    if (($item[$key] ?? null) !== $value) continue 2;
                }
                return (object) $item;
            }
            return null;
        }
    }
}

namespace {
    use Illuminate\Support\Facades\DB;
    use Modules\Theme\Domain\Appearance;
    use Modules\Theme\Domain\LoginAppearance;

    function config(string $name, mixed $default = null): mixed
    {
        return $name === 'theme.login_company_id' ? 0 : $default;
    }
    function check(bool $ok, string $msg): void
    {
        if (! $ok) throw new \RuntimeException($msg);
    }

    require dirname(__DIR__) . '/Domain/Appearance.php';
    require dirname(__DIR__) . '/Domain/DomainRegistry.php';
    require dirname(__DIR__) . '/Domain/LoginAppearance.php';

    check(LoginAppearance::forHost('akaunting.example.coop') === null,
        'Without configured canonical host the login must preserve Core.');
    DB::$rows['lcc_theme_login_defaults'] = [[
        'id' => 1,
        'options' => json_encode([
            'base_host' => 'akaunting.example.coop',
            'alias_zone' => 'example.coop',
            'brand_name' => 'Instance brand',
            'footer_text' => 'Hosted by instance',
            'footer_url' => 'https://example.coop',
            'favicon' => 'akaunting-theme/global/fav.png',
            'login_slider' => false,
            'slides' => [['title' => 'Global', 'image' => 'akaunting-theme/global/one.png']],
        ]),
    ]];
    DB::$rows['lcc_theme_companies'] = [[
        'company_id' => 7,
        'options' => json_encode([
            'brand_name' => 'Alpha company',
            'logo' => 'akaunting-theme/7/logo.png',
            'login_background' => 'akaunting-theme/7/login.jpg',
            'login_inherit_slider' => true,
        ]),
    ]];
    DB::$rows['companies'] = [[
        'id' => 7, 'name' => 'Alpha company', 'enabled' => 1, 'deleted_at' => null,
    ]];
    DB::$rows['lcc_theme_domains'] = [[
        'host' => 'alpha.akaunting.example.coop',
        'status' => 'active',
        'company_id' => 7,
    ]];

    $main = LoginAppearance::forHost('akaunting.example.coop');
    check($main['brand_name'] === 'Instance brand', 'Default login must be distinct.');
    check($main['login_slider'] === false, 'Default slider setting lost.');
    check($main['footer_text'] === 'Hosted by instance', 'Instance footer missing.');
    $alpha = LoginAppearance::forHost('alpha.akaunting.example.coop');
    check($alpha['logo'] === 'akaunting-theme/7/logo.png', 'Company logo not loaded.');
    check($alpha['favicon'] === 'akaunting-theme/global/fav.png', 'Global favicon fallback missing.');
    check($alpha['login_background'] === 'akaunting-theme/7/login.jpg', 'Company login background not loaded.');
    check($alpha['slides'][0]['title'] === 'Global', 'Company should inherit global slides.');
    check($alpha['login_slider'] === false, 'Company should inherit slider visibility.');
    check(LoginAppearance::forHost('alpha.akaunting.example.coop.attacker.test') === null,
        'Suffix spoofing accepted.');
    check(LoginAppearance::forHost('unregistered.akaunting.example.coop') === null,
        'Unverified hostname accepted.');
    DB::$rows['lcc_theme_domains'][] = [
        'host' => 'accounting.example.coop', 'status' => 'active', 'company_id' => 7,
    ];
    check(LoginAppearance::forHost('accounting.example.coop')['brand_name'] === 'Alpha company',
        'Configured sibling alias did not inherit company branding.');
    check(LoginAppearance::forHost('unknown.example.coop') === null,
        'Unregistered corporate hostname was accepted.');

    DB::$rows['companies'][0]['enabled'] = 0;
    check(LoginAppearance::forHost('alpha.akaunting.example.coop') === null,
        "Disabled company branded login remained reachable.");
    DB::$rows['companies'][0]['enabled'] = 1;

    DB::$rows['lcc_theme_companies'][0]['options'] = json_encode([
        'brand_name' => 'Alpha company',
        'footer_credit' => 'Maintained by Alpha',
        'footer_url' => 'https://alpha.example.coop',
        'login_inherit_slider' => false,
        'login_slider' => true,
        'login_slides' => [['title' => 'Company', 'image' => 'akaunting-theme/7/company.png']],
    ]);
    $overridden = LoginAppearance::forHost('alpha.akaunting.example.coop');
    check($overridden['login_slider'] === true, 'Company slider override ignored.');
    check($overridden['slides'][0]['title'] === 'Company', 'Company slide override ignored.');
    check($overridden['footer_text'] === 'Maintained by Alpha', 'Company footer was not selected.');
    check($overridden['footer_url'] === 'https://alpha.example.coop', 'Company footer link was not selected.');


    echo "PASS: public login host isolation, global fallback and company overrides\n";
}
