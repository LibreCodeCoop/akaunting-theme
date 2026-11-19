// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');

test('global dark palette is scoped to html.lcc-theme-dark and uses semantic tokens', () => {
    for (const token of [
        '--lcc-dark-page', '--lcc-dark-surface', '--lcc-dark-elevated',
        '--lcc-dark-hover', '--lcc-dark-text', '--lcc-dark-link',
    ]) {
        assert.ok(view.includes(token + ':'), token + ' missing');
    }
    assert.ok(view.includes('html.lcc-theme-dark :is('));
    assert.ok(!view.includes('html:not(.lcc-theme-dark) :is('), 'global palette must not override light mode');
});

test('core-native table flex rows and semantic status badges are handled without Core edits', () => {
    assert.ok(view.includes('html.lcc-theme-dark table.flex.flex-col tbody tr.group:hover'));
    for (const cls of ['.bg-lilac-900', '.bg-purple-lighter', '.bg-green-100', '.bg-red-100', '.bg-blue-100']) {
        assert.ok(view.includes(cls), 'Missing ' + cls);
    }
    assert.match(view, /html\.lcc-theme-dark :is\(a\.bg-green,button\.bg-green/);
});
