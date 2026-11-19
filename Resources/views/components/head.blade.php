@php
    $logo = $options['logo'] ? asset('public/storage/' . $options['logo']) : '';
    $favicon = $options['favicon'] ? asset('public/storage/' . $options['favicon']) : '';
    $spinner = $options['loading_spinner'] ? asset('public/storage/' . $options['loading_spinner']) : '';
    $spinnerFallback = $logo;
@endphp
@if ($favicon)
<link rel="icon" href="{{ $favicon }}" type="image/png" id="lcc-theme-favicon">
@endif
<style id="lcc-theme-brand">
:root {
    --lcc-theme-sidebar: {{ $options['sidebar_color'] }};
    --lcc-theme-menu: {{ $options['menu_color'] }};
    --lcc-theme-content: {{ $options['content_color'] }};
    --lcc-theme-rail-bg: #343c49;
    --lcc-theme-rail-foreground: #f8fafc;
}
html:not(.lcc-theme-dark) body #sidenav-main { background-color: var(--lcc-theme-sidebar) !important; color: #334155 !important; }
html:not(.lcc-theme-dark) body #sidenav-main .main-menu { background-color: var(--lcc-theme-menu) !important; }
html:not(.lcc-theme-dark) body #main-body { background-color: var(--lcc-theme-content); }
/* The 3.3.x narrow quick-action rail uses bg-lilac-900 and is separate from the company menu. */
html:not(.lcc-theme-dark) body .js-menu > .menu-scroll.bg-lilac-900 {
    background-color: var(--lcc-theme-rail-bg) !important;
    color: var(--lcc-theme-rail-foreground) !important;
}
html:not(.lcc-theme-dark) body .js-menu > .menu-scroll.bg-lilac-900 .text-purple,
html:not(.lcc-theme-dark) body .js-menu > .menu-scroll.bg-lilac-900 .material-icons-outlined {
    color: var(--lcc-theme-rail-foreground) !important;
}
html:not(.lcc-theme-dark) body .js-menu > .menu-scroll.bg-lilac-900 .menu-button:hover,
html:not(.lcc-theme-dark) body .js-menu > .menu-scroll.bg-lilac-900 .menu-button:focus-visible {
    background-color: #4b5563 !important;
    border-radius: 6px;
}
html:not(.lcc-theme-dark) body #sidenav-main .text-purple {
    color: #334155 !important;
}
/* Upstream hover:bg-purple can meet text-purple and produce same-color text on fill. */
html:not(.lcc-theme-dark) body .hover\:bg-purple:hover {
    background-color: #55588b !important;
    color: #ffffff !important;
}
html:not(.lcc-theme-dark) body .hover\:bg-purple:hover .text-purple,
html:not(.lcc-theme-dark) body .hover\:bg-purple:hover span,
html:not(.lcc-theme-dark) body .hover\:bg-purple:hover svg {
    color: #ffffff !important;
}
@if ($logo)
/* The company selector gets its own icon. The upstream Akaunting attribution stays in the footer. */
#sidenav-main .lcc-theme-company-logo { width: 28px; height: 28px; object-fit: contain; }
html.lcc-theme-dark #sidenav-main .lcc-theme-company-logo {
    background-color: #ffffff; padding: 2px; border-radius: 5px;
}
@endif
/* Dark mode follows the rendered core surfaces without mutating upstream styles. */
html.lcc-theme-dark { color-scheme: dark; }
/* Dark surfaces: readable navigation, icons and focus states (WCAG AA foreground contrast). */
html.lcc-theme-dark body .js-menu > .menu-scroll.bg-lilac-900 {
    background-color: #111827 !important;
    color: #f1f5f9 !important;
}
html.lcc-theme-dark body .js-menu > .menu-scroll.bg-lilac-900 .text-purple,
html.lcc-theme-dark body .js-menu > .menu-scroll.bg-lilac-900 .material-icons-outlined {
    color: #f1f5f9 !important;
}
html.lcc-theme-dark body .js-menu > .menu-scroll.bg-lilac-900 .menu-button:hover,
html.lcc-theme-dark body .js-menu > .menu-scroll.bg-lilac-900 .menu-button:focus-visible {
    background-color: #374151 !important;
    border-radius: 6px;
}
html.lcc-theme-dark #sidenav-main,
html.lcc-theme-dark #sidenav-main .main-menu {
    background-color: #1f2937 !important;
    color: #e2e8f0 !important;
}
html.lcc-theme-dark #sidenav-main a,
html.lcc-theme-dark #sidenav-main button,
html.lcc-theme-dark #sidenav-main summary,
html.lcc-theme-dark #sidenav-main span,
html.lcc-theme-dark #sidenav-main svg {
    color: #e2e8f0 !important;
}
html.lcc-theme-dark #sidenav-main .text-gray-400 {
    color: #cbd5e1 !important;
}
html.lcc-theme-dark body,
html.lcc-theme-dark .bg-body,
html.lcc-theme-dark #panel,
html.lcc-theme-dark #main-body { background: #111827 !important; color: #e5e7eb !important; }
html.lcc-theme-dark .bg-white,
html.lcc-theme-dark .bg-gray-50,
html.lcc-theme-dark .bg-gray-100,
html.lcc-theme-dark .bg-black-50,
html.lcc-theme-dark .bg-black-100,
html.lcc-theme-dark .card,
html.lcc-theme-dark .el-card,
html.lcc-theme-dark .el-dialog,
html.lcc-theme-dark .el-input__inner,
html.lcc-theme-dark .el-select-dropdown,
html.lcc-theme-dark .el-table,
html.lcc-theme-dark .el-table__header-wrapper,
html.lcc-theme-dark .el-table__body-wrapper,
html.lcc-theme-dark .js-main-menu,
html.lcc-theme-dark .main-menu,
html.lcc-theme-dark .profile-menu,
html.lcc-theme-dark .user-menu,
html.lcc-theme-dark input:not([type=checkbox]):not([type=radio]),
html.lcc-theme-dark select,
html.lcc-theme-dark textarea {
    background-color: #1f2937 !important;
    color: #e5e7eb !important;
    border-color: #374151 !important;
}
/* Akaunting uses dark-purple utility classes and a hard-coded #595959 in selects. */
html.lcc-theme-dark .text-purple,
html.lcc-theme-dark .text-purple-200,
html.lcc-theme-dark .text-purple-500,
html.lcc-theme-dark .text-purple-600,
html.lcc-theme-dark .text-purple-700,
html.lcc-theme-dark .text-gray-500,
html.lcc-theme-dark .text-gray-400,
html.lcc-theme-dark .text-black-600,
html.lcc-theme-dark .text-black-700,
html.lcc-theme-dark .el-form-item__label,
html.lcc-theme-dark .el-table__empty-text,
html.lcc-theme-dark .el-select-dropdown__item,
html.lcc-theme-dark .el-tabs__item,
html.lcc-theme-dark .el-pagination button,
html.lcc-theme-dark .el-pagination span,
html.lcc-theme-dark .el-input__icon {
    color: #e2e8f0 !important;
}
html.lcc-theme-dark .el-select .el-input .el-input__inner,
html.lcc-theme-dark .un-el-select {
    color: #f1f5f9 !important;
    background-color: #1f2937 !important;
    border-color: #475569 !important;
}
html.lcc-theme-dark .el-table__row:hover > td { background-color: #29384b !important; }
html.lcc-theme-dark label:not([class*="text-"]) { color: #e2e8f0; }
html.lcc-theme-dark .text-black,
html.lcc-theme-dark .text-black-400,
html.lcc-theme-dark .text-black-500,
html.lcc-theme-dark .text-gray-900,
html.lcc-theme-dark .text-gray-800,
html.lcc-theme-dark .text-gray-700,
html.lcc-theme-dark .text-gray-600 { color: #e5e7eb !important; }
html.lcc-theme-dark .border-gray-100,
html.lcc-theme-dark .border-gray-200,
html.lcc-theme-dark .border-gray-300 { border-color: #374151 !important; }
html.lcc-theme-dark .el-table tr,
html.lcc-theme-dark .el-table th,
html.lcc-theme-dark .el-table td { background-color: #1f2937 !important; color: #e5e7eb !important; }
html.lcc-theme-dark a { color: #a5b4fc; }
html.lcc-theme-dark input::placeholder { color: #9ca3af; }
/* Dropdowns/cards use Tailwind hover utilities that otherwise restore light or purple fills. */
html.lcc-theme-dark .hover\:bg-lilac-100:hover,
html.lcc-theme-dark .hover\:bg-lilac-900:hover,
html.lcc-theme-dark .hover\:bg-purple:hover,
html.lcc-theme-dark .hover\:bg-purple-100:hover,
html.lcc-theme-dark .hover\:bg-purple-lighter:hover,
html.lcc-theme-dark .hover\:bg-gray-100:hover,
html.lcc-theme-dark .hover\:bg-gray-200:hover,
html.lcc-theme-dark .hover\:bg-white:hover,
html.lcc-theme-dark .group:hover .group-hover\:bg-gray-100,
html.lcc-theme-dark .dropdown-menu li:hover,
html.lcc-theme-dark .dropdown-item:hover,
html.lcc-theme-dark .el-dropdown-menu__item:hover,
html.lcc-theme-dark .el-select-dropdown__item.hover,
html.lcc-theme-dark .el-select-dropdown__item:hover,
html.lcc-theme-dark .el-select-dropdown__item.selected {
    background-color: #334155 !important;
    color: #f1f5f9 !important;
}
/* Match the foreground of interactive child labels with the dark hover background. */
html.lcc-theme-dark .dropdown-menu li:hover .text-purple,
html.lcc-theme-dark .dropdown-menu li:hover a,
html.lcc-theme-dark .dropdown-menu li:hover button,
html.lcc-theme-dark .dropdown-menu li:hover span,
html.lcc-theme-dark .hover\:bg-purple:hover .text-purple,
html.lcc-theme-dark .hover\:bg-purple:hover .group-hover\:text-white,
html.lcc-theme-dark .el-select-dropdown__item.hover span,
html.lcc-theme-dark .el-select-dropdown__item:hover span {
    color: #f1f5f9 !important;
}
/* Element UI: rows may use :hover, .hover-row, .current-row or fixed-column clones. */
html.lcc-theme-dark .el-table--enable-row-hover .el-table__body tr:hover > td,
html.lcc-theme-dark .el-table__body tr.hover-row > td,
html.lcc-theme-dark .el-table__body tr.hover-row.current-row > td,
html.lcc-theme-dark .el-table__body tr.current-row > td,
html.lcc-theme-dark .el-table__fixed-body-wrapper .el-table__body tr:hover > td,
html.lcc-theme-dark .el-table__fixed-right .el-table__body tr:hover > td,
html.lcc-theme-dark table tbody tr:hover > td,
html.lcc-theme-dark table tbody tr:hover > th {
    background-color: #29384b !important;
    color: #f1f5f9 !important;
}
@media (prefers-reduced-motion: no-preference) {
    html.lcc-theme-dark * { scrollbar-color: #4b5563 #111827; }
}

/*
 * Global Akaunting 3.x dark-mode surface contract.
 *
 * All neutral backgrounds and foregrounds map to these semantic tokens, rather
 * than relying on route-specific view markup. Keep meaningful state/status
 * colors separate: a red error and a green success must remain identifiable.
 * The rules are scoped to html.lcc-theme-dark and do not alter Akaunting Core.
 */
html.lcc-theme-dark {
    --lcc-dark-page: #111827;
    --lcc-dark-surface: #1f2937;
    --lcc-dark-surface-alt: #273447;
    --lcc-dark-elevated: #334155;
    --lcc-dark-hover: #334155;
    --lcc-dark-border: #475569;
    --lcc-dark-text: #f1f5f9;
    --lcc-dark-muted: #cbd5e1;
    --lcc-dark-link: #a5b4fc;
}

/* Tailwind surface utilities used throughout server-rendered pages and Vue. */
html.lcc-theme-dark :is(
    .bg-white,
    .bg-gray-50, .bg-gray-100, .bg-black-50, .bg-black-100,
    .bg-purple-lighter, .bg-lilac-100, .bg-lilac-900,
    .bg-wisteria, .bg-gray-200, .bg-purple-100
) {
    background-color: var(--lcc-dark-surface) !important;
}
html.lcc-theme-dark :is(.bg-gray-200, .bg-gray-300, .bg-lilac-300, .bg-purple-200) {
    background-color: var(--lcc-dark-elevated) !important;
}
/* Pale informational chips are not surfaces for normal white-on-light labels. */
html.lcc-theme-dark .bg-blue-100 {
    background-color: #1e3652 !important;
    color: #bfdbfe !important;
}
html.lcc-theme-dark .bg-green-100 {
    background-color: #123426 !important;
    color: #86efac !important;
}
html.lcc-theme-dark .bg-red-100 {
    background-color: #45232b !important;
    color: #fca5a5 !important;
}
html.lcc-theme-dark .bg-orange-100,
html.lcc-theme-dark .bg-yellow-100 {
    background-color: #48321c !important;
    color: #fde68a !important;
}

/* Contrast-safe normal and muted text on any dark neutral surface. */
html.lcc-theme-dark :is(
    .text-black, .text-black-200, .text-black-300, .text-black-400,
    .text-black-500, .text-black-600, .text-black-700,
    .text-gray-900, .text-gray-800, .text-gray-700, .text-gray-600,
    .text-purple, .text-purple-500, .text-purple-600, .text-purple-700
) {
    color: var(--lcc-dark-text) !important;
}
html.lcc-theme-dark :is(.text-gray-500, .text-gray-400, .text-muted, .text-light-gray) {
    color: var(--lcc-dark-muted) !important;
}
html.lcc-theme-dark :is(.text-green, .text-green-500, .text-green-700) {
    color: #86efac !important;
}
html.lcc-theme-dark :is(.text-red, .text-red-500, .text-red-600, .text-red-700) {
    color: #fca5a5 !important;
}
html.lcc-theme-dark :is(.text-blue, .text-blue-500, .text-blue-700) {
    color: #93c5fd !important;
}

/* Core's flex table is distinct from Element UI's HTML table. Its rows carry
 * the background-hover utility on TR itself, not on TD as Element UI does. */
html.lcc-theme-dark table.flex.flex-col tbody tr.group,
html.lcc-theme-dark table.flex.flex-col tbody tr[data-table-list] {
    background-color: var(--lcc-dark-surface) !important;
    color: var(--lcc-dark-text) !important;
}
html.lcc-theme-dark table.flex.flex-col tbody tr.group:hover,
html.lcc-theme-dark table.flex.flex-col tbody tr[data-table-list]:hover,
html.lcc-theme-dark table.flex.flex-col tbody tr.group:focus-within {
    background-color: var(--lcc-dark-hover) !important;
}
html.lcc-theme-dark table.flex.flex-col tbody tr :is(th,td),
html.lcc-theme-dark table.flex.flex-col tbody tr:hover :is(th,td) {
    color: var(--lcc-dark-text) !important;
}
/* Table row badges explicitly opt in to a raised surface and readable text. */
html.lcc-theme-dark table.flex.flex-col :is(.bg-lilac-900,.bg-gray-200,.bg-purple-lighter) {
    background-color: var(--lcc-dark-elevated) !important;
    color: var(--lcc-dark-text) !important;
}

/* Form/dialog/popup surfaces need an opaque backdrop when opened above a page. */
html.lcc-theme-dark :is(
    .dropdown-menu, .el-popover, .el-tooltip__popper, .el-select-dropdown,
    .el-picker-panel, .el-dialog, .el-message-box, .el-notification,
    .el-popover__title, .el-table__header-wrapper, .el-table__body-wrapper
) {
    background-color: var(--lcc-dark-surface) !important;
    color: var(--lcc-dark-text) !important;
    border-color: var(--lcc-dark-border) !important;
}
html.lcc-theme-dark :is(input:not([type="checkbox"]):not([type="radio"]), textarea, select,
    .el-input__inner, .el-textarea__inner) {
    color: var(--lcc-dark-text) !important;
    background-color: var(--lcc-dark-surface) !important;
    border-color: var(--lcc-dark-border) !important;
}
html.lcc-theme-dark :is(input,textarea)::placeholder {
    color: #cbd5e1 !important;
}

/* Existing green 'New' actions are too bright for white text. Keep semantics. */
html.lcc-theme-dark :is(a.bg-green,button.bg-green,[role="button"].bg-green) {
    background-color: #32632c !important;
    color: #ffffff !important;
}
html.lcc-theme-dark :is(a.bg-green,button.bg-green,[role="button"].bg-green):hover {
    background-color: #285324 !important;
    color: #ffffff !important;
}
/* Light links are for text links, not colored actions or status chips. */
html.lcc-theme-dark a:not(.bg-green):not(.bg-purple):not(.bg-red):not(.bg-orange):not(.bg-blue) {
    color: var(--lcc-dark-link);
}
html.lcc-theme-dark .group:hover :is(.group-hover\:text-purple-700, .group-hover\:text-purple) {
    color: var(--lcc-dark-text) !important;
}

/* ApexCharts renders its own HTML tooltip and SVG labels with light defaults.
   Override only presentation; never change financial data or series colors. */
html.lcc-theme-dark .apexcharts-tooltip,
html.lcc-theme-dark .apexcharts-tooltip.apexcharts-theme-light,
html.lcc-theme-dark .apexcharts-xaxistooltip,
html.lcc-theme-dark .apexcharts-yaxistooltip {
    background: var(--lcc-dark-surface) !important;
    color: var(--lcc-dark-text) !important;
    border: 1px solid var(--lcc-dark-border) !important;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .34) !important;
}
html.lcc-theme-dark .apexcharts-tooltip-title {
    background: var(--lcc-dark-elevated) !important;
    color: var(--lcc-dark-text) !important;
    border-bottom: 1px solid var(--lcc-dark-border) !important;
}
html.lcc-theme-dark .apexcharts-tooltip-text,
html.lcc-theme-dark .apexcharts-tooltip-text-y-label,
html.lcc-theme-dark .apexcharts-tooltip-text-y-value,
html.lcc-theme-dark .apexcharts-tooltip-text-z-label,
html.lcc-theme-dark .apexcharts-tooltip-text-z-value,
html.lcc-theme-dark .apexcharts-xaxistooltip-text,
html.lcc-theme-dark .apexcharts-yaxistooltip-text {
    color: var(--lcc-dark-text) !important;
}
html.lcc-theme-dark .apexcharts-legend-text {
    color: var(--lcc-dark-muted) !important;
}
html.lcc-theme-dark .apexcharts-xaxis-label,
html.lcc-theme-dark .apexcharts-yaxis-label,
html.lcc-theme-dark .apexcharts-text {
    fill: var(--lcc-dark-muted) !important;
}
html.lcc-theme-dark .apexcharts-gridline,
html.lcc-theme-dark .apexcharts-xaxis-tick,
html.lcc-theme-dark .apexcharts-yaxis-tick {
    stroke: var(--lcc-dark-border) !important;
}
html.lcc-theme-dark .apexcharts-xaxistooltip-bottom:before,
html.lcc-theme-dark .apexcharts-xaxistooltip-bottom:after {
    border-bottom-color: var(--lcc-dark-surface) !important;
}
html.lcc-theme-dark .apexcharts-xaxistooltip-top:before,
html.lcc-theme-dark .apexcharts-xaxistooltip-top:after {
    border-top-color: var(--lcc-dark-surface) !important;
}

/* Loading-menu skeleton is rendered BEFORE the real #sidenav-main.
   Scope these rules to the exact Core component, not the authenticated menu. */
html.lcc-theme-dark [x-ref="loadingMenu"] .menu-list {
    background-color: var(--lcc-dark-surface) !important;
}
html.lcc-theme-dark [x-ref="loadingMenu"] > .menu-scroll {
    background-color: var(--lcc-dark-page) !important;
}
html.lcc-theme-dark [x-ref="loadingMenu"] .animate-pulse.bg-gray-300 {
    background-color: var(--lcc-dark-border) !important;
}
html.lcc-theme-dark [x-ref="loadingMenu"] {
    color: var(--lcc-dark-muted) !important;
}
</style>
<script>
(function () {
    'use strict';
    const initialMode = @json($mode);
    const savedMode = ['system', 'light', 'dark'].includes(initialMode) ? initialMode : 'system';
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    function apply(mode) {
        const dark = mode === 'dark' || (mode === 'system' && media.matches);
        document.documentElement.classList.toggle('lcc-theme-dark', dark);
        document.documentElement.dataset.lccThemeMode = mode;
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        return dark;
    }
    window.LccTheme = {
        mode: savedMode,
        apply,
        async save(mode) {
            if (!['system', 'light', 'dark'].includes(mode)) throw new Error('Invalid color mode.');
            const token = document.querySelector('meta[name=csrf-token]')?.content || @json(csrf_token());
            const response = await fetch(@json(route('theme.preference.save')), {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                body: JSON.stringify({mode})
            });
            if (!response.ok) throw new Error('Unable to save color mode (' + response.status + ').');
            this.mode = mode;
            apply(mode);
            document.dispatchEvent(new CustomEvent('lcc-theme-change', {detail: {mode}}));
        }
    };
    apply(savedMode);
    @if ($logo)
    function applyCompanyMark() {
        const companyIcon = document.querySelector('#sidenav-main [data-dropdown-toggle="dropdown-menu-company"] img');
        if (companyIcon) {
            companyIcon.src = @json($logo);
            companyIcon.alt = @json($options['brand_name'] ?: 'Company');
            companyIcon.classList.add('lcc-theme-company-logo');
        }
        // The compact mobile company selector uses the same brand identity.
        const mobileIcon = document.querySelector('.js-hamburger-menu')?.nextElementSibling?.querySelector('img[src*="akaunting-logo"]');
        if (mobileIcon) {
            mobileIcon.src = @json($logo);
            mobileIcon.alt = @json($options['brand_name'] ?: 'Company');
            mobileIcon.classList.add('lcc-theme-company-logo');
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyCompanyMark, {once: true});
    } else {
        applyCompanyMark();
    }
    @endif
    @if ($options['support_url'])
    document.addEventListener('DOMContentLoaded', function () {
        const support = document.querySelector('#menu-support-icon')?.closest('a');
        if (support) {
            support.href = @json($options['support_url']);
            support.rel = 'noopener noreferrer';
            support.target = '_blank';
        }
    });
    @endif
    media.addEventListener?.('change', () => {
        if (window.LccTheme.mode === 'system') apply('system');
    });
})();
</script>

@if ($spinner)
<script id="lcc-theme-loading-script">
(function () {
    'use strict';
    const customGif = @json($spinner);
    const staticLogo = @json($spinnerFallback);
    const loadingLabel = @json(trans('general.loading'));
    const selectors = [
        '[x-ref="loadingContent"] img[src*="akaunting-loading.gif"]',
        '[x-ref="loadingAbsoluteContent"] img[src*="akaunting-loading.gif"]'
    ].join(',');
    function replaceLoaders() {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const source = reducedMotion && staticLogo ? staticLogo : customGif;
        document.querySelectorAll(selectors).forEach(function (img) {
            if (img.dataset.lccThemeSpinner === 'true') {
                return;
            }
            img.src = source;
            img.alt = loadingLabel;
            img.dataset.lccThemeSpinner = 'true';
            img.classList.add('lcc-theme-loading-image');
        });
    }
    if (document.readyState === 'loading') {
        // Cover overlays as soon as they are parsed, before DOMContentLoaded.
        // Observe only inserted nodes; replacing an img src does not re-trigger this.
        const observer = new MutationObserver(replaceLoaders);
        observer.observe(document.documentElement, {childList: true, subtree: true});
        document.addEventListener('DOMContentLoaded', function () {
            replaceLoaders();
            observer.disconnect();
        }, {once: true});
    } else {
        replaceLoaders();
    }
})();
</script>
@endif
