// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8');
const head = read('Resources/views/components/head.blade.php');

test('ApexCharts charts receive legible tooltip, legend, axis and grid palettes in dark mode', () => {
    for (const selector of [
        '.apexcharts-tooltip', '.apexcharts-tooltip-title', '.apexcharts-tooltip-text',
        '.apexcharts-legend-text', '.apexcharts-xaxistooltip',
        '.apexcharts-yaxistooltip', '.apexcharts-xaxis-label', '.apexcharts-yaxis-label',
        '.apexcharts-gridline',
    ]) {
        assert.ok(head.includes('html.lcc-theme-dark ' + selector), selector + ' missing dark override');
    }
});

test('Core sidebar-loading skeleton has dark surfaces and dark placeholders', () => {
    assert.ok(head.includes('[x-ref="loadingMenu"] .menu-list'));
    assert.ok(head.includes('[x-ref="loadingMenu"] .animate-pulse.bg-gray-300'));
    assert.ok(head.includes('[x-ref="loadingMenu"] > .menu-scroll'));
});

test('Animated company spinner is configurable, GIF validated and resettable', () => {
    const defaults = read('Domain/Appearance.php');
    const controller = read('Http/Controllers/ThemeController.php');
    const settings = read('Resources/views/settings.blade.php');
    assert.match(defaults, /'loading_spinner'\s*=>\s*''/);
    assert.match(controller, /'loading_spinner'\s*=>\s*.*mimes:gif/);
    assert.match(controller, /'remove_loading_spinner'/);
    assert.match(settings, /name="loading_spinner"/);

    assert.match(settings, /name="remove_loading_spinner"/);
});

test('Theme only replaces known Core loader images, scoped to authenticated company', () => {
    assert.match(head, /@json\(\$spinner\)/);
    assert.match(head, /\[x-ref="loadingContent"\]/);
    assert.match(head, /\[x-ref="loadingAbsoluteContent"\]/);
    assert.ok(!head.includes('document.querySelectorAll("img")'), 'unscoped image mutation');
});
