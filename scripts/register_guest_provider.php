<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

/**
 * Akaunting registers normal modules only after company identification.
 * Anonymous login requires a separate, early-discovered Laravel provider.
 *
 * This command changes ONLY Laravel's generated package manifest; it never
 * edits Akaunting's PHP sources or the root composer.json/composer.lock.
 * Run after Composer package discovery and after deploying the module.
 *
 * php modules/Theme/scripts/register_guest_provider.php /var/www/html install
 * php modules/Theme/scripts/register_guest_provider.php /var/www/html verify
 * php modules/Theme/scripts/register_guest_provider.php /var/www/html remove
 */
$root = realpath($argv[1] ?? '');
$mode = $argv[2] ?? '';
if (! $root || ! in_array($mode, ['install', 'verify', 'remove'], true) ||
    ! is_file($root . '/bootstrap/app.php') ||
    ! is_file($root . '/modules/Theme/Providers/Guest.php') ||
    ! is_file($root . '/bootstrap/cache/packages.php')) {
    fwrite(STDERR, "Invalid Akaunting root or action.\n");
    exit(2);
}

$path = $root . '/bootstrap/cache/packages.php';
$provider = 'Modules\\Theme\\Providers\\Guest';
$key = 'librecodecoop/akaunting-theme';
$loaded = require $path;
if (! is_array($loaded)) {
    fwrite(STDERR, "Unexpected Laravel package manifest.\n");
    exit(2);
}
if (isset($loaded[$key]) &&
    ($loaded[$key]['providers'] ?? []) !== [$provider]) {
    fwrite(STDERR, "Different package manifest entry exists; refusing overwrite.\n");
    exit(2);
}

$active = isset($loaded[$key]);
if ($mode === 'verify') {
    if (! $active) {
        fwrite(STDERR, "Early Theme guest provider not registered.\n");
        exit(1);
    }
    echo "Theme guest provider registered.\n";
    exit(0);
}
if (($mode === 'install' && $active) || ($mode === 'remove' && ! $active)) {
    echo "Theme guest provider already in requested state.\n";
    exit(0);
}
if ($mode === 'install') {
    $loaded[$key] = ['providers' => [$provider]];
} else {
    unset($loaded[$key]);
}

$source = '<?php return ' . var_export($loaded, true) . ';' . PHP_EOL;
$tmp = tempnam(dirname($path), '.lcc-theme-pkg-');
if ($tmp === false) throw new RuntimeException('Cannot create atomic package manifest.');
try {
    if (file_put_contents($tmp, $source, LOCK_EX) !== strlen($source)) {
        throw new RuntimeException('Incomplete package manifest write.');
    }
    $stat = stat($path);
    if ($stat === false) {
        throw new RuntimeException('Cannot stat package manifest.');
    }
    chmod($tmp, $stat['mode'] & 0777);
    if (function_exists('chown')) {
        @chown($tmp, $stat['uid']);
        @chgrp($tmp, $stat['gid']);
    }
    if (! rename($tmp, $path)) {
        throw new RuntimeException('Atomic manifest replacement failed.');
    }
} finally {
    if (is_file($tmp)) unlink($tmp);
}
echo "Theme guest provider " . ($mode === 'install' ? 'installed' : 'removed') . ".\n";
