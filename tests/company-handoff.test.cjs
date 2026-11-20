// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8');
const middleware = read('Http/Middleware/HostContext.php');
const ticket = read('Domain/CompanyTransfer.php');
const redeem = read('Http/Controllers/TransferController.php');

test('cross-domain routing starts disabled, requires canonical verified hosts and uses host-only cookies', () => {
    assert.match(read('config/theme.php'), /domain_routing_enabled.*false/);
    assert.match(middleware, /if \(! config\('theme\.domain_routing_enabled', false\)\)/);
    assert.match(middleware, /DomainRegistry::activeForHost/);
    assert.match(ticket, /DomainRegistry::primaryForCompany/);
    assert.match(ticket, /random_bytes\(32\)/);
    assert.match(ticket, /hash\('sha256', \$ticket\)/);
    assert.doesNotMatch(ticket, /SESSION_DOMAIN/);
});
test('verified sibling-domain aliases require an explicit instance-wide trust zone', () => {
    const registry = read('Domain/DomainRegistry.php');
    const controller = read('Http/Controllers/DomainController.php');
    assert.match(registry, /oneLabelBelow\(\$baseHost, \$aliasZone\)/);
    assert.match(registry, /\$host !== \$baseHost/);
    assert.match(controller, /LoginAppearance::options\(\)\['alias_zone'\]/);
});
test('handoff is single use, host-bound and rechecks company membership', () => {
    assert.match(ticket, /->where\('destination_host', \$host\)/);
    assert.match(ticket, /->whereNull\('consumed_at'\)/);
    assert.match(ticket, /->where\('expires_at', '>', now\(\)\)/);
    assert.match(ticket, /->lockForUpdate\(\)->first\(\)/);
    assert.match(ticket, /self::allowed\(\$user, \(int\) \$row->company_id\)/);
    assert.match(ticket, /self::canonicalHost\(\(int\) \$row->company_id\)/);
});
test('origin session invalidated, target session regenerated, transfer does not accept arbitrary destinations', () => {
    assert.match(middleware, /auth\(\)->logout\(\)/);
    assert.match(middleware, /\$request->session\(\)->invalidate\(\)/);
    assert.match(middleware, /CompanyTransfer::redirectUrl\(\$host, \$ticket\)/);
    assert.match(redeem, /\$request->session\(\)->regenerate\(\)/);
    assert.match(redeem, /Referrer-Policy/);
    assert.match(redeem, /Cache-Control/);
});
test('company A login can select accessible B without preserving A brand; company switching handled', () => {
    assert.match(middleware, /\$route === 'login.store'/);
    assert.match(middleware, /CompanyTransfer::allowed\(auth\(\)->user\(\), \(int\) \$domain->company_id\)/);
    assert.match(middleware, /\$response instanceof RedirectResponse/);
    assert.match(middleware, /abort\(409, 'Switch to the company domain/);
});
