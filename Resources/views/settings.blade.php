<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.name') }} — {{ $company->name }}</x-slot>
    <x-slot name="content">
        <div class="max-w-5xl mx-auto grid gap-5">
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ trans('theme::general.company_theme') }}</p>
                    <h1 class="text-2xl font-semibold">{{ $company->name }} <span class="text-sm text-gray-500">(ID {{ $company->id }})</span></h1>
                    <p class="text-sm text-gray-500 mt-2">{{ trans('theme::general.company_scope_warning') }}</p>
                </div>
                @if ($companyCount > 1)
                    <a href="{{ route('theme.companies.index') }}" class="rounded-lg border border-gray-200 px-4 py-2">{{ trans('theme::general.change_company') }}</a>
                @endif
            </header>
            @if (session('success'))
                <p role="status" class="rounded-md p-3 bg-green-100">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div role="alert" class="rounded-md p-3 bg-red-100">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('theme.settings.save') }}" enctype="multipart/form-data" class="grid gap-5">
                @csrf
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 grid gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ trans('theme::general.identity_section') }}</h2>
                        <p class="text-sm text-gray-500">{{ trans('theme::general.identity_help') }}</p>
                    </div>
                    <label class="grid gap-1">
                        <span>{{ trans('theme::general.brand_name') }}</span>
                        <input class="border rounded-md p-2" name="brand_name" maxlength="80" value="{{ old('brand_name', $options['brand_name']) }}">
                    </label>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach (['logo' => trans('theme::general.logo'), 'favicon' => trans('theme::general.favicon')] as $key => $label)
                            <article class="border border-gray-200 rounded-lg p-4 grid gap-3">
                                <h3 class="font-semibold">{{ $label }}</h3>
                                @if ($options[$key])
                                <div class="rounded-lg border border-gray-200 bg-gray-100 p-4 min-h-28 flex items-center justify-center">
                                    <img src="{{ asset('public/storage/' . $options[$key]) }}" alt="{{ $label }}" class="max-h-28 max-w-full object-contain" loading="lazy">
                                </div>
                                <a href="{{ asset('public/storage/' . $options[$key]) }}" class="text-sm text-purple underline" target="_blank" rel="noopener noreferrer">{{ trans('theme::general.view_current') }}</a>
                                @else
                                <p class="text-sm text-gray-500">{{ trans('theme::general.no_image') }}</p>
                                @endif
                                <label class="grid gap-1 text-sm">
                                    <span>{{ trans('theme::general.replace_image') }}</span>
                                    <input type="file" name="{{ $key }}" accept="image/png,image/jpeg,image/webp" class="w-full">
                                </label>
                                @if ($options[$key])
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="remove_{{ $key }}" value="1">
                                    <span>{{ trans('theme::general.remove_image') }}</span>
                                </label>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 grid gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ trans('theme::general.appearance_section') }}</h2>
                        <p class="text-sm text-gray-500">{{ trans('theme::general.appearance_help') }}</p>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach (['sidebar_color' => trans('theme::general.sidebar_color'), 'menu_color' => trans('theme::general.menu_color'), 'content_color' => trans('theme::general.content_color')] as $key => $label)
                        <label class="grid gap-1"><span>{{ $label }}</span>
                            <input type="color" class="border rounded-md w-full h-12" name="{{ $key }}" value="{{ old($key, $options[$key]) }}" required>
                        </label>
                        @endforeach
                    </div>
                    <article class="border border-gray-200 rounded-lg p-4 grid gap-2">
                        <h3 class="font-semibold">{{ trans('theme::general.loading_spinner') }}</h3>
                        @if ($options['loading_spinner'])
                            <div class="rounded-lg border bg-gray-100 p-4 self-start">
                                <img src="{{ asset('public/storage/' . $options['loading_spinner']) }}" alt="{{ trans('theme::general.loading_spinner') }}" class="w-28 h-28 object-contain">
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ trans('theme::general.no_image') }}</p>
                        @endif
                        <input type="file" name="loading_spinner" accept="image/gif">
                        @if ($options['loading_spinner'])
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_loading_spinner" value="1"><span>{{ trans('theme::general.spinner_reset') }}</span></label>
                        @endif
                    </article>
                </section>
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 grid gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ trans('theme::general.login_section') }}</h2>
                        <p class="text-sm text-gray-500">{{ trans('theme::general.login_help') }}</p>
                    </div>
                    <article class="border border-gray-200 rounded-lg p-4 grid gap-3">
                        <h3 class="font-semibold">{{ trans('theme::general.login_background') }}</h3>
                        @if ($options['login_background'])
                            <div class="relative rounded-lg overflow-hidden border border-gray-200" style="min-height:12rem;background-image:url('{{ asset('public/storage/' . $options['login_background']) }}');background-size:cover;background-position:center">
                                <span class="inline-block bg-white text-black text-xs px-3 py-2 m-3 rounded-md">{{ trans('theme::general.login_preview') }}</span>
                            </div>
                            <a href="{{ asset('public/storage/' . $options['login_background']) }}" target="_blank" rel="noopener noreferrer" class="text-purple underline text-sm">{{ trans('theme::general.view_current') }}</a>
                        @else
                            <p class="text-sm text-gray-500">{{ trans('theme::general.no_image') }}</p>
                        @endif
                        @if ($hasActiveDomain)
                        <label class="grid gap-1 text-sm"><span>{{ trans('theme::general.replace_image') }}</span>
                            <input type="file" name="login_background" accept="image/png,image/jpeg,image/webp">
                        </label>
                        @endif
                        @if ($options['login_background'])
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_login_background" value="1"><span>{{ trans('theme::general.remove_image') }}</span></label>
                        @endif
                    </article>
                    @if ($hasActiveDomain)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="login_inherit_slider" value="1"
                                @checked(old('login_inherit_slider', $options['login_inherit_slider']))>
                            <span>{{ trans('theme::general.inherit_carousel') }}</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="login_slider" value="1"
                                @checked(old('login_slider', $options['login_slider']))>
                            <span>{{ trans('theme::general.login_slider') }}</span>
                        </label>
                        <h3 class="font-semibold">{{ trans('theme::general.carousel_title') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('theme::general.carousel_company_help') }}</p>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @for ($i = 0; $i < 6; $i++)
                            <article class="grid gap-2 p-3 border border-gray-200 rounded-md">
                                <h4 class="font-semibold">{{ trans('theme::general.carousel_slide') }} {{ $i + 1 }}</h4>
                                @if (! empty($options['login_slides'][$i]['image']))
                                    <img src="{{ asset('public/storage/' . $options['login_slides'][$i]['image']) }}"
                                        class="w-full h-24 object-contain bg-gray-100"
                                        alt="{{ $options['login_slides'][$i]['title'] ?? '' }}" loading="lazy">
                                    <label class="text-sm flex gap-2">
                                        <input type="checkbox" name="remove_slide_{{ $i }}" value="1">
                                        <span>{{ trans('theme::general.remove_image') }}</span>
                                    </label>
                                @endif
                                <label class="grid gap-1 text-sm"><span>{{ trans('theme::general.slide_title') }}</span>
                                    <input class="border rounded-md p-2" maxlength="120"
                                        name="slide_title[{{ $i }}]"
                                        value="{{ old('slide_title.' . $i, $options['login_slides'][$i]['title'] ?? '') }}">
                                </label>
                                <label class="grid gap-1 text-sm"><span>{{ trans('theme::general.slide_caption') }}</span>
                                    <textarea class="border rounded-md p-2" maxlength="280"
                                        name="slide_caption[{{ $i }}]">{{ old('slide_caption.' . $i, $options['login_slides'][$i]['caption'] ?? '') }}</textarea>
                                </label>
                                <input type="file" accept="image/png,image/jpeg,image/webp" name="slide_image_{{ $i }}">
                            </article>
                            @endfor
                        </div>
                    @else
                        <p role="note" class="rounded-md bg-gray-100 p-4 text-sm">
                            {{ trans('theme::general.login_locked_until_verified') }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-500">{{ trans('theme::general.login_domain_warning') }}</p>
                    <a href="{{ route('theme.domains.index') }}" class="text-purple underline text-sm">{{ trans('theme::general.manage_domains') }}</a>
                </section>
                <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.contact_section') }}</h2>
                    @foreach (['support_url' => trans('theme::general.support_url'), 'documentation_url' => trans('theme::general.documentation_url')] as $key => $label)
                    <label class="grid gap-1"><span>{{ $label }}</span>
                        <input type="url" name="{{ $key }}" maxlength="400" pattern="https://.*" class="border rounded-md p-2" value="{{ old($key, $options[$key]) }}">
                    </label>
                    @endforeach
                    <label class="grid gap-1"><span>{{ trans('theme::general.footer_credit') }}</span>
                        <input name="footer_credit" maxlength="160" class="border rounded-md p-2" value="{{ old('footer_credit', $options['footer_credit']) }}" placeholder="{{ trans('theme::general.footer_placeholder') }}">
                    </label>
                </section>
                @can('update-settings-company')
                    <button class="bg-purple text-white rounded-md px-6 py-3 justify-self-start" type="submit">{{ trans('theme::general.save') }}</button>
                @endcan
            </form>
        </div>
    </x-slot>
</x-layouts.admin>
