<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.name') }}</x-slot>
    <x-slot name="content">
        <div class="bg-white rounded-lg p-6 shadow-sm max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold mb-2">{{ trans('theme::general.title') }}</h2>
            <p class="text-sm mb-6">{{ trans('theme::general.subtitle') }}</p>

            @if (session('success'))
                <p role="status" class="rounded-md p-3 mb-4 bg-green-100">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div role="alert" class="rounded-md p-3 mb-4 bg-red-100">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('theme.settings.save') }}" enctype="multipart/form-data" class="grid gap-4">
                @csrf
                <label class="grid gap-1">
                    <span>{{ trans('theme::general.brand_name') }}</span>
                    <input class="border rounded-md p-2" name="brand_name" maxlength="80" value="{{ old('brand_name', $options['brand_name']) }}">
                </label>
                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach (['sidebar_color' => trans('theme::general.sidebar_color'), 'menu_color' => trans('theme::general.menu_color'), 'content_color' => trans('theme::general.content_color')] as $key => $label)
                    <label class="grid gap-1">
                        <span>{{ $label }}</span>
                        <input type="color" class="border rounded-md w-full h-12" name="{{ $key }}" value="{{ old($key, $options[$key]) }}" required>
                    </label>
                    @endforeach
                </div>
                @foreach (['logo' => trans('theme::general.logo'), 'favicon' => trans('theme::general.favicon'), 'login_background' => trans('theme::general.login_background')] as $key => $label)
                <label class="grid gap-1">
                    <span>{{ $label }}</span>
                    <input type="file" name="{{ $key }}" accept="image/png,image/jpeg,image/webp">
                </label>
                @if ($options[$key])
                <p class="text-xs">{{ trans('theme::general.configured_image') }}</p>
                @endif
                @endforeach
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="login_slider" value="1" @checked(old('login_slider', $options['login_slider']))>
                    <span>{{ trans('theme::general.login_slider') }}</span>
                </label>
                @foreach (['support_url' => trans('theme::general.support_url'), 'documentation_url' => trans('theme::general.documentation_url')] as $key => $label)
                <label class="grid gap-1">
                    <span>{{ $label }}</span>
                    <input type="url" name="{{ $key }}" maxlength="400" pattern="https://.*" class="border rounded-md p-2" value="{{ old($key, $options[$key]) }}">
                </label>
                @endforeach
                <label class="grid gap-1">
                    <span>{{ trans('theme::general.footer_credit') }}</span>
                    <input name="footer_credit" maxlength="160" class="border rounded-md p-2" value="{{ old('footer_credit', $options['footer_credit']) }}" placeholder="{{ trans('theme::general.footer_placeholder') }}">
                </label>
                @can('update-settings-company')
                    <button class="bg-purple text-white rounded-md px-6 py-3 justify-self-start" type="submit">{{ trans('theme::general.save') }}</button>
                @endcan
            </form>
        </div>
    </x-slot>
</x-layouts.admin>
