<x-atrium::card :title="__('showroom::showroom.widget_completeness')">
    @if ($rows === [])
        <p class="text-sm opacity-70">{{ __('showroom::showroom.no_completeness_yet') }}</p>
    @else
        <ul class="flex flex-col gap-3">
            @foreach ($rows as $row)
                <li data-slot="{{ $row['scope'] }}-{{ $row['locale'] }}">
                    <div class="flex items-center justify-between text-sm">
                        <a class="font-mono underline-offset-2 hover:underline"
                           href="{{ route('atrium.showroom.products.index', ['complete' => ['scope' => $row['scope'], 'locale' => $row['locale'], 'min' => 100]]) }}">
                            {{ $row['scope'] }} · {{ $row['locale'] }}
                        </a>
                        <span class="tabular-nums">
                            {{ $row['average'] }}%
                            <span class="opacity-70">— {{ __('showroom::showroom.fully_complete', ['complete' => $row['complete'], 'products' => $row['products']]) }}</span>
                        </span>
                    </div>
                    <x-atrium::progress class="mt-1" :value="$row['average']" />
                </li>
            @endforeach
        </ul>
    @endif
</x-atrium::card>
