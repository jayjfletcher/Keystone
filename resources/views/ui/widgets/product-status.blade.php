@use(RefactorCircus\Showroom\Atrium\Badges)
@use(RefactorCircus\Showroom\Domains\Product\Enums\ProductStatus)

<x-atrium::card :title="__('showroom::showroom.widget_product_status')">
    <div class="flex flex-wrap gap-2">
        @foreach ($counts as $status => $count)
            <a class="flex items-center gap-2 rounded-radius border border-outline px-3 py-2 transition hover:bg-surface-alt dark:border-outline-dark dark:hover:bg-surface-dark-alt"
               href="{{ route('atrium.showroom.products.index', ['status' => $status]) }}" data-status="{{ $status }}">
                <x-atrium::status-dot :variant="Badges::forStatus(ProductStatus::from($status))" :label="__('showroom::showroom.status_'.$status)" />
                <span class="text-sm font-semibold tabular-nums">{{ $count }}</span>
            </a>
        @endforeach

        <a class="flex items-center gap-2 rounded-radius border border-outline px-3 py-2 transition hover:bg-surface-alt dark:border-outline-dark dark:hover:bg-surface-dark-alt"
           href="{{ route('atrium.showroom.products.index', ['published' => 1]) }}" data-status="published">
            <x-atrium::status-dot :variant="Badges::forPublished()" :label="__('showroom::showroom.live')" />
            <span class="text-sm font-semibold tabular-nums">{{ $published }}</span>
        </a>
    </div>
</x-atrium::card>
