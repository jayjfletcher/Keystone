@use(JayI\Keystone\Domains\Product\Models\ProductModel)

<x-atrium::card :title="__('keystone::keystone.widget_recent_changes')">
    @if ($versions->isEmpty())
        <p class="text-sm opacity-70">{{ __('keystone::keystone.no_changes') }}</p>
    @else
        <ul class="flex flex-col divide-y divide-outline dark:divide-outline-dark">
            @foreach ($versions as $version)
                <li class="flex items-center justify-between gap-3 py-2 text-sm" data-version="{{ $version->version }}">
                    <span class="flex items-center gap-2">
                        @if ($version->versionable instanceof ProductModel)
                            <a class="font-mono underline-offset-2 hover:underline" href="{{ route('atrium.keystone.products.show', $version->versionable) }}">{{ $version->versionable->identifier }}</a>
                        @endif
                        <span class="font-mono opacity-70">v{{ $version->version }}</span>
                        <x-atrium::badge>{{ $version->action }}</x-atrium::badge>
                    </span>
                    <span class="text-right opacity-70">
                        @if ($version->author_id)
                            {{ __('keystone::keystone.by', ['author' => $version->author?->getAttribute('name') ?? $version->author_id]) }} ·
                        @endif
                        {{ $version->created_at?->diffForHumans() }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</x-atrium::card>
