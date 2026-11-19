<div class="py-2" id="lcc-theme-toggle-container">
    <button id="lcc-theme-toggle" type="button" class="flex items-center gap-2 p-2 text-sm rounded-md hover:bg-gray-100"
            aria-label="{{ trans('theme::general.toggle') }}"
            title="{{ trans('theme::general.toggle') }}">
        <span class="material-icons-outlined" aria-hidden="true">brightness_6</span>
        <span>{{ trans('theme::general.mode') }}</span>
    </button>
</div>
<script>
(function () {
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('#lcc-theme-toggle');
        if (!button || !window.LccTheme || button.disabled) return;
        button.disabled = true;
        // A single click switches the effective appearance. System mode remains available in the profile.
        const dark = document.documentElement.classList.contains('lcc-theme-dark');
        try {
            await window.LccTheme.save(dark ? 'light' : 'dark');
        } catch (error) {
            console.error('Theme preference could not be saved.');
        } finally {
            button.disabled = false;
        }
    });
})();
</script>
