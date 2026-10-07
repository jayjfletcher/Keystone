@use(JayI\Keystone\Atrium\Badges)
@use(JayI\Keystone\Atrium\ScreenAccess)

@php($canUpdate = ScreenAccess::allows('update', $product))

<x-atrium::layout :title="$product->identifier">
    <x-atrium::page-header :title="$product->identifier" :description="$product->family?->label()">
        <x-slot:actions>
            <x-atrium::status-dot :variant="Badges::forEnabled($product->enabled)" :label="$product->enabled ? __('keystone::keystone.enabled') : __('keystone::keystone.disabled')" data-status="{{ $product->enabled ? 'enabled' : 'disabled' }}" data-testid="product-enabled" />

            @if ($product->parent)
                @keystoneCan('view', $product->parent)
                    <x-atrium::icon-button icon="arrow-left" :label="$product->parent->code" variant="outline" :href="route('atrium.keystone.product-models.show', $product->parent)" data-testid="parent-model" />
                @endkeystoneCan
            @endif

            @keystoneCan('delete', $product)
                <form method="POST" action="{{ route('atrium.keystone.products.destroy', $product) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-product" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        @include('keystone::ui.products.workflow')

        @include('keystone::ui.partials.slot-picker')

        {{-- Shown to those who may only view it, read-only and without save. --}}
        <form method="POST" action="{{ route('atrium.keystone.products.update', $product) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="add" value="{{ request('add') }}">

            <fieldset class="flex min-w-0 flex-col gap-5" @disabled(! $canUpdate)>

                <x-atrium::card :title="__('keystone::keystone.details')">
                    <div class="flex flex-wrap items-end gap-4">
                        @unless ($product->isVariant())
                            <x-atrium::form.select
                                name="family"
                                :label="__('keystone::keystone.family')"
                                :placeholder="__('keystone::keystone.no_family')"
                                :options="$families"
                                :selected="$product->family?->code"
                                wrapper="w-56" />
                        @endunless

                        @if ($product->isVariant())
                            <div class="text-sm">
                                <p class="font-medium">{{ __('keystone::keystone.inherited_owner') }}</p>
                                <p class="font-mono text-xs">{{ $product->effectiveOwner()?->code ?? __('keystone::keystone.none') }}</p>
                            </div>
                        @else
                            <x-atrium::form.input name="owner" :label="__('keystone::keystone.owner')" :hint="__('keystone::keystone.owner_hint')" :value="$product->owner?->code" wrapper="w-56" />
                        @endif

                        <input type="hidden" name="enabled" value="0">
                        <x-atrium::form.checkbox name="enabled" :label="__('keystone::keystone.enabled')" :checked="$product->enabled" wrapper="w-auto" />
                    </div>
                </x-atrium::card>

                <x-atrium::card :title="__('keystone::keystone.categories')">
                    @include('keystone::ui.partials.categories', [
                        'record' => $product,
                        'inheritedCategories' => $product->parent?->allCategories() ?? collect(),
                    ])
                </x-atrium::card>

                <x-atrium::card :title="__('keystone::keystone.values')">
                    @include('keystone::ui.partials.values', ['values' => $product->ownValues()])
                </x-atrium::card>

                @if ($canUpdate)
                    <div>
                        <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-product" />
                    </div>
                @endif
            </fieldset>
        </form>

        @if ($addable !== [] && $canUpdate)
            <x-atrium::card>
                <form method="GET" action="{{ route('atrium.keystone.products.show', $product) }}" class="flex flex-wrap items-end gap-3">
                    <x-atrium::form.select name="add" :label="__('keystone::keystone.add_attribute')" :placeholder="__('keystone::keystone.choose_attribute')" :options="$addable" wrapper="w-72" />
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add')" variant="outline" type="submit" />
                </form>
            </x-atrium::card>
        @endif

        @include('keystone::ui.partials.assets', [
            'own' => $product->assets,
            'inheritedAssets' => $product->parent?->allAssets() ?? collect(),
            'linkType' => 'product',
            'linkTarget' => $product->identifier,
        ])

        @include('keystone::ui.partials.associations', ['record' => $product, 'sourceKind' => 'product', 'sourceKey' => $product->identifier])

        @include('keystone::ui.partials.inherited')

        <x-atrium::audit-trail source="keystone" :subject="$product" />
    </div>
</x-atrium::layout>
