// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const template = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');

test('company icon replaces the company-selector image, without adding a second logo', () => {
    assert.match(template, /#sidenav-main \[data-dropdown-toggle="dropdown-menu-company"\] img/);
    assert.match(template, /companyIcon\.src = @json\(\$logo\)/);
    assert.doesNotMatch(template, /insertAdjacentElement\('afterend', brand\)/);
    assert.match(template, /companyIcon\.alt = @json\(\$options\['brand_name'\]/);
});

test('light mode styles the narrow quick-action rail rather than mistaking main menu for sidebar', () => {
    assert.match(template, /html:not\(\.lcc-theme-dark\) body \.js-menu > \.menu-scroll\.bg-lilac-900/);
    assert.match(template, /--lcc-theme-rail-foreground/);
    assert.match(template, /#sidenav-main/);
});

test('dark mode gives the narrow rail an explicit dark background and readable icon text', () => {
    assert.match(template, /html\.lcc-theme-dark body \.js-menu > \.menu-scroll\.bg-lilac-900/);
    assert.match(template, /html\.lcc-theme-dark[^\n]*\.menu-scroll[^\n]*\.text-purple/);
});
