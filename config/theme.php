<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

return [
    // Use a trusted, fixed company for public login branding, never an incoming company_id.
    // Leave unset to preserve the upstream login.
    'login_company_id' => (int) env('AKAUNTING_THEME_LOGIN_COMPANY_ID', 0),
];
