<x-atrium::card :title="__('keystone::keystone.widget_review_queue')">
    @if ($products->isEmpty())
        <p class="text-sm opacity-70">{{ __('keystone::keystone.nothing_in_review') }}</p>
    @else
        <p class="mb-2 text-sm opacity-70">
            <a class="underline-offset-2 hover:underline" href="{{ route('atrium.keystone.products.index', ['status' => 'in_review']) }}">
                {{ __('keystone::keystone.waiting_review', ['count' => $waiting]) }}
            </a>
        </p>
        <ul class="flex flex-col divide-y divide-outline dark:divide-outline-dark">
            @foreach ($products as $product)
                <li class="flex items-center justify-between py-2 text-sm" data-product="{{ $product->identifier }}">
                    <a class="font-mono underline-offset-2 hover:underline" href="{{ route('atrium.keystone.products.show', $product) }}">{{ $product->identifier }}</a>
                    <span class="opacity-70">{{ $product->updated_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-atrium::card>
