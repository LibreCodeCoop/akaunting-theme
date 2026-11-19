// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function runTheme(mode, prefersDark = false, serverStatus = 200) {
    const blade = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');
    const source = blade.slice(blade.indexOf('<script>') + 8, blade.indexOf('</script>'))
        .replace('@json($mode)', JSON.stringify(mode))
        .replace('@json(csrf_token())', JSON.stringify('synthetic-csrf'))
        .replace('@json(route(\'theme.preference.save\'))', JSON.stringify('/1/theme/preference'))
        .replace(/@if \([^\n]*\)[\s\S]*?@endif/g, '');
    const classes = new Set();
    const calls = [];
    let changeListener = null;
    const root = {
        classList: {toggle: (name, yes) => yes ? classes.add(name) : classes.delete(name)},
        dataset: {}, style: {}
    };
    const media = {
        matches: prefersDark,
        addEventListener: (_name, listener) => {changeListener = listener;}
    };
    const window = {
        matchMedia: () => media,
        fetch: async (_url, opts) => {
            calls.push(opts);
            return {ok: serverStatus === 200, status: serverStatus};
        }
    };
    const document = {
        documentElement: root,
        querySelector: () => null,
        dispatchEvent: () => {},
    };
    vm.runInNewContext(source, {
        window, document, fetch: window.fetch, CustomEvent: function () {}
    });
    return {window, classes, calls, media, notify: () => changeListener?.()};
}

test('system mode follows OS changes', () => {
    const app = runTheme('system', true);
    assert.equal(app.classes.has('lcc-theme-dark'), true);
    app.media.matches = false;
    app.notify();
    assert.equal(app.classes.has('lcc-theme-dark'), false);
});

test('explicit light ignores a dark system', () => {
    const app = runTheme('light', true);
    assert.equal(app.classes.has('lcc-theme-dark'), false);
    app.notify();
    assert.equal(app.classes.has('lcc-theme-dark'), false);
});

test('explicit dark persists with CSRF and same-origin credentials', async () => {
    const app = runTheme('system', false);
    await app.window.LccTheme.save('dark');
    assert.equal(app.classes.has('lcc-theme-dark'), true);
    assert.equal(app.calls[0].credentials, 'same-origin');
    assert.equal(app.calls[0].headers['X-CSRF-TOKEN'], 'synthetic-csrf');
    assert.equal(JSON.parse(app.calls[0].body).mode, 'dark');
});

test('failed save does not alter effective appearance', async () => {
    const app = runTheme('light', false, 403);
    await assert.rejects(() => app.window.LccTheme.save('dark'));
    assert.equal(app.window.LccTheme.mode, 'light');
    assert.equal(app.classes.has('lcc-theme-dark'), false);
});

test('invalid modes never reach the server', async () => {
    const app = runTheme('system');
    await assert.rejects(() => app.window.LccTheme.save('invalid'));
    assert.equal(app.calls.length, 0);
});
