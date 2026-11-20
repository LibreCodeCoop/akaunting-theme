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
if (D::validSubdomain('alpha.akaunting.example.coop', '')) {
    throw new RuntimeException('Unconfigured instance host accepted.');
}
echo "PASS: company domain normalization, strict suffix and invalid hosts\n";
