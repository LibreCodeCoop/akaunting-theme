// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = file => fs.readFileSync(path.join(__dirname, '..', file), 'utf8');
const settings = read('Resources/views/settings.blade.php');
const companies = read('Resources/views/companies.blade.php');
const controller = read('Http/Controllers/ThemeController.php');

test('all persisted company images have a preview and an explicit reset control', () => {
    assert.ok(settings.includes("['logo' =>") && settings.includes("'favicon' =>"), 'Shared image card must include logo and favicon');
    assert.ok(settings.includes("$options['login_background']") && settings.includes("$options['loading_spinner']"));
    for (const name of ['logo', 'favicon', 'login_background', 'loading_spinner']) {
        const isSharedCard = ['logo', 'favicon'].includes(name);
        assert.ok(
            isSharedCard ? settings.includes('name="remove_{{ $key }}"') : settings.includes('name="remove_' + name + '"'),
            'Missing removal control for ' + name,
        );
        assert.ok(controller.includes("'remove_" + name + "'"), 'Missing reset validation for ' + name);
    }
});
test('editor shows active company name and prevents blind tenant switching', () => {
    assert.match(settings, /\$company->name/);
    assert.match(settings, /\$company->id/);
    assert.match(controller, /Company::findOrFail\(\(int\) company_id\(\)\)/);
    assert.match(controller, /auth\(\)->user\(\)->companies\(\)->enabled\(\)/);
});
test('only existing accessible Akaunting companies are listed; module has no company-create endpoint', () => {
    assert.match(companies, /@foreach \(\$companies as \$item\)/);
    assert.match(controller, /auth\(\)->user\(\)->companies\(\)->enabled\(\)/);
    assert.doesNotMatch(companies, /companies\.create/);
    assert.match(controller, /\$companies->count\(\) === 1/);
    assert.match(read('Routes/admin.php'), /Route::get\('companies'/);
});
test('image reset never deletes files or mutates another company', () => {
    assert.match(controller, /Appearance::update\(\$companyId, \$options\)/);
    assert.match(controller, /\$request->boolean\('remove_' \. \$key\)/);
    assert.doesNotMatch(controller, /Storage::delete/);
});
