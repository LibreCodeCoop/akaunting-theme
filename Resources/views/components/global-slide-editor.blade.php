{{-- SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
     SPDX-License-Identifier: AGPL-3.0-or-later --}}
@php
    $savedSlides = array_values($options['slides']);
    $visibleSlides = $savedSlides;
    $oldTitles = old('slide_title', []);
    $oldCaptions = old('slide_caption', []);
    if (is_array($oldTitles)) {
        foreach ($oldTitles as $key => $title) {
            if (! is_numeric($key) || (int) $key < count($savedSlides) ||
                (int) $key > 99 || count($visibleSlides) >= \Modules\Theme\Domain\LoginSlides::LIMIT) {
                continue;
            }
            $visibleSlides[(int) $key] = [
                'title' => (string) $title,
                'caption' => (string) ($oldCaptions[$key] ?? ''),
                'image' => '',
            ];
        }
    }
    ksort($visibleSlides, SORT_NUMERIC);
@endphp
<div id="lcc-global-slide-editor" data-slide-editor
    data-max-slides="{{ \Modules\Theme\Domain\LoginSlides::LIMIT }}">
    <div data-slide-list class="grid sm:grid-cols-2 gap-4">
        @foreach ($visibleSlides as $i => $slide)
            <article data-slide-card data-slide-index="{{ $i }}"
                data-slide-saved="{{ $i < count($savedSlides) ? '1' : '0' }}"
                class="grid gap-3 rounded-lg border border-gray-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="font-semibold">{{ trans('theme::general.carousel_slide') }} {{ $loop->iteration }}</h3>
                    @if ($canEdit)
                        <button type="button" data-slide-remove
                            class="text-red-600 underline text-sm">{{ trans('theme::general.delete_slide') }}</button>
                    @endif
                </div>
                <input type="hidden" data-slide-delete-flag name="delete_slide[{{ $i }}]"
                    value="{{ old('delete_slide.' . $i, '0') }}">
                <div data-slide-deleted class="text-sm rounded-md p-3 bg-gray-100" hidden>
                    {{ trans('theme::general.slide_marked_for_removal') }}
                    <button type="button" data-slide-undo class="text-purple underline">
                        {{ trans('theme::general.undo_delete') }}
                    </button>
                </div>
                <div data-slide-fields class="grid gap-3">
                    @if (! empty($slide['image']))
                        <div class="rounded-md border border-gray-200 p-2 bg-gray-50">
                            <img src="{{ asset('public/storage/' . $slide['image']) }}"
                                alt="{{ $slide['title'] ?? trans('theme::general.carousel_slide') }}"
                                class="w-full max-h-36 object-contain" loading="lazy">
                        </div>
                    @endif
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.slide_title') }}</span>
                        <input type="text" name="slide_title[{{ $i }}]" maxlength="120"
                            value="{{ old('slide_title.' . $i, $slide['title'] ?? '') }}"
                            class="border rounded-md p-2">
                    </label>
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.slide_caption') }}</span>
                        <textarea name="slide_caption[{{ $i }}]" maxlength="280"
                            class="border rounded-md p-2">{{ old('slide_caption.' . $i, $slide['caption'] ?? '') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.replace_image') }}</span>
                        <input type="file" name="slide_image[{{ $i }}]"
                            accept="image/png,image/jpeg,image/webp" class="w-full">
                    </label>
                </div>
            </article>
        @endforeach
    </div>
    <p data-slide-empty class="text-sm text-gray-500 mt-3" @if ($visibleSlides !== []) hidden @endif>{{ trans('theme::general.slides_empty') }}</p>
    @if ($canEdit)
        <div class="flex flex-wrap items-center gap-3 mt-4">
            <button type="button" data-slide-add class="rounded-lg border border-gray-300 px-4 py-2 font-semibold">
                {{ trans('theme::general.add_slide') }}
            </button>
            <span class="text-sm text-gray-500">{{ trans('theme::general.slides_limit') }}</span>
        </div>
        <template data-slide-template>
            <article data-slide-card data-slide-index="__INDEX__" data-slide-saved="0"
                class="grid gap-3 rounded-lg border border-gray-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="font-semibold">{{ trans('theme::general.carousel_slide') }} __NUMBER__</h3>
                    <button type="button" data-slide-remove class="text-red-600 underline text-sm">
                        {{ trans('theme::general.delete_slide') }}
                    </button>
                </div>
                <div data-slide-fields class="grid gap-3">
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.slide_title') }}</span>
                        <input type="text" name="slide_title[__INDEX__]" maxlength="120"
                            class="border rounded-md p-2">
                    </label>
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.slide_caption') }}</span>
                        <textarea name="slide_caption[__INDEX__]" maxlength="280"
                            class="border rounded-md p-2"></textarea>
                    </label>
                    <label class="grid gap-1 text-sm">
                        <span>{{ trans('theme::general.replace_image') }}</span>
                        <input type="file" name="slide_image[__INDEX__]"
                            accept="image/png,image/jpeg,image/webp" class="w-full">
                    </label>
                </div>
            </article>
        </template>
    @endif
</div>
@if ($canEdit)
    <script id="lcc-global-slide-editor-script">
        (() => {
            const editor = document.querySelector('[data-slide-editor]');
            if (!editor) return;
            const list = editor.querySelector('[data-slide-list]');
            const template = editor.querySelector('[data-slide-template]');
            const add = editor.querySelector('[data-slide-add]');
            const empty = editor.querySelector('[data-slide-empty]');
            const limit = Number(editor.dataset.maxSlides);
            let nextIndex = Math.max(-1, ...Array.from(list.querySelectorAll('[data-slide-card]'))
                .map(card => Number(card.dataset.slideIndex))) + 1;

            const cards = () => Array.from(list.querySelectorAll('[data-slide-card]'));
            const active = () => cards().filter(card => card.dataset.slideDeleted !== '1');
            const refresh = () => {
                empty.hidden = active().length !== 0;
                if (add) add.disabled = active().length >= limit || nextIndex > 99;
                active().forEach((card, index) => {
                    const title = card.querySelector('h3');
                    if (title) title.textContent = @json(trans('theme::general.carousel_slide')) + ' ' + (index + 1);
                });
            };

            list.addEventListener('click', event => {
                const remove = event.target.closest('[data-slide-remove]');
                const undo = event.target.closest('[data-slide-undo]');
                if (!remove && !undo) return;
                const card = event.target.closest('[data-slide-card]');
                if (!card) return;
                if (remove && card.dataset.slideSaved === '0') {
                    card.remove();
                } else {
                    const isDeleted = !!remove;
                    card.dataset.slideDeleted = isDeleted ? '1' : '0';
                    card.querySelector('[data-slide-delete-flag]').value = isDeleted ? '1' : '0';
                    card.querySelector('[data-slide-fields]').hidden = isDeleted;
                    card.querySelector('[data-slide-fields]').style.display = isDeleted ? 'none' : '';
                    card.querySelector('[data-slide-deleted]').hidden = !isDeleted;
                    if (remove) remove.hidden = true;
                    else card.querySelector('[data-slide-remove]').hidden = false;
                }
                refresh();
            });

            add?.addEventListener('click', () => {
                if (active().length >= limit || nextIndex > 99) return;
                const index = nextIndex++;
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name], [data-slide-index]').forEach(element => {
                    if (element.hasAttribute('name')) {
                        element.setAttribute('name', element.getAttribute('name').replace('__INDEX__', String(index)));
                    }
                    if (element.hasAttribute('data-slide-index')) {
                        element.setAttribute('data-slide-index', String(index));
                    }
                });
                clone.querySelector('h3').textContent =
                    @json(trans('theme::general.carousel_slide')) + ' ' + (active().length + 1);
                list.appendChild(clone);
                refresh();
            });

            cards().forEach(card => {
                const deleted = card.querySelector('[data-slide-delete-flag]')?.value === '1';
                if (!deleted) return;
                card.dataset.slideDeleted = '1';
                card.querySelector('[data-slide-fields]').hidden = true;
                card.querySelector('[data-slide-fields]').style.display = 'none';
                card.querySelector('[data-slide-deleted]').hidden = false;
                const remove = card.querySelector('[data-slide-remove]');
                if (remove) remove.hidden = true;
            });
            refresh();
        })();
    </script>
@endif
