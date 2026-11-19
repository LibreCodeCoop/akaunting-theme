// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const view = fs.readFileSync(path.join(__dirname, '../Resources/views/components/head.blade.php'), 'utf8');
const raw = view.split('<script id="lcc-theme-loading-script">')[1]?.split('</script>')[0];
assert.ok(raw, 'Custom loading image script not rendered by module');
const gif = '/public/storage/akaunting-theme/1/company.gif';
const fallback = '/public/storage/akaunting-theme/1/company.png';

function run({reducedMotion = false, loading = false} = {}) {
    const code = raw
        .replace('@json($spinner)', JSON.stringify(gif))
        .replace('@json($spinnerFallback)', JSON.stringify(fallback))
        .replace("@json(trans('general.loading'))", JSON.stringify('Carregando'));
    const events = {};
    let query = '';
    const images = Array.from({length: 2}, () => ({
        src: '/public/img/akaunting-loading.gif',
        alt: 'Akaunting',
        dataset: {},
        classes: new Set(),
        classList: {add(name) {this._last = name;}},
    }));
    const unrelated = {
        src: '/public/img/akaunting-loading.gif',
        alt: 'Unaffected',
        dataset: {},
    };
    const document = {
        readyState: loading ? 'loading' : 'complete',
        documentElement: {},
        querySelectorAll(selector) {
            query = selector;
            return images;
        },
        addEventListener(type, callback) {events[type] = callback;},
    };
    const window = {matchMedia: () => ({matches: reducedMotion})};
    class MutationObserver {
        constructor(callback) {this.callback = callback;}
        observe(_target, options) {
            assert.equal(options.childList, true);
            events.inserted = this.callback;
        }
        disconnect() {events.disconnected = true;}
    }
    vm.runInNewContext(code, {window, document, MutationObserver});
    return {images, unrelated, events, getQuery: () => query};
}

test('custom spinner replaces only known loading overlays with the per-company GIF', () => {
    const result = run();
    assert.ok(result.getQuery().includes('[x-ref="loadingContent"]'));
    assert.ok(result.getQuery().includes('[x-ref="loadingAbsoluteContent"]'));
    for (const image of result.images) {
        assert.equal(image.src, gif);
        assert.equal(image.alt, 'Carregando');
        assert.equal(image.dataset.lccThemeSpinner, 'true');
    }
    assert.equal(result.unrelated.alt, 'Unaffected');
});

test('reduced-motion users receive the static company icon', () => {
    const result = run({reducedMotion: true});
    result.images.forEach(image => assert.equal(image.src, fallback));
});

test('DOM loading state waits for document availability', () => {
    const result = run({loading: true});
    assert.equal(result.images[0].src, '/public/img/akaunting-loading.gif');
    result.events.inserted();
    assert.equal(result.images[0].src, gif, 'Early DOM insertion must apply branding');
    result.events.DOMContentLoaded();
    assert.equal(result.events.disconnected, true, 'Observer must be released after parsing');
    assert.equal(result.images[0].src, gif);
});
