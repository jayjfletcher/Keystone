@use(JayI\Keystone\Atrium\Badges)
@use(JayI\Keystone\Domains\Product\Models\ProductModel)

<x-atrium::layout :title="__('keystone::keystone.products')">
    <x-atrium::page-header :title="__('keystone::keystone.products')">
        <x-slot:actions>
            @keystoneCan('create', ProductModel::class)
                <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.new_product')" variant="primary" :href="route('atrium.keystone.products.create')" data-testid="new-product" />
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.products.index') }}" class="flex flex-wrap items-end gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-64" />

                <x-atrium::form.select
                    name="family"
                    :label="__('keystone::keystone.family')"
                    :placeholder="__('keystone::keystone.all_families')"
                    :options="$families"
                    :selected="$filters['family'] ?? null"
                    wrapper="w-48" />

                <x-atrium::form.input name="category" :label="__('keystone::keystone.category')" :value="$filters['category'] ?? null" wrapper="w-44" />

                <x-atrium::form.input name="owner" :label="__('keystone::keystone.owner')" :value="$filters['owner'] ?? null" wrapper="w-44" />

                <x-atrium::form.select
                    name="status"
                    :label="__('keystone::keystone.workflow')"
                    :placeholder="__('keystone::keystone.all_statuses')"
                    :options="collect(\JayI\Keystone\Domains\Product\Enums\ProductStatus::cases())->mapWithKeys(fn ($status) => [$status->value => __('keystone::keystone.status_'.$status->value)])"
                    :selected="$filters['status'] ?? null"
                    wrapper="w-40" />

                <x-atrium::form.select
                    name="enabled"
                    :label="__('keystone::keystone.status')"
                    :placeholder="__('keystone::keystone.all_statuses')"
                    :options="['1' => __('keystone::keystone.enabled'), '0' => __('keystone::keystone.disabled')]"
                    :selected="isset($filters['enabled']) ? (string) (int) $filters['enabled'] : null"
                    wrapper="w-40" />

                <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" data-testid="filter-products" />
                <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.products.index')" />
            </form>
        </x-atrium::card>

        @if ($error)
            <x-atrium::alert variant="danger">{{ $error }}</x-atrium::alert>
        @elseif ($products->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_products')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.identifier') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.family') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.parent') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.status') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.workflow') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.updated') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($products as $product)
                    @php($labelCode = $product->family?->labelAttribute?->code)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.keystone.products.show', $product) }}">{{ $product->identifier }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $labelCode ? ($product->value($labelCode) ?? __('keystone::keystone.none')) : __('keystone::keystone.none') }}</x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $product->family?->label() ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $product->parent?->code ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            <x-atrium::status-dot :variant="Badges::forEnabled($product->enabled)" :label="$product->enabled ? __('keystone::keystone.enabled') : __('keystone::keystone.disabled')" data-status="{{ $product->enabled ? 'enabled' : 'disabled' }}" />
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>
                            <span class="flex items-center gap-2">
                                <x-atrium::status-dot :variant="Badges::forStatus($product->status)" :label="__('keystone::keystone.status_'.$product->status->value)" data-status="{{ $product->status->value }}" />
                                @if ($product->published_version)<x-atrium::badge>v{{ $product->published_version }}</x-atrium::badge>@endif
                            </span>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $product->updated_at?->diffForHumans() }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$products" />
        @endif

        <x-atrium::audit-trail source="keystone" />
    </div>
</x-atrium::layout>
