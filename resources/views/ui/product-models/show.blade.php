@use(JayI\Keystone\Atrium\ScreenAccess)
@use(JayI\Keystone\Domains\Product\Models\ProductModel)
@use(JayI\Keystone\Domains\ProductModel\Models\ProductModelModel)

@php($canUpdate = ScreenAccess::allows('update', $model))

<x-atrium::layout :title="$model->code">
    <x-atrium::page-header :title="$model->code" :description="$model->familyVariant->code.' · '.__('keystone::keystone.level', ['level' => $model->level()])">
        <x-slot:actions>
            @if ($model->parent)
                @keystoneCan('view', $model->parent)
                    <x-atrium::icon-button icon="arrow-left" :label="$model->parent->code" variant="outline" :href="route('atrium.keystone.product-models.show', $model->parent)" data-testid="parent-model" />
                @endkeystoneCan
            @endif

            @keystoneCan('delete', $model)
                <form method="POST" action="{{ route('atrium.keystone.product-models.destroy', $model) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-product-model" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        @include('keystone::ui.partials.slot-picker')

        {{-- Shown to those who may only view it, read-only and without save. --}}
        <form method="POST" action="{{ route('atrium.keystone.product-models.update', $model) }}">
            @csrf
            @method('PATCH')

            <fieldset class="flex min-w-0 flex-col gap-5" @disabled(! $canUpdate)>

                @if ($model->level() === 0)
                    <x-atrium::card :title="__('keystone::keystone.details')">
                        <x-atrium::form.input name="owner" :label="__('keystone::keystone.owner')" :hint="__('keystone::keystone.owner_hint')" :value="$model->owner?->code" wrapper="w-56" />
                    </x-atrium::card>
                @endif

                <x-atrium::card :title="__('keystone::keystone.categories')">
                    @include('keystone::ui.partials.categories', [
                        'record' => $model,
                        'inheritedCategories' => $model->parent?->allCategories() ?? collect(),
                    ])
                </x-atrium::card>

                <x-atrium::card :title="__('keystone::keystone.values')">
                    @include('keystone::ui.partials.values', ['values' => $model->ownValues()])
                </x-atrium::card>

                @if ($canUpdate)
                    <div>
                        <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-product-model" />
                    </div>
                @endif
            </fieldset>
        </form>

        @include('keystone::ui.partials.assets', [
            'own' => $model->assets,
            'inheritedAssets' => $model->parent?->allAssets() ?? collect(),
            'linkType' => 'product_model',
            'linkTarget' => $model->code,
        ])

        @include('keystone::ui.partials.associations', ['record' => $model, 'sourceKind' => 'product_model', 'sourceKey' => $model->code])

        @include('keystone::ui.partials.inherited')

        @if ($model->holdsProducts())
            <x-atrium::card :title="__('keystone::keystone.variant_products')">
                @if ($model->products->isNotEmpty())
                    <ul class="mb-4 flex flex-wrap gap-2">
                        @foreach ($model->products as $product)
                            <li><a class="font-mono text-sm underline-offset-2 hover:underline" href="{{ route('atrium.keystone.products.show', $product) }}">{{ $product->identifier }}</a></li>
                        @endforeach
                    </ul>
                @endif

                @keystoneCan('create', ProductModel::class)
                <form method="POST" action="{{ route('atrium.keystone.products.store') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="parent" value="{{ $model->code }}">
                    <x-atrium::form.input name="identifier" :label="__('keystone::keystone.identifier')" required wrapper="w-56" />
                    @include('keystone::ui.partials.axes', ['axes' => $model->familyVariant->axesAt($model->familyVariant->levels)])
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add_variant')" variant="primary" type="submit" data-testid="create-variant" />
                </form>
                @endkeystoneCan
            </x-atrium::card>
        @else
            <x-atrium::card :title="__('keystone::keystone.sub_models')">
                @if ($model->children->isNotEmpty())
                    <ul class="mb-4 flex flex-wrap gap-2">
                        @foreach ($model->children as $child)
                            <li><a class="font-mono text-sm underline-offset-2 hover:underline" href="{{ route('atrium.keystone.product-models.show', $child) }}">{{ $child->code }}</a></li>
                        @endforeach
                    </ul>
                @endif

                @keystoneCan('create', ProductModelModel::class)
                <form method="POST" action="{{ route('atrium.keystone.product-models.store') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="parent" value="{{ $model->code }}">
                    <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-56" />
                    @include('keystone::ui.partials.axes', ['axes' => $model->familyVariant->axesAt(1)])
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add_sub_model')" variant="primary" type="submit" data-testid="create-sub-model" />
                </form>
                @endkeystoneCan
            </x-atrium::card>
        @endif

        <x-atrium::audit-trail source="keystone" :subject="$model" />
    </div>
</x-atrium::layout>
