// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = x => fs.readFileSync(path.join(__dirname, '..', x), 'utf8');
const defaults = read('Resources/views/global-login.blade.php');
const company = read('Resources/views/settings.blade.php');
const login = read('Resources/views/components/login.blade.php');
const controller = read('Http/Controllers/LoginSettingsController.php');

test('instance login branding is distinct and guarded by instance company-management permissions', () => {
    assert.match(defaults, /theme\.login\.global\.save/);
    assert.match(defaults, /name="base_host"/);
    for (const image of ['logo', 'favicon', 'login_background']) {
        assert.ok(defaults.includes("['logo' =>") || defaults.includes('name="' + image + '"'));
        assert.ok(defaults.includes("remove_{{ $key }}"), 'Missing preview reset');
    }
    assert.match(read('Domain/LoginAppearance.php'), /create-common-companies/);
    assert.match(read('Domain/LoginAppearance.php'), /delete-common-companies/);
    assert.match(controller, /LoginAppearance::allowedEditor\(\)/);
});
test('company-only login customization is behind an active verified domain', () => {
    assert.match(company, /@if \(\$hasActiveDomain\)/);
    assert.match(read('Http/Controllers/ThemeController.php'), /DomainRegistry::primaryForCompany/);
    assert.match(read('Http/Controllers/ThemeController.php'), /abort\(422, trans\('theme::general.login_locked_until_verified'\)\)/);
});
test('carousel uses safe DOM primitives and preserves native Akaunting slides as fallback', () => {
    assert.match(defaults, /@for \(\$i = 0; \$i < 6; \$i\+\+\)/);
    assert.match(login, /if \(!slides.length\) return;/);
    assert.match(login, /document\.createElement\('img'\)/);
    assert.match(login, /title\.textContent = slide\.title/);
    assert.match(login, /caption\.textContent = slide\.caption/);
    assert.doesNotMatch(login, /innerHTML\s*=/);
    assert.match(login, /#app \{/);
    assert.match(login, /background-image: url/);
});
test('login slides use only installed Swiper APIs, preserving looping and autoplay', () => {
    assert.doesNotMatch(login, /swiper\.removeAllSlides\(/);
    assert.doesNotMatch(login, /swiper\.appendSlide\(/);
    assert.match(login, /swiper\.loopDestroy\(\)/);
    assert.match(login, /wrapper\.replaceChildren\(\.\.\.nodes\)/);
    assert.match(login, /swiper\.loopCreate\(\)/);
    assert.match(login, /swiper\.update\(\)/);
    assert.match(login, /swiper\.autoplay\?\.start\?\.\(\)/);
});
test('domain registry verifies DNS and TLS, status is pending until checks pass', () => {
    const registry = read('Domain/DomainRegistry.php');
    const domains = read('Http/Controllers/DomainController.php');
    assert.match(domains, /'status' => 'pending'/);
    assert.match(domains, /DomainRegistry::verifyDns/);
    assert.match(domains, /DomainRegistry::verifyTls/);
    assert.match(registry, /DNS_TXT/);
    assert.match(registry, /FILTER_FLAG_NO_PRIV_RANGE/);
    assert.match(registry, /FILTER_FLAG_NO_RES_RANGE/);
    assert.match(registry, /verify_peer_name/);
});

test('public login background never paints oversized artwork over the credential form', () => {
    assert.match(login, /#app::before \{/);
    assert.match(login, /background-size: cover;/);
    assert.match(login, /background_opacity'\] \?\? 10/);
    assert.match(login, /pointer-events: none;/);
    assert.match(login, /#app > \.relative > \.w-full/);
    assert.match(login, /background-color: rgba\(255, 255, 255, 0\.9\)/);
});

test('login footer is additive, configurable and leaves upstream Akaunting attribution intact', () => {
    const guest = read('Providers/Guest.php');
    const footer = read('Resources/views/components/public-footer.blade.php');
    const global = read('Resources/views/global-login.blade.php');
    assert.match(guest, /components\.layouts\.auth\.footer/);
    assert.match(guest, /startPush\('footer_end'/);
    assert.match(footer, /\$options\['footer_text'\]/);
    assert.match(footer, /rel="noopener noreferrer"/);
    assert.doesNotMatch(footer, /powered by Akaunting/i);
    assert.match(global, /name="footer_text"/);
    assert.match(global, /name="footer_url"/);
    assert.match(global, /name="background_opacity"/);
    assert.match(global, /name="alias_zone"/);
    assert.match(read('Http/Controllers/LoginSettingsController.php'), /starts_with:https:\/\//);
});
