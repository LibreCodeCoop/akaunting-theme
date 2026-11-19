@php
    $logo = $options['logo'] ? asset('public/storage/' . $options['logo']) : '';
    $favicon = $options['favicon'] ? asset('public/storage/' . $options['favicon']) : '';
@endphp
@if ($favicon)
<link rel="icon" href="{{ $favicon }}" type="image/png" id="lcc-theme-favicon">
@endif
<style id="lcc-theme-brand">
:root {
    --lcc-theme-sidebar: {{ $options['sidebar_color'] }};
    --lcc-theme-menu: {{ $options['menu_color'] }};
    --lcc-theme-content: {{ $options['content_color'] }};
}
body:not(.lcc-theme-dark) .js-main-menu { background-color: var(--lcc-theme-sidebar); }
body:not(.lcc-theme-dark) .main-menu { background-color: var(--lcc-theme-menu); }
body:not(.lcc-theme-dark) #main-body { background-color: var(--lcc-theme-content); }
@if ($logo)
/* Add the company identity without erasing the upstream Akaunting logo. */
.js-main-menu .lcc-theme-company-logo { display: inline-block; width: 28px; height: 28px; object-fit: contain; margin-right: 8px; }
@endif
/* Dark mode follows the rendered core surfaces without mutating upstream styles. */
html.lcc-theme-dark { color-scheme: dark; }
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
@media (prefers-reduced-motion: no-preference) {
    html.lcc-theme-dark * { scrollbar-color: #4b5563 #111827; }
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
    document.addEventListener('DOMContentLoaded', function () {
        const icon = document.querySelector('#sidenav-main img[src*="akaunting-logo"]');
        if (!icon || document.querySelector('.lcc-theme-company-logo')) return;
        const brand = document.createElement('img');
        brand.src = @json($logo);
        brand.alt = @json($options['brand_name'] ?: 'Company');
        brand.className = 'lcc-theme-company-logo';
        icon.insertAdjacentElement('afterend', brand);
    });
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
