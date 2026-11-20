#!/bin/sh
# SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
# SPDX-License-Identifier: AGPL-3.0-or-later
# Use an isolated Composer project; never mutate application or production data.
set -eu
PROJECT_ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
TEST_ROOT="$(mktemp -d)"
trap 'rm -rf "$TEST_ROOT"' EXIT HUP INT TERM
cat > "$TEST_ROOT/composer.json" <<'JSON'
{
  "name": "test/theme-path-discovery",
  "description": "Disposable test for early Laravel package discovery",
  "repositories": [
    {"type":"path", "url":"/module", "options":{"symlink":true}},
    {"packagist.org":false}
  ],
  "require": {"php":"^8.3"},
  "minimum-stability":"dev",
  "prefer-stable":true
}
JSON
docker run --rm --network none --entrypoint sh \
  --user "$(id -u):$(id -g)" -e HOME=/tmp -e COMPOSER_HOME=/workspace/.composer \
  -v "$PROJECT_ROOT:/module:ro" -v "$TEST_ROOT:/workspace" \
  --workdir /workspace composer:2 -c '
    set -eu
    export COMPOSER_DISABLE_NETWORK=1
    composer require "librecodecoop/akaunting-theme:*@dev" \
      --no-interaction --no-plugins --no-scripts --no-audit --no-progress --no-ansi
    php -r '\''
      $installed = json_decode(file_get_contents("vendor/composer/installed.json"), true);
      $found = array_values(array_filter($installed["packages"],
          fn($pkg) => $pkg["name"] === "librecodecoop/akaunting-theme"));
      if (count($found) !== 1 ||
          ($found[0]["extra"]["laravel"]["providers"] ?? []) !==
              [implode(chr(92), ["Modules", "Theme", "Providers", "Guest"])]) {
          fwrite(STDERR, "Package not auto-discoverable.\\n"); exit(1);
      }
      if (!is_link("vendor/librecodecoop/akaunting-theme")) {
          fwrite(STDERR, "Module was duplicated instead of linked.\\n"); exit(1);
      }
    '\''
    composer dump-autoload --no-interaction --no-scripts --no-plugins --no-ansi
    composer install --no-interaction --no-scripts --no-plugins --no-progress --no-ansi
    php -r '\''
      $installed = json_decode(file_get_contents("vendor/composer/installed.json"), true);
      $packages = array_filter($installed["packages"],
          fn($pkg) => $pkg["name"] === "librecodecoop/akaunting-theme");
      if (count($packages) !== 1 ||
          !is_file("vendor/librecodecoop/akaunting-theme/Providers/Guest.php")) {
          fwrite(STDERR, "Composer update lost Theme provider.\\n"); exit(1);
      }
      echo "PASS: path package and guest provider survive Composer autoload refresh\\n";
    '\''
  '
