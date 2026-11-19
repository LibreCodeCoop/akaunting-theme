<!-- SPDX-FileCopyrightText: 2026 LibreCode coop and contributors -->
<!-- SPDX-License-Identifier: AGPL-3.0-or-later -->

# Akaunting Theme

Independent open-source appearance module for Akaunting 3.

The module adds configurable company colors, company imagery, a public login background (explicitly configured for one trusted company), additional footer credits, support/documentation links and a per-user color-mode preference. The original Akaunting attribution remains visible. This module is not affiliated with Akaunting Inc.

## Features

- Company-scoped sidebar/menu/content colors and imagery (PNG, JPEG, WebP).
- Optional logo displayed **alongside** the Akaunting icon, favicon and login background.
- Login slider visibility and trusted, single-instance public login branding.
- Additional footer attribution and HTTPS support/documentation links.
- Automatic dark mode following the browser/system setting, persistent per-user **System / Light / Dark** choices, and a one-click Light/Dark switch in the profile menu.
- Preferences presented in the user's own profile without overriding the Core profile view.
- CSRF protection, permission checks, company isolation and strict image/color/link validation.

## Install

Install using the Akaunting module system with module alias `theme`; do not patch Core. Run module migrations before enabling the module, then clear compiled views and application cache. A public storage symlink (`php artisan storage:link`) is needed for uploaded, non-sensitive brand images. Akaunting 3 serves public assets under `/public/`, so theme uploads use `/public/storage/` rather than the Core's protected `/storage/` URL.

Akaunting 3.3.x/PHP 8.3 is the development target. Verify runtime compatibility with your exact Core version before deployment; do not assume the module is production-certified solely because it installs.

The login is public and has no authenticated company context. By default its original branding is unchanged. To explicitly select a trusted company as the **instance-wide** public login identity, set `AKAUNTING_THEME_LOGIN_COMPANY_ID` in application configuration and rebuild configuration cache. Avoid reading a company id from an unauthenticated request.

## Configuration

Administrators with `read-settings-company` may view Settings > Theme. Changes require `update-settings-company`. Profile color mode changes require `update-auth-profile` and always affect only the authenticated user. Color mode is stored independently of the currently selected company.

The theme never modifies PDFs, invoices, DANFSe, NFS-e, customer emails or original Akaunting attribution. The color palette covers common Akaunting 3.x surfaces and interactions globally in the authenticated administration interface. Third-party modules with custom inline styles or canvas-based charts may still require explicit compatibility testing. Changes to the licensed core, redistribution or white-labeling must be evaluated independently under the applicable Akaunting terms.

## Development and testing

- `php -l` for PHP files (PHP 8.3).
- `node --test tests/*.test.cjs` for browser-preference behavior and palette contracts.
- `node tests/browser-regression.mjs` for a synthetic dashboard, bank account,
  dialogs, filters, neutral badges and colored status chips in both modes.
  Requires Chrome or `CHROME_BIN`. It evaluates computed styles, mouse hover
  and WCAG AA text contrast. No financial/customer data are used.
- Validate against the Core's compiled CSS in a local checkout with
  `AKAUNTING_CORE_CSS=/path/to/app.css AKAUNTING_ELEMENT_CSS=/path/to/element.css node tests/browser-regression.mjs`.
  The CI defaults to an isolated upstream class fixture; check actual Core CSS
  after upgrading Akaunting.
- Integration and visual checks against an isolated, disposable Akaunting installation are required before releasing.
- Check on desktop and mobile, authenticated and unauthenticated screens, high-contrast colors, forms, tables, popovers, reports, language and right-to-left pages.

## License

AGPL-3.0-or-later for the original module code. This does not relicense Akaunting Core or any third-party assets.
