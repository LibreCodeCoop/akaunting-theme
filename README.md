<!-- SPDX-FileCopyrightText: 2026 LibreCode coop and contributors -->
<!-- SPDX-License-Identifier: AGPL-3.0-or-later -->

# Akaunting Theme

Independent open-source appearance module for Akaunting 3.

The module adds configurable company colors, company imagery, a public login background (explicitly configured for one trusted company), additional footer credits, support/documentation links and a per-user color-mode preference. The original Akaunting attribution remains visible. This module is not affiliated with Akaunting Inc.

## Features

- Company-scoped sidebar/menu/content colors and imagery (PNG, JPEG, WebP).
- Company-scoped animated **GIF loading indicator** (up to 1 MiB, 320×320 pixels), with reset to upstream default; supports static logo fallback under reduced-motion preferences.
- Company logo replacing the company-selector icon, plus favicon and login background.
- Login slider visibility and trusted, single-instance public login branding.
- Additional footer attribution and HTTPS support/documentation links.
- Native Akaunting loading-menu skeleton and ApexCharts SVG legends, axis labels and HTML tooltip colors for dark mode.
- Native Core tooltip arrows (all orientations) match their popover background in dark mode, preventing white diamonds.
- Company loading asset is preloaded; the stock Akaunting GIF is hidden immediately to prevent mixed-brand flashes.
- Automatic dark mode following the browser/system setting, persistent per-user **System / Light / Dark** choices, and a one-click Light/Dark switch in the profile menu.
- Preferences presented in the user's own profile without overriding the Core profile view.
- CSRF protection, permission checks, company isolation and strict image/color/link validation.

## Install

Install using the Akaunting module system with module alias `theme`; do not patch Core. Run module migrations before enabling the module, then clear compiled views and application cache. A public storage symlink (`php artisan storage:link`) is needed for uploaded, non-sensitive brand images. Akaunting 3 serves public assets under `/public/`, so theme uploads use `/public/storage/` rather than the Core's protected `/storage/` URL.

Akaunting 3.3.x/PHP 8.3 is the development target. Verify runtime compatibility with your exact Core version before deployment; do not assume the module is production-certified solely because it installs.

The user-configured loading GIF is preloaded before replacing the original image source and is used only in **authenticated** Core loading-content and loading-absolute overlays, and never changes the public login or the Core's original GIF file. The included example, `Resources/assets/spinners/librecode-loading.gif`, is a user-provided LibreCode spinning symbol normalized to transparent frames; it is **not** a default for other companies. Upload via Settings > Theme to activate for a specific company. As an accessibility measure, browsers requesting reduced motion receive the company's static logo when available.

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


## Administration and company domains

- Company themes: explicit company context, saved image previews and
  unlink/replacement controls. No second CRUD of Akaunting companies.
- Instance login: separate privileged configuration of canonical hostname,
  logo/favicon/background, carousel and up to six slides.
- Company domains: DNS TXT and HTTPS-verified aliases with a primary choice;
  company login settings become usable only after verification.
- Session handoff: optional, off by default, bound to one verified host,
  one company and a short-lived single-use code. No shared domain cookies.

Read docs/theme-admin.md and docs/company-domains.md before activating
company domain routing in a live installation.
