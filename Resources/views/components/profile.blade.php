<div id="lcc-theme-profile-fields" hidden>
    <label for="lcc-theme-profile-mode" style="display:block;font-weight:600;margin-bottom:0.5rem;">
        {{ trans('theme::general.mode') }}
    </label>
    <select id="lcc-theme-profile-mode" class="rounded-md border p-2" aria-label="{{ trans('theme::general.mode') }}">
        <option value="system" @selected($mode === 'system')>{{ trans('theme::general.system') }}</option>
        <option value="light" @selected($mode === 'light')>{{ trans('theme::general.light') }}</option>
        <option value="dark" @selected($mode === 'dark')>{{ trans('theme::general.dark') }}</option>
    </select>
    <span id="lcc-theme-profile-status" aria-live="polite" style="margin-left:0.6rem;"></span>
</div>
<script>
(function () {
    function mount() {
        // No core template override: extend only the currently edited user's Preferences section.
        const form = document.getElementById('user');
        const fields = document.getElementById('lcc-theme-profile-fields');
        if (!form || !fields) return;
        const sections = form.querySelectorAll('.mb-14');
        const target = Array.from(sections).find(section =>
            section.textContent.includes(@json(trans('general.preferences')))
        );
        // Fail closed rather than insert into an unrelated profile panel.
        if (!target) return;
        const body = target.querySelector('.grid') || target;
        if (!body.contains(fields)) {
            body.appendChild(fields);
            fields.hidden = false;
        }
        const select = fields.querySelector('select');
        if (select.dataset.lccBound) return;
        select.dataset.lccBound = 'true';
        select.addEventListener('change', async function () {
            const previous = window.LccTheme?.mode || 'system';
            const status = document.getElementById('lcc-theme-profile-status');
            select.disabled = true;
            status.textContent = '';
            try {
                await window.LccTheme.save(select.value);
            } catch (error) {
                select.value = previous;
                status.textContent = 'Unable to save preference.';
            } finally {
                select.disabled = false;
            }
        });
        document.addEventListener('lcc-theme-change', (event) => {
            select.value = event.detail.mode;
        });
    }
    document.addEventListener('DOMContentLoaded', mount);
    if (document.readyState !== 'loading') mount();
})();
</script>
