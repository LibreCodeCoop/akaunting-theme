// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
test('light-only company brand surface colors cannot override per-user dark mode', () => {
  const source = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');
  assert.ok(source.includes('html:not(.lcc-theme-dark) body #sidenav-main'), 'company menu must be light-only');
  assert.ok(source.includes('html:not(.lcc-theme-dark) body #sidenav-main .main-menu'), 'menu must be light-only');
  assert.ok(source.includes('html:not(.lcc-theme-dark) body #main-body'), 'content must be light-only');
  assert.ok(!source.includes('body:not(.lcc-theme-dark)'), 'dark mode is toggled on html, not body');
});
