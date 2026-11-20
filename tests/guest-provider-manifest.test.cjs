// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const {spawnSync} = require('node:child_process');
const script = path.join(__dirname, '../scripts/register_guest_provider.php');

test('module-owned provider discovery installer is idempotent and reversible', () => {
    const base = fs.mkdtempSync(path.join(os.tmpdir(), 'lcc-theme-pkg-'));
    try {
        for (const name of ['bootstrap/cache', 'modules/Theme/Providers']) {
            fs.mkdirSync(path.join(base, name), {recursive: true});
        }
        fs.writeFileSync(path.join(base, 'bootstrap/app.php'), '<?php return null;');
        fs.writeFileSync(path.join(base, 'modules/Theme/Providers/Guest.php'), '<?php return null;');
        const manifest = path.join(base, 'bootstrap/cache/packages.php');
        fs.writeFileSync(manifest, "<?php return ['unrelated/pkg'=>['providers'=>['Acme\\\\App']]];\n");
        const original = fs.readFileSync(manifest, 'utf8');
        const hasPhp = spawnSync('php', ['-v'], {encoding:'utf8'}).status === 0;
        const run = mode => hasPhp
            ? spawnSync('php', [script, base, mode], {encoding:'utf8'})
            : spawnSync('docker', ['run','--rm','--network','none',
                '-v',path.join(__dirname,'..')+':/app:ro',
                '-v',base+':/sandbox', 'php:8.3-cli',
                'php','/app/scripts/register_guest_provider.php','/sandbox',mode],
                {encoding:'utf8'});
        assert.equal(run('verify').status, 1);
        assert.equal(run('install').status, 0);
        assert.equal(run('verify').status, 0);
        const installed = fs.readFileSync(manifest, 'utf8');
        assert.match(installed, /librecodecoop\/akaunting-theme/);
        assert.match(installed, /unrelated\/pkg/);
        assert.equal(run('install').status, 0);
        assert.equal(fs.readFileSync(manifest, 'utf8'), installed, 'Idempotent install must not rewrite');
        assert.equal(run('remove').status, 0);
        assert.equal(run('verify').status, 1);
        assert.match(fs.readFileSync(manifest,'utf8'), /unrelated\/pkg/);
        assert.equal(run('remove').status, 0);
        assert.ok(original.includes('unrelated/pkg'));
    } finally {
        fs.rmSync(base, {recursive:true, force:true});
    }
});

test('the guest provider does not register company admin routes before Akaunting macros', () => {
    const guest = fs.readFileSync(path.join(__dirname,'../Providers/Guest.php'), 'utf8');
    const main = fs.readFileSync(path.join(__dirname,'../Providers/Main.php'), 'utf8');
    const moduleComposer = JSON.parse(fs.readFileSync(path.join(__dirname,'../composer.json'), 'utf8'));
    assert.deepEqual(moduleComposer.extra.laravel.providers, ['Modules\\Theme\\Providers\\Guest']);
    assert.match(guest, /Routes\/public.php/);
    assert.doesNotMatch(guest, /Routes\/admin.php/);
    assert.match(guest, /HostContext::class/);
    assert.match(main, /Routes\/admin.php/);
    assert.doesNotMatch(main, /Routes\/public.php/);
});
