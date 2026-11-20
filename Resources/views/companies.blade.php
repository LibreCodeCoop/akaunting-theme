<x-layouts.admin>
    <x-slot name="title">{{ trans('theme::general.companies_title') }}</x-slot>
    <x-slot name="content">
        <div class="max-w-5xl mx-auto grid gap-5">
            <header>
                <h1 class="text-2xl font-semibold">{{ trans('theme::general.companies_title') }}</h1>
                <p class="text-sm text-gray-500 mt-2">{{ trans('theme::general.companies_help') }}</p>
            </header>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($companies as $item)
                <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm flex flex-col gap-3">
                    <h2 class="text-lg font-semibold">{{ $item->name }}</h2>
                    <span class="text-xs text-gray-500">ID {{ $item->id }}</span>
                    <p class="text-sm">{{ in_array($item->id, $configured) ? trans('theme::general.configured') : trans('theme::general.not_configured') }}</p>
                    <a class="text-purple underline self-start" href="{{ route('theme.settings.index', ['company_id' => $item->id]) }}">
                        {{ in_array($item->id, $configured) ? trans('theme::general.edit_theme') : trans('theme::general.create_theme') }}
                    </a>
                </article>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-layouts.admin>
