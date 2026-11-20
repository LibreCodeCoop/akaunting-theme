<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.global_login_title') }}</x-slot>
    <x-slot name="content">
        <div class="max-w-5xl mx-auto grid gap-5">
            <header>
                <p class="text-xs text-gray-500">{{ trans('theme::general.instance_scope') }}</p>
                <h1 class="text-2xl font-semibold">{{ trans('theme::general.global_login_title') }}</h1>
                <p class="text-sm text-gray-500 mt-2">{{ trans('theme::general.global_login_description') }}</p>
            </header>
            @if (session('success'))
                <p class="rounded-lg bg-green-100 p-3" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 rounded-lg p-3" role="alert">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @unless ($canEdit)
                <p role="note" class="border border-gray-200 rounded-md p-4 text-sm">
                    {{ trans('theme::general.global_login_readonly') }}
                </p>
            @endunless
            <form method="POST" enctype="multipart/form-data" action="{{ route('theme.login.global.save') }}" class="grid gap-5">
                @csrf
                <fieldset class="grid gap-5 min-w-0" @disabled(!$canEdit)>
                <section class="bg-white rounded-lg p-5 border border-gray-200 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.global_host_title') }}</h2>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.global_host_help') }}</span>
                        <input type="text" name="base_host" class="border rounded-md p-2" maxlength="253" required
                            placeholder="example.org" value="{{ old('base_host', $options['base_host']) }}">
                    </label>
                    <p class="text-xs text-gray-500">{{ trans('theme::general.host_warning') }}</p>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.alias_zone_label') }}</span>
                        <input type="text" name="alias_zone" class="border rounded-md p-2" maxlength="253"
                            placeholder="example.org" value="{{ old('alias_zone', $options['alias_zone']) }}">
                    </label>
                    <p class="text-xs text-gray-500">{{ trans('theme::general.alias_zone_help') }}</p>
                </section>
                <section class="bg-white rounded-lg p-5 border border-gray-200 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.identity_section') }}</h2>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.brand_name') }}</span>
                        <input name="brand_name" class="border rounded-md p-2" maxlength="80" value="{{ old('brand_name', $options['brand_name']) }}">
                    </label>
                    <label class="grid gap-1 max-w-xs">
                        <span>{{ trans('theme::general.login_background_opacity') }}</span>
                        <input type="number" min="0" max="100" name="background_opacity" class="border rounded-md p-2"
                            value="{{ old('background_opacity', $options['background_opacity']) }}">
                    </label>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach (['logo' => trans('theme::general.logo'), 'favicon' => trans('theme::general.favicon'), 'login_background' => trans('theme::general.login_background')] as $key => $label)
                            <article class="grid gap-3 border border-gray-200 rounded-lg p-4">
                                <h3 class="font-semibold">{{ $label }}</h3>
                                @if ($options[$key])
                                    <img src="{{ asset('public/storage/' . $options[$key]) }}" alt="{{ $label }}"
                                        class="object-contain rounded-lg bg-gray-100 max-h-44 w-full" loading="lazy">
                                    <a class="text-sm underline text-purple" target="_blank" rel="noopener noreferrer"
                                        href="{{ asset('public/storage/' . $options[$key]) }}">{{ trans('theme::general.view_current') }}</a>
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="remove_{{ $key }}" value="1">
                                        <span>{{ trans('theme::general.remove_image') }}</span>
                                    </label>
                                @else
                                    <p class="text-sm text-gray-500">{{ trans('theme::general.no_image') }}</p>
                                @endif
                                <input type="file" name="{{ $key }}" accept="image/png,image/jpeg,image/webp">
                            </article>
                        @endforeach
                    </div>
                </section>
                <section class="bg-white rounded-lg p-5 border border-gray-200 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.footer_section') }}</h2>
                    <p class="text-sm text-gray-500">{{ trans('theme::general.footer_preserve_credit') }}</p>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.footer_text_label') }}</span>
                        <input type="text" name="footer_text" maxlength="180"
                            class="border rounded-md p-2" value="{{ old('footer_text', $options['footer_text']) }}">
                    </label>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.footer_url_label') }}</span>
                        <input type="url" name="footer_url" maxlength="400"
                            class="border rounded-md p-2" value="{{ old('footer_url', $options['footer_url']) }}" placeholder="https://example.org">
                    </label>
                </section>
                <section class="bg-white rounded-lg p-5 border border-gray-200 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.carousel_title') }}</h2>
                    <p class="text-sm text-gray-500">{{ trans('theme::general.carousel_help') }}</p>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="login_slider" value="1" @checked(old('login_slider', $options['login_slider']))>
                        <span>{{ trans('theme::general.login_slider') }}</span>
                    </label>
                    @include('theme::components.global-slide-editor')
                </section>
                </fieldset>
                @if ($canEdit)
                    <button type="submit" class="bg-purple text-white rounded-md px-5 py-3 justify-self-start">
                        {{ trans('theme::general.save') }}
                    </button>
                @endif
            </form>
        </div>
    </x-slot>
</x-layouts.admin>
