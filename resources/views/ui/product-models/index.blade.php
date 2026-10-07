@use(JayI\Keystone\Domains\ProductModel\Models\ProductModelModel)

<x-atrium::layout :title="__('keystone::keystone.product_models')">
    <x-atrium::page-header :title="__('keystone::keystone.product_models')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', ProductModelModel::class)
        <x-atrium::card :title="__('keystone::keystone.new_product_model')" data-testid="new-product-model-card">
            <form method="POST" action="{{ route('atrium.keystone.product-models.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-56" />
                <x-atrium::form.select name="family_variant" :label="__('keystone::keystone.family_variant')" :placeholder="__('keystone::keystone.choose_family_variant')" :options="$variants" required wrapper="w-64" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-product-model" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.product-models.index') }}" class="flex flex-wrap items-start gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-56" />
                <x-atrium::form.select name="family_variant" :label="__('keystone::keystone.family_variant')" :placeholder="__('keystone::keystone.all_family_variants')" :options="$variants" :selected="$filters['family_variant'] ?? null" wrapper="w-64" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" />
                    <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.product-models.index')" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>

        @if ($models->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_product_models')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.family_variant') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.updated') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($models as $model)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline" href="{{ route('atrium.keystone.product-models.show', $model) }}">{{ $model->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $model->familyVariant->code }}</x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $model->updated_at?->diffForHumans() }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$models" />
        @endif
    </div>
</x-atrium::layout>
