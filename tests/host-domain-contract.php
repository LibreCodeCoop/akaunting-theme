<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

require dirname(__DIR__) . '/Domain/DomainRegistry.php';

use Modules\Theme\Domain\DomainRegistry as D;

$base = 'akaunting.example.coop';
$valid = [
    'alpha.akaunting.example.coop',
    'beta-2.akaunting.example.coop',
    '9.akaunting.example.coop',
];
$invalid = [
    'akaunting.example.coop',
    'alpha.akaunting.example.coop.evil.com',
    'alpha.beta.akaunting.example.coop',
    'alpha..akaunting.example.coop',
    'alpha.akaunting.example.coop:443',
    '127.0.0.1',
    'localhost',
    '../alpha.akaunting.example.coop',
    '*.akaunting.example.coop',
    'al_pha.akaunting.example.coop',
    'http://alpha.akaunting.example.coop',
    'a-.akaunting.example.coop',
    '-a.akaunting.example.coop',
];
foreach ($valid as $host) {
    if (! D::validSubdomain($host, $base)) {
        throw new RuntimeException('Valid subdomain rejected: ' . $host);
    }
}
foreach ($invalid as $host) {
    if (D::validSubdomain($host, $base)) {
        throw new RuntimeException('Invalid subdomain accepted: ' . $host);
    }
}
if (D::normalize('ALPHA.AKAUNTING.EXAMPLE.COOP.') !== 'alpha.akaunting.example.coop') {
    throw new RuntimeException('Normalization failed.');
}
// Explicit, administrator-controlled organizational zone permits sibling
// aliases while preserving one-label/suffix restrictions.
if (D::validSubdomain('accounting.example.coop', $base)) {
    throw new RuntimeException('Sibling alias accepted without configured trust zone.');
}
foreach (['accounting.example.coop', 'finance.example.coop'] as $alias) {
    if (! D::validSubdomain($alias, $base, 'example.coop')) {
        throw new RuntimeException('Explicit corporate alias rejected: ' . $alias);
    }
}
foreach (['accounting.example.coop.attacker.test','x.accounting.example.coop',
          'example.coop', 'akaunting.example.coop','evil.example.org',
          'accounting.coop', '127.0.0.1'] as $alias) {
    if (D::validSubdomain($alias, $base, 'example.coop')) {
        throw new RuntimeException('Untrusted alias accepted: ' . $alias);
    }
}
if (D::validSubdomain('accounting.example.coop', $base, 'coop')) {
    throw new RuntimeException('Invalid trust zone accepted.');
}
if (D::validSubdomain('alpha.akaunting.example.coop', '')) {
    throw new RuntimeException('Unconfigured instance host accepted.');
}
echo "PASS: company domain normalization, strict suffix and invalid hosts\n";
