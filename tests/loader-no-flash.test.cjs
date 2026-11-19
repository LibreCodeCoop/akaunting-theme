// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');
test('custom loading GIF hides Akaunting image before it can be painted', () => {
    assert.match(view, /\[x-ref="loadingContent"\] img\[src\*="akaunting-loading\.gif"\]/);
    assert.match(view, /\[x-ref="loadingAbsoluteContent"\] img\[src\*="akaunting-loading\.gif"\]/);
    assert.match(view, /visibility:\s*hidden !important/);
    assert.match(view, /\.lcc-theme-loading-image/);
    assert.match(view, /visibility:\s*visible !important/);
});
test('loading swap waits for the custom image to be decoded before showing it', () => {
    assert.match(view, /new Image\(\)/);
    assert.match(view, /preload\.onload/);
    assert.match(view, /preload\.src\s*=\s*source/);
});
