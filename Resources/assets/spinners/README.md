<!-- SPDX-FileCopyrightText: 2026 LibreCode coop and contributors -->
<!-- SPDX-License-Identifier: AGPL-3.0-or-later -->

# LibreCode branded loading GIFs

- `librecode-loading.gif`: **user-provided** spinning LibreCode symbol,
  normalized to a **transparent, consistent background**. The supplied GIF
  contained opaque full-frame color changes that were unrelated to the logo.
  This distribution preserves the red/gray shapes and motion sampled from
  the source, with 20 frames at 100 ms, a 120×120-pixel canvas and 3-color
  palette (transparent, institutional red, institutional gray).
- `librecode-wordmark-loading.gif`: previous LibreCode wordmark animation,
  retained as an alternative. Regenerate locally with
  `python3 scripts/generate_librecode_spinner.py` (Pillow and Lato required).
- `librecode-mark.png`: source mark obtained from the LibreCode website for
  the alternative wordmark animation.

These sample assets are **not** imposed on all companies. Upload a GIF to the
company's Theme settings or reset to Akaunting's original indicator. The
Core application assets stay unchanged.

LibreCode trademarks and artwork are governed by their respective rights.
Open source licensing of the theme module does not imply that third parties
may claim the LibreCode name or logo.
