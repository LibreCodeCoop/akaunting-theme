// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const file = path.join(__dirname, '../Resources/views/components/head.blade.php');
const view = fs.readFileSync(file, 'utf8');

test('CSS/JS comments must not contain Blade component-looking tags', () => {
    // The real Laravel/Livewire Blade compiler treats <x-tooltip> inside a CSS
    // comment as a component and produces invalid PHP; this caused HTTP 500.
    const comments = [...view.matchAll(/\/\*[\s\S]*?\*\//g)];
    assert.ok(comments.length > 0, 'Test must cover real comments');
    for (const comment of comments) {
        assert.doesNotMatch(comment[0], /<\s*x-[a-z][a-z0-9-]*/i);
    }
});

test('head view includes balanced conditional directives', () => {
    const found = [...view.matchAll(/@(if|elseif|else|endif)\b/g)].map(m => m[1]);
    let depth = 0;
    for (const directive of found) {
        if (directive === 'if') depth++;
        if (directive === 'endif') depth--;
        assert.ok(depth >= 0, 'Stray @endif directive');
    }
    assert.equal(depth, 0, 'Unclosed Blade conditional');
});
