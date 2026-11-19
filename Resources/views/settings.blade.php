<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.name') }}</x-slot>
    <x-slot name="content">
        <div class="bg-white rounded-lg p-6 shadow-sm max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold mb-2">{{ trans('theme::general.description') }}</h2>
            <p class="text-sm mb-6">Akaunting retains its original identity. Company branding is displayed in addition to the upstream attribution.</p>

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
                    <span>Brand name</span>
                    <input class="border rounded-md p-2" name="brand_name" maxlength="80" value="{{ old('brand_name', $options['brand_name']) }}">
                </label>
                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach (['sidebar_color' => 'Sidebar color', 'menu_color' => 'Menu color', 'content_color' => 'Content color'] as $key => $label)
                    <label class="grid gap-1">
                        <span>{{ $label }}</span>
                        <input type="color" class="border rounded-md w-full h-12" name="{{ $key }}" value="{{ old($key, $options[$key]) }}" required>
                    </label>
                    @endforeach
                </div>
                @foreach (['logo' => 'Logo (PNG/JPEG/WebP)', 'favicon' => 'Favicon (PNG/JPEG/WebP)', 'login_background' => 'Login background (PNG/JPEG/WebP)'] as $key => $label)
                <label class="grid gap-1">
                    <span>{{ $label }}</span>
                    <input type="file" name="{{ $key }}" accept="image/png,image/jpeg,image/webp">
                </label>
                @if ($options[$key])
                <p class="text-xs">An image is currently configured for {{ $label }}. Upload a new one to replace it.</p>
                @endif
                @endforeach
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="login_slider" value="1" @checked(old('login_slider', $options['login_slider']))>
                    <span>Show login slider</span>
                </label>
                @foreach (['support_url' => 'Support URL (HTTPS)', 'documentation_url' => 'Documentation URL (HTTPS)'] as $key => $label)
                <label class="grid gap-1">
                    <span>{{ $label }}</span>
                    <input type="url" name="{{ $key }}" maxlength="400" pattern="https://.*" class="border rounded-md p-2" value="{{ old($key, $options[$key]) }}">
                </label>
                @endforeach
                <label class="grid gap-1">
                    <span>Additional footer credit</span>
                    <input name="footer_credit" maxlength="160" class="border rounded-md p-2" value="{{ old('footer_credit', $options['footer_credit']) }}" placeholder="Integrations and support by LibreCode">
                </label>
                @can('update-settings-company')
                    <button class="bg-purple text-white rounded-md px-6 py-3 justify-self-start" type="submit">Save appearance</button>
                @endcan
            </form>
        </div>
    </x-slot>
</x-layouts.admin>
