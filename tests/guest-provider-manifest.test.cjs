// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.join(__dirname, '..');
const read = file => fs.readFileSync(path.join(root,file),'utf8');

test('public guest provider is discoverable through the module own Composer package', () => {
  const moduleComposer = JSON.parse(read('composer.json'));
  assert.equal(moduleComposer.name, 'librecodecoop/akaunting-theme');
  assert.equal(moduleComposer.type, 'akaunting-module');
  assert.deepEqual(moduleComposer.extra.laravel.providers, ['Modules\\Theme\\Providers\\Guest']);
  assert.equal(moduleComposer.autoload['psr-4']['Modules\\Theme\\'], './');
  assert.equal(fs.existsSync(path.join(root,'scripts/register_guest_provider.php')), false,
      'Do not edit Laravel generated package manifests directly');
});

test('the guest provider loads only public routes and not company admin macros', () => {
  const guest = read('Providers/Guest.php');
  const main = read('Providers/Main.php');
  assert.match(guest, /Routes\/public.php/);
  assert.doesNotMatch(guest, /Routes\/admin.php/);
  assert.match(guest, /HostContext::class/);
  assert.match(main, /Routes\/admin.php/);
  assert.doesNotMatch(main, /Routes\/public.php/);
});

test('operator instructions install and remove through Composer, not via Docker entrypoint', () => {
  const docs = read('docs/company-domains.md');
  assert.match(docs,/composer require 'librecodecoop\/akaunting-theme:\*@dev'/);
  assert.match(docs,/composer remove librecodecoop\/akaunting-theme/);
  assert.doesNotMatch(docs, /scripts\/register_guest_provider\.php/);
});
