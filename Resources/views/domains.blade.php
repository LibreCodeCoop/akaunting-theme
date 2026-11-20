<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.domains_title') }}</x-slot>
    <x-slot name="content">
        <div class="max-w-5xl mx-auto grid gap-5">
            <header>
                <p class="text-xs text-gray-500">{{ trans('theme::general.company_theme') }}</p>
                <h1 class="text-2xl font-semibold">{{ trans('theme::general.domains_title') }} — {{ $companyName }}</h1>
                <p class="text-sm text-gray-500">{{ trans('theme::general.domains_description') }}</p>
            </header>
            @if (session('success'))<p role="status" class="bg-green-100 rounded-lg p-3">{{ session('success') }}</p>@endif
            @if ($errors->any())
                <div role="alert" class="bg-red-100 rounded-lg p-3">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            @if ($baseHost === '')
                <p role="status" class="rounded-lg p-4 border border-orange-100 bg-yellow-100">
                    {{ trans('theme::general.configure_global_first') }}
                </p>
            @else
                <section class="rounded-lg bg-white border border-gray-200 p-5 grid gap-4">
                    <h2 class="text-lg font-semibold">{{ trans('theme::general.add_domain') }}</h2>
                    @can('update-settings-company')
                    <form method="POST" action="{{ route('theme.domains.store') }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <label class="grid gap-1 flex-1">
                            <span>{{ trans('theme::general.company_domain') }}</span>
                            <input class="border rounded-md p-2 w-full" type="text" name="host"
                                placeholder="empresa.{{ $baseHost }}" maxlength="253" required>
                        </label>
                        <button class="bg-purple text-white px-5 py-2 rounded-md" type="submit">{{ trans('theme::general.add_domain') }}</button>
                    </form>
                    @endcan
                    <p class="text-sm text-gray-500">{{ trans('theme::general.domains_one_label') }}</p>
                </section>
            @endif
            <section class="grid gap-3">
                @forelse ($domains as $domain)
                    <article class="rounded-lg bg-white border border-gray-200 p-5 grid gap-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="font-semibold">{{ $domain->host }}</h2>
                            <span class="text-sm">{{ $domain->status === 'active' ? trans('theme::general.domain_active') : trans('theme::general.domain_pending_status') }} @if ($domain->is_primary) · {{ trans('theme::general.domain_primary') }} @endif</span>
                        </div>
                        @if ($domain->status !== 'active')
                            <div class="rounded-md p-3 bg-gray-100 text-sm grid gap-1">
                                <p>{{ trans('theme::general.domain_dns_instruction') }}</p>
                                <code>TXT _akaunting-theme.{{ $domain->host }}</code>
                                <code>akaunting-theme-verification={{ $domain->verification_token }}</code>
                                <p>{{ trans('theme::general.domain_https_instruction') }}</p>
                            </div>
                        @else
                            <a class="text-purple underline text-sm" href="https://{{ $domain->host }}/login" target="_blank" rel="noopener noreferrer">{{ trans('theme::general.view_login') }}</a>
                        @endif
                        @can('update-settings-company')
                        <div class="flex gap-4 flex-wrap">
                            @if ($domain->status === 'pending')
                                <form method="POST" action="{{ route('theme.domains.verify', ['domain' => $domain->id]) }}">
                                    @csrf
                                    <button class="text-purple underline" type="submit">{{ trans('theme::general.verify_domain') }}</button>
                                </form>
                            @elseif (! $domain->is_primary)
                                <form method="POST" action="{{ route('theme.domains.primary', ['domain' => $domain->id]) }}">
                                    @csrf
                                    <button class="text-purple underline" type="submit">{{ trans('theme::general.make_primary') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('theme.domains.destroy', ['domain' => $domain->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 underline" type="submit">{{ trans('theme::general.remove_domain') }}</button>
                            </form>
                        </div>
                        @endcan
                    </article>
                @empty
                    <p class="text-sm text-gray-500">{{ trans('theme::general.no_domains') }}</p>
                @endforelse
            </section>
            <a href="{{ route('theme.settings.index') }}" class="text-purple underline">{{ trans('theme::general.back_to_theme') }}</a>
        </div>
    </x-slot>
</x-layouts.admin>
