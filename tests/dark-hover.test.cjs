// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const css = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');

test('dark-mode utility hover surfaces cover Akaunting dropdown and card highlight classes', () => {
    for (const className of [
        'hover\\:bg-lilac-100',
        'hover\\:bg-lilac-900',
        'hover\\:bg-purple',
        'hover\\:bg-purple-100',
        'hover\\:bg-gray-100',
        'hover\\:bg-gray-200',
    ]) {
        assert.ok(css.includes(className + ':hover'), 'No dark override for ' + className);
    }
    assert.match(css, /html\.lcc-theme-dark\s+\.dropdown-menu[^\n]*:hover/);
});

test('Element UI table hovered rows override fixed and normal columns', () => {
    assert.match(css, /\.el-table--enable-row-hover \.el-table__body tr:hover > td/);
    assert.match(css, /\.el-table__body tr\.hover-row > td/);
    assert.match(css, /\.el-table__body tr\.current-row > td/);
    assert.match(css, /\.el-table__fixed-body-wrapper/);
});

test('native table and selected list items cannot turn white in dark mode', () => {
    assert.match(css, /html\.lcc-theme-dark table tbody tr:hover > td/);
    assert.match(css, /html\.lcc-theme-dark \.el-select-dropdown__item\.hover/);
    assert.match(css, /background-color:\s*#334155 !important/);
});

test('light mode purple hover renders white foreground over dark purple rather than purple on purple', () => {
    assert.match(css, /html:not\(\.lcc-theme-dark\) body \.hover\\:bg-purple:hover/);
    assert.match(css, /background-color: #55588b !important;/);
    assert.match(css, /html:not\(\.lcc-theme-dark\) body \.hover\\:bg-purple:hover span/);
});
