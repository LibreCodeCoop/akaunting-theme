// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Chrome DevTools browser verification of common Akaunting 3.3 appearance
 * patterns in both modes. No real companies, sessions, credentials or network.
 *
 * For full-Core CSS validation set:
 * AKAUNTING_CORE_CSS=/path/to/public/css/app.css
 * AKAUNTING_ELEMENT_CSS=/path/to/public/css/element.css
 */
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { mkdtempSync, readFileSync, writeFileSync, rmSync, existsSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

const root = resolve(import.meta.dirname, '..');
const source = readFileSync(process.env.AKAUNTING_THEME_VIEW ?? join(root, 'Resources/views/components/head.blade.php'), 'utf8');
const marker = '<style id="lcc-theme-brand">';
assert.ok(source.includes(marker), 'Theme stylesheet not found in module view');
let theme = source.slice(source.indexOf(marker) + marker.length, source.indexOf('</style>'));
for (const [name, color] of Object.entries({
    sidebar_color: '#fff9f8', menu_color: '#fffaf9', content_color: '#f7f8fa',
})) {
    theme = theme.replaceAll("{{ $options['" + name + "'] }}", color);
}
theme = theme.replaceAll(/@if \([^\n]*\)|@endif/g, '');

const coreCss = readFileSync(process.env.AKAUNTING_CORE_CSS ??
    join(root, 'tests/fixtures/akaunting-3.3-surfaces.css'), 'utf8');
const elementCss = process.env.AKAUNTING_ELEMENT_CSS ?
    readFileSync(process.env.AKAUNTING_ELEMENT_CSS, 'utf8') : '';

const fixture = (mode) => `<!doctype html>
<html class="${mode === 'dark' ? 'lcc-theme-dark' : ''}">
<head><meta charset="utf-8"><style>${coreCss}</style>
<style>${elementCss}</style><style id="lcc-theme-brand">${theme}</style></head>
<body class="bg-body">
<div class="js-menu"><div class="menu-scroll bg-lilac-900">
<span class="text-purple" id="rail-icon">Menu</span></div>
<nav id="sidenav-main" class="menu-list js-main-menu bg-purple-lighter">
  <button data-dropdown-toggle="dropdown-menu-company" type="button">
    <span class="text-purple" id="company-label">LibreCode</span>
  </button>
  <div class="main-menu"><a id="main-link" class="text-purple" href="#">Painel</a></div>
</nav></div>
<div id="panel"><div id="main-body">
  <header><h1>Painéis</h1><a href="#" id="new-dashboard" class="bg-green text-white">Novo Painel</a></header>
  <div id="search-surface" class="bg-white">
    <input id="search-input" class="bg-white text-black" placeholder="Pesquisar ou filtrar resultados">
  </div>
  <table class="flex flex-col divide-y divide-gray-200" id="core-table">
  <thead><tr><th class="text-black">Nome</th></tr></thead>
  <tbody data-table-body="true">
    <tr id="dashboard-row" data-table-list class="relative flex items-center px-1 group border-b hover:bg-gray-100">
      <td id="dashboard-name" class="py-4 text-sm text-black bg-white">RH</td>
      <td><span id="user-badge" class="bg-lilac-900 px-3 py-1 text-sm rounded-lg text-black">Usuário de teste</span></td>
      <td><button id="table-action" type="button" class="bg-white hover:bg-gray-100">Editar</button></td>
    </tr>
  </tbody></table>
  <div class="bg-white" id="card"><label id="card-label" class="text-black-400">Conta sintética</label>
    <span class="bg-blue-100 text-blue" id="info-chip">Informação</span>
    <span class="bg-green-100 text-green" id="success-chip">Pago</span>
    <span class="bg-red-100 text-red" id="error-chip">Rejeitado</span>
  </div>
  <div class="dropdown-menu bg-white" id="dropdown">
   <a href="#" class="hover:bg-lilac-100 text-purple" id="dropdown-item">Item</a>
  </div>
  <div class="el-select"><div class="el-input">
     <input class="el-input__inner" id="element-input" value="Selecionar">
  </div></div>
  <article id="banking-card" class="bg-white">
    <h2 id="banking-heading" class="text-purple">Conta sintética</h2>
    <div id="banking-popover" class="bg-lilac-900">
        <div id="banking-popover-label" class="text-black">Resumo de teste</div>
    </div>
    <a id="banking-action" class="bg-white hover:bg-gray-100 text-purple" href="#">Visualizar</a>
  </article>
  <div x-ref="loadingContent" id="test-core-loader">
    <img id="core-loading-image" src="/public/img/akaunting-loading.gif" width="160" height="160" />
  </div>
  <div x-ref="loadingAbsoluteContent" id="test-core-loader-absolute">
    <img id="core-loading-absolute" src="/public/img/akaunting-loading.gif" width="160" height="160" />
  </div>
  <div id="settings-dialog" class="el-dialog bg-white">
     <label class="el-form-item__label" id="dialog-label">Configuração</label>
  </div>
  <span class="relative" data-tooltip-target="tooltip-search" data-tooltip-placement="right">Search icon</span>
  <div id="tooltip-search" role="tooltip"
      class="inline-block absolute z-20 bg-white text-black rounded-lg border border-gray-200 shadow-sm"
      style="position:relative;display:inline-block;padding:6px 10px;margin:16px">
    <span id="tooltip-search-label">Pesquisar</span>
    <div id="tooltip-search-arrow" data-popper-arrow
      class="absolute w-2 h-2 before:absolute before:w-2 before:h-2 before:bg-white before:border-gray-200 before:transform before:rotate-45 before:border -left-1 before:border-t-0 before:border-r-0"></div>
  </div>
  <!-- Core action tooltips do not carry role=tooltip; regression for white squares. -->
  <div id="action-hint" data-tooltip-placement="top" class="bg-white border border-gray-200">
    <span>Visualizar</span>
    <div id="action-hint-arrow" data-popper-arrow
      class="absolute w-2 h-2 before:absolute before:w-2 before:h-2 before:bg-white before:border-gray-200 before:transform before:rotate-45 before:border"></div>
  </div>
  <div id="document-info-tip" role="tooltip" class="information-content bg-lilac-900">
    <span>Detalhes da transação</span>
    <div id="document-info-arrow" data-popper-arrow
      class="w-2 h-2 before:absolute before:w-2 before:h-2 before:bg-lilac-900 before:transform before:rotate-45"></div>
  </div>
  <div id="cashflow-chart" class="apexcharts-canvas">
    <span id="chart-legend" class="apexcharts-legend-text" style="color: #424242;">Entrada</span>
    <svg width="240" height="48">
      <text x="10" y="20" id="chart-axis" class="apexcharts-text apexcharts-yaxis-label" style="fill: #424242;">R$ 140.000,00</text>
      <line id="chart-grid" class="apexcharts-gridline" x1="0" y1="35" x2="220" y2="35" style="stroke: #eeeeee;" />
    </svg>
    <div id="chart-tooltip" class="apexcharts-tooltip apexcharts-theme-light"
        style="display:block; background: #f8fafc; color: #3d3d3d;">
      <div id="chart-tooltip-title" class="apexcharts-tooltip-title"
         style="background: #eeeeee; color: #333333;">abr 2026</div>
      <div class="apexcharts-tooltip-text">
        <span id="chart-tooltip-label" class="apexcharts-tooltip-text-y-label" style="color: #7b7b7b;">Entrada:</span>
        <span id="chart-tooltip-value" class="apexcharts-tooltip-text-y-value" style="color:#7b7b7b;">R$ 72.697,85</span>
      </div>
    </div>
    <div class="apexcharts-xaxistooltip apexcharts-xaxistooltip-bottom" id="chart-x-tooltip"
      style="background:#f8fafc;color:#424242;">
      <span class="apexcharts-xaxistooltip-text" id="chart-x-tooltip-text">abr 2026</span>
    </div>
  </div>
  <div x-ref="loadingMenu" id="loading-menu-skeleton" class="flex">
    <div class="menu-scroll bg-lilac-900" id="skeleton-rail">
      <div class="animate-pulse bg-gray-300" id="skeleton-rail-dot">•</div>
    </div>
    <nav class="menu-list" id="skeleton-panel" style="background-color:#f2f4fc">
      <div class="animate-pulse bg-gray-300" id="skeleton-dot">&nbsp;</div>
    </nav>
  </div>
</div></div>
</body></html>`;

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
async function poll(fn, timeout = 12000) {
    const start = Date.now();
    while (Date.now() - start < timeout) {
        const value = await Promise.resolve().then(fn).catch(() => null);
        if (value) return value;
        await sleep(120);
    }
    throw Error('Browser did not become ready');
}

function rgb(value) {
    const match = value.match(/rgba?\(\s*(\d+(?:\.\d+)?)\s*[, ]\s*(\d+(?:\.\d+)?)\s*[, ]\s*(\d+(?:\.\d+)?)/);
    assert.ok(match, 'Expected RGB color, got: ' + value);
    return match.slice(1, 4).map(Number);
}
function luminance(values) {
    const v = values.map(n => n / 255).map(n => n <= .04045 ? n / 12.92 : ((n + .055) / 1.055) ** 2.4);
    return .2126 * v[0] + .7152 * v[1] + .0722 * v[2];
}
function contrast(a, b) {
    const [hi, lo] = [luminance(rgb(a)), luminance(rgb(b))].sort((x, y) => y - x);
    return (hi + .05) / (lo + .05);
}
function checkReadable(name, style, min = 4.5) {
    const ratio = contrast(style.color, style.backgroundColor);
    assert.ok(ratio >= min, `${name}: WCAG AA fails (${ratio.toFixed(2)}:1) ${JSON.stringify(style)}`);
    return ratio.toFixed(2);
}

let chrome;
const tmp = mkdtempSync(join(tmpdir(), 'akaunting-theme-browser-'));
try {
    for (const mode of ['dark', 'light']) {
        writeFileSync(join(tmp, mode + '.html'), fixture(mode));
    }
    const chromeBin = process.env.CHROME_BIN || 'google-chrome';
    chrome = spawn(chromeBin, [
        '--headless=new', '--no-sandbox', '--disable-gpu',
        '--disable-dev-shm-usage', '--allow-file-access-from-files',
        '--remote-allow-origins=*', '--remote-debugging-port=0',
        '--user-data-dir=' + join(tmp, 'profile'), 'about:blank',
    ], {stdio: 'ignore'});

    const port = await poll(() => existsSync(join(tmp, 'profile/DevToolsActivePort'))
        ? Number(readFileSync(join(tmp, 'profile/DevToolsActivePort'), 'utf8').split('\n')[0]) : null);
    const pages = await poll(async () => {
        const res = await fetch(`http://127.0.0.1:${port}/json`);
        return res.ok ? await res.json() : null;
    });
    const url = pages.find(p => p.type === 'page')?.webSocketDebuggerUrl;
    assert.ok(url, 'Chrome DevTools page missing');
    const socket = new WebSocket(url);
    await new Promise((resolveOpen, rejectOpen) => {
        socket.addEventListener('open', resolveOpen, {once: true});
        socket.addEventListener('error', rejectOpen, {once: true});
    });
    let counter = 0;
    const pending = new Map();
    socket.addEventListener('message', ({data}) => {
        const reply = JSON.parse(data);
        if (!reply.id) return;
        const waiter = pending.get(reply.id);
        if (!waiter) return;
        pending.delete(reply.id);
        reply.error ? waiter.reject(new Error(JSON.stringify(reply.error))) : waiter.resolve(reply.result ?? {});
    });
    function call(method, params = {}) {
        const id = ++counter;
        return new Promise((resolveCall, reject) => {
            pending.set(id, {resolve: resolveCall, reject});
            socket.send(JSON.stringify({id, method, params}));
            setTimeout(() => {
                if (pending.delete(id)) reject(Error('Timeout in ' + method));
            }, 9000).unref();
        });
    }
    async function evaluate(code) {
        const r = await call('Runtime.evaluate', {expression: code, returnByValue: true});
        assert.ok(!r.exceptionDetails, 'Browser error ' + JSON.stringify(r.exceptionDetails));
        return r.result?.value;
    }
    async function style(selector) {
        return await evaluate(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});
            if (!e) throw Error('missing selector'); const s=getComputedStyle(e);
            return {color:s.color,backgroundColor:s.backgroundColor,borderColor:s.borderColor,fill:s.fill,stroke:s.stroke};})()`);
    }
    async function hover(selector, query = selector) {
        const documentNode = (await call('DOM.getDocument', {depth: -1})).root.nodeId;
        const node = (await call('DOM.querySelector', {nodeId: documentNode, selector: query})).nodeId;
        assert.ok(node, 'Hover target missing: ' + query);
        await call('CSS.forcePseudoState', {nodeId: node, forcedPseudoClasses: ['hover']});
        await sleep(35);
        const result = await style(selector);
        await call('CSS.forcePseudoState', {nodeId: node, forcedPseudoClasses: []});
        return result;
    }
    await call('DOM.enable');
    await call('CSS.enable');

    for (const mode of ['dark', 'light']) {
        await call('Page.navigate', {url: pathToFileURL(join(tmp, mode + '.html')).href});
        await poll(async () => await evaluate('document.readyState==="complete"'));
        const dark = mode === 'dark';
        const checks = [
            ['dashboard row badge', '#user-badge'],
            ['new dashboard action', '#new-dashboard'],
            ['information chip', '#info-chip'],
            ['success chip', '#success-chip'],
            ['error chip', '#error-chip'],
            ['search box', '#search-input'],
            ['card label', '#card-label'],
            ['element input', '#element-input'],
            ['banking popup', '#banking-popover-label'],
            ['banking action', '#banking-action'],
            ['dialog label', '#dialog-label'],
        ];
        // With the company spinner configured, the original Akaunting GIF is
        // invisible before the custom asset downloads and decodes.
        for (const selector of ['#core-loading-image', '#core-loading-absolute']) {
            const before = await style(selector);
            const visibility = await evaluate(`getComputedStyle(document.querySelector(${JSON.stringify(selector)})).visibility`);
            assert.equal(visibility, 'hidden', 'Core branded GIF must never flash: ' + selector);
            await evaluate(`(()=>{
                const image = document.querySelector(${JSON.stringify(selector)});
                image.src = '/public/storage/akaunting-theme/1/librecode.gif';
                image.classList.add('lcc-theme-loading-image');
            })()`);
            const after = await evaluate(`getComputedStyle(document.querySelector(${JSON.stringify(selector)})).visibility`);
            assert.equal(after, 'visible', 'Branded GIF must become visible: ' + selector);
        }
        if (dark) {
            for (const [name, selector] of checks) {
                const result = await style(selector);
                // A transparent background is valid for text labels inheriting a card surface.
                if (name === 'card label' || name === 'banking popup' || name === 'dialog label') {
                    const parent = name === 'card label' ? '#card' : name === 'banking popup' ? '#banking-popover' : '#settings-dialog';
                    const background = await style(parent);
                    checkReadable(name, {...result, backgroundColor: background.backgroundColor});
                } else {
                    const ratio = checkReadable(name, result);
                    console.log(mode, name, ratio + ':1');
                }
            }
            const row = await style('#dashboard-row');
            assert.notEqual(row.backgroundColor, 'rgb(255, 255, 255)', 'Dashboard row unexpectedly white');
            const hoveredRow = await hover('#dashboard-row');
            checkReadable('hovered dashboard name', {...await style('#dashboard-name'), backgroundColor: hoveredRow.backgroundColor});
            assert.equal(hoveredRow.backgroundColor, 'rgb(51, 65, 85)', 'Dashboard row hover must use dark elevated surface');
            // A hovered transaction row must not contain opaque cells of a
            // different shade: the screenshots show rectangular patches.
            const rowNode = (await call('DOM.getDocument', {depth: -1})).root.nodeId;
            const hoverNode = (await call('DOM.querySelector', {nodeId: rowNode, selector: '#dashboard-row'})).nodeId;
            await call('CSS.forcePseudoState', {nodeId:hoverNode,forcedPseudoClasses:['hover']});
            const hoveredCell = await style('#dashboard-name');
            const targetRow = await style('#dashboard-row');
            assert.equal(hoveredCell.backgroundColor, targetRow.backgroundColor,
                'Native table row has a separate rectangular cell background');
            await call('CSS.forcePseudoState', {nodeId:hoverNode,forcedPseudoClasses:[]});
            // Core table/actions and information popovers have distinct arrows.
            for (const [tip, arrow] of [
                ['#action-hint','#action-hint-arrow'],
                ['#document-info-tip','#document-info-arrow'],
            ]) {
                const bubble = await style(tip);
                const pseudo = await evaluate(`getComputedStyle(document.querySelector(${JSON.stringify(arrow)}),'::before').backgroundColor`);
                assert.equal(pseudo,bubble.backgroundColor,
                    'Tooltip arrow remains a light square: '+arrow);
            }
            const menuHover = await hover('#dropdown-item');
            checkReadable('dropdown hover', menuHover);
            const tableHover = await hover('#table-action');
            assert.notEqual(tableHover.backgroundColor, 'rgb(255, 255, 255)');
            const bankingHover = await hover('#banking-action');
            checkReadable('banking action on hover', bankingHover);
            const green = await style('#new-dashboard');
            assert.equal(green.color, 'rgb(255, 255, 255)', 'Primary action foreground must remain white');
            // ApexCharts adds inline light-colored defaults to both HTML and SVG.
            for (const [name, selector] of [
                ['cash-flow tooltip', '#chart-tooltip'],
                ['cash-flow title', '#chart-tooltip-title'],
                ['cash-flow label', '#chart-tooltip-label'],
                ['cash-flow value', '#chart-tooltip-value'],
                ['cash-flow horizontal tooltip', '#chart-x-tooltip'],
            ]) {
                const computed = await style(selector);
                if (name === 'cash-flow label' || name === 'cash-flow value') {
                    const parent = await style('#chart-tooltip');
                    checkReadable(name, {...computed, backgroundColor: parent.backgroundColor});
                } else {
                    checkReadable(name, computed);
                }
            }
            const chartBackground = await style('body');
            const legend = await style('#chart-legend');
            checkReadable('cash-flow legend', {...legend, backgroundColor: chartBackground.backgroundColor});
            const axis = await style('#chart-axis');
            checkReadable('cash-flow axis labels', {...axis, color: axis.fill, backgroundColor: chartBackground.backgroundColor});
            const grid = await style('#chart-grid');
            assert.equal(grid.stroke, 'rgb(71, 85, 105)', 'Chart grid should use subdued dark palette');
            // The Core tooltip arrow is ::before on [data-popper-arrow],
            // with before:bg-white and before:border-gray-200 utility classes.
            const tooltip = await style('#tooltip-search');
            checkReadable('native tooltip text', {
                ...await style('#tooltip-search-label'),
                backgroundColor: tooltip.backgroundColor,
            });
            const arrow = await evaluate(`(()=>{const a=document.querySelector('#tooltip-search-arrow');
                const s=getComputedStyle(a, '::before');
                return {backgroundColor:s.backgroundColor,borderColor:s.borderTopColor};})()`);
            assert.equal(arrow.backgroundColor, tooltip.backgroundColor,
                'Native Core tooltip arrow is detached from dark tooltip surface');
            if (mode === 'dark') {
                assert.notEqual(arrow.borderColor, 'rgb(229, 231, 235)',
                    'Tooltip arrow border retains the light theme color');
            }
            const skeletonPanel = await style('#skeleton-panel');
            assert.equal(skeletonPanel.backgroundColor, 'rgb(31, 41, 55)', 'Loading menu must not flash white');
            const skeletonRail = await style('#skeleton-rail');
            assert.equal(skeletonRail.backgroundColor, 'rgb(17, 24, 39)', 'Loading icon rail must match dark mode');
            const skeletonDot = await style('#skeleton-dot');
            assert.equal(skeletonDot.backgroundColor, 'rgb(71, 85, 105)', 'Loading placeholders must be dark');
            console.log(mode, 'chart/skeleton themes OK');
        } else {
            const badge = await style('#user-badge');
            assert.equal(badge.backgroundColor, 'rgb(220, 226, 249)', 'Light mode must preserve upstream badge colors');
            const skeleton = await style('#skeleton-panel');
            assert.equal(skeleton.backgroundColor, 'rgb(242, 244, 252)', 'Light skeleton should retain Core palette');
            const tip = await style('#chart-tooltip');
            assert.equal(tip.backgroundColor, 'rgb(248, 250, 252)', 'Light tooltip should remain native');
        }
        if (process.env.AKAUNTING_THEME_SCREENSHOTS) {
            mkdirSync(process.env.AKAUNTING_THEME_SCREENSHOTS, {recursive: true});
            const result = await call('Page.captureScreenshot', {format: 'png', captureBeyondViewport: false});
            writeFileSync(join(process.env.AKAUNTING_THEME_SCREENSHOTS, mode + '.png'), Buffer.from(result.data, 'base64'));
        }
    }
    console.log('PASS: semantic surfaces and interactive states in both color modes');
    socket.close();
} finally {
    chrome?.kill('SIGTERM');
    await sleep(200);
    rmSync(tmp, {recursive: true, force: true});
}
