// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('company artwork URLs use Akaunting public asset prefix and never protected storage', () => {
    for (const filename of ['head.blade.php', 'login.blade.php']) {
        const view = fs.readFileSync(path.join(__dirname, '../Resources/views/components', filename), 'utf8');
        assert.ok(view.includes("asset('public/storage/'"), filename + ' is not public');
        assert.ok(!view.includes("Storage::disk('public')->url"), filename + ' uses protected storage URL');
    }
});
