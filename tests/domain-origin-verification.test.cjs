// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = name => fs.readFileSync(path.join(__dirname,'..',name),'utf8');

test('public origin proof is host and pending-record bound, throttled and read-only', () => {
 const controller = read('Http/Controllers/PublicDomainProofController.php');
 const routes = read('Routes/public.php');
 assert.match(routes, /theme\/domain-proof\/\{token\}/);
 assert.match(routes, /throttle:12,1/);
 assert.match(controller, /DomainRegistry::validSubdomain/);
 assert.match(controller, /->where\('host', \$host\)/);
 assert.match(controller, /->where\('status', 'pending'\)/);
 assert.match(controller, /->where\('verification_token', \$token\)/);
 assert.match(controller, /Cache-Control.*private, no-store/);
 assert.doesNotMatch(controller, /->(insert|update|delete)\(/);
});
test('domain activation requires the DNS record, public certificate and this Akaunting origin', () => {
 const controller = read('Http/Controllers/DomainController.php');
 const registry = read('Domain/DomainRegistry.php');
 assert.match(controller, /DomainRegistry::verifyDns/);
 assert.match(controller, /DomainRegistry::verifyTls/);
 assert.match(controller, /DomainRegistry::verifyHostRoute/);
 assert.match(registry, /FILTER_FLAG_NO_PRIV_RANGE/);
 assert.match(registry, /FILTER_FLAG_NO_RES_RANGE/);
 assert.match(registry, /follow_location' => 0/);
 assert.match(registry, /'verify_peer_name' => true/);
 assert.match(registry, /hash_equals\('akaunting-theme-origin=' \./);
});
