// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
test('company list sorts the hydrated Akaunting company name, not a nonexistent SQL column', () => {
 const controller = fs.readFileSync(path.join(__dirname,'../Http/Controllers/ThemeController.php'),'utf8');
 assert.doesNotMatch(controller,/orderBy\(['"]companies\.name['"]\)/);
 assert.match(controller, /companies\(\)->enabled\(\)->get\(\)/);
 assert.match(controller, /sortBy\(static fn \(Company \$item\)/);
});
