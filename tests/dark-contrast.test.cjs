// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const css = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');

function luminance(hex) {
    const values = hex.match(/[0-9a-f]{2}/gi).map(v => parseInt(v, 16) / 255);
    const linear = values.map(v => v <= 0.04045 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4);
    return linear[0] * 0.2126 + linear[1] * 0.7152 + linear[2] * 0.0722;
}

function ratio(a, b) {
    const [high, low] = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (high + 0.05) / (low + 0.05);
}

test('dark navigation, action rail and form inputs satisfy WCAG AA normal-text contrast', () => {
    for (const [surface, foreground, background] of [
        ['company menu', '#e2e8f0', '#1f2937'],
        ['quick-action rail', '#f1f5f9', '#111827'],
        ['form inputs', '#f1f5f9', '#1f2937'],
    ]) {
        assert.ok(ratio(foreground, background) >= 4.5, surface + ' has insufficient contrast');
        assert.ok(css.includes('color: ' + foreground + ' !important;'), surface + ' text style missing');
        assert.ok(css.includes('background-color: ' + background + ' !important;'), surface + ' surface missing');
    }
});

test('dark palette overrides Akaunting dark purple labels and Element UI select text', () => {
    for (const target of [
        '.text-purple,',
        '.text-purple-500,',
        '.text-gray-500,',
        '.el-form-item__label,',
        '.el-select-dropdown__item,',
        '.el-select .el-input .el-input__inner,',
    ]) {
        assert.ok(css.includes('html.lcc-theme-dark ' + target), 'Missing dark-mode override for ' + target);
    }
});


test('fixed dark not-equal SVG is recolored only within dark-mode search filters', () => {
    const expected = 'html.lcc-theme-dark .js-search img[src*="/public/img/tailwind_icons/not-equal.svg"]';
    assert.ok(css.includes(expected), 'Missing scoped rule for the fixed-color SVG operator');
    assert.ok(css.slice(css.indexOf(expected)).includes('filter: brightness(0) invert(1) !important;'));
});

test('inline search keeps native transparency while ordinary dark inputs remain opaque', () => {
    assert.match(css, /html\.lcc-theme-dark \.js-search input\.bg-transparent \{/);
    const start = css.indexOf('html.lcc-theme-dark .js-search input.bg-transparent {');
    assert.ok(start >= 0);
    assert.ok(css.slice(start, css.indexOf('}', start)).includes('background-color: transparent !important;'));
    assert.ok(css.includes('background-color: var(--lcc-dark-surface) !important;'),
        'Normal inputs must keep their raised dark surface');
});
