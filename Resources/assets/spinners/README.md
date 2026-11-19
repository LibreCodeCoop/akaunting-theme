<!-- SPDX-FileCopyrightText: 2026 LibreCode coop and contributors -->
<!-- SPDX-License-Identifier: AGPL-3.0-or-later -->

# Branded loading animation

`librecode-loading.gif` is a 238×96px, 24-frame, looped, transparent GIF
containing **only** LibreCode's red/gray institutional mark, its wordmark and
a subtle three-dot red loading animation. It deliberately contains no Akaunting
logo or ring. Source file: `librecode-mark.png`, downloaded from
`https://librecode.coop/assets/images/favico.png`. Rebuild offline with
`python3 scripts/generate_librecode_spinner.py` (requires Pillow and Lato).

The module **never applies it globally**: company administrators may upload it,
upload another GIF or restore the stock Akaunting loading image. The original
Akaunting Core animation remains unchanged.

The source identity artwork originates from the LibreCode brand materials and is credited to LibreCode coop; the canonical brand repository's artwork is under CC-BY-SA-4.0. LibreCode logos and trademarks retain their respective rights. Open-sourcing
the module does not grant rights to use LibreCode's trademark as one's own.
