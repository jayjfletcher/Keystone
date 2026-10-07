@php($locale = app()->getLocale())

<x-atrium::layout :title="$category->label()">
    <x-atrium::page-header :title="$category->label()" :description="$category->code">
        <x-slot:actions>
            @keystoneCan('viewAny', \JayI\Keystone\Domains\Product\Models\ProductModel::class)
                <x-atrium::icon-button icon="cube" :label="__('keystone::keystone.products')" variant="outline" :href="route('atrium.keystone.products.index', ['category' => $category->code])" data-testid="category-products" />
            @endkeystoneCan

            @keystoneCan('delete', $category)
                <form method="POST" action="{{ route('atrium.keystone.categories.destroy', $category) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-category" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        <nav aria-label="{{ __('keystone::keystone.chain') }}" class="flex flex-wrap items-center gap-1 text-sm" data-testid="category-chain">
            <a class="underline-offset-2 hover:underline" href="{{ route('atrium.keystone.categories.index') }}">{{ __('keystone::keystone.categories') }}</a>
            @foreach ($category->getRelation('chain') as $link)
                <span class="opacity-50">›</span>
                @if ($link->is($category))
                    <span class="font-medium">{{ $link->label() }}</span>
                @else
                    <a class="underline-offset-2 hover:underline" href="{{ route('atrium.keystone.categories.show', $link) }}">{{ $link->label() }}</a>
                @endif
            @endforeach
        </nav>

        <x-atrium::card :title="__('keystone::keystone.branch')">
            @if ($branch->isEmpty())
                <x-atrium::empty-state :title="__('keystone::keystone.no_subcategories')" />
            @else
                @include('keystone::ui.categories.branch', ['parentId' => $category->id, 'nested' => false])
            @endif

            @keystoneCan('create', \JayI\Keystone\Domains\Category\Models\CategoryModel::class)
            <form method="POST" action="{{ route('atrium.keystone.categories.store') }}" class="mt-4 flex flex-wrap items-start gap-3">
                @csrf
                <input type="hidden" name="parent" value="{{ $category->code }}">
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-48" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" value="0" wrapper="w-28" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add_child')" variant="primary" type="submit" data-testid="add-subcategory" />
                </x-atrium::form.actions>
            </form>
            @endkeystoneCan
        </x-atrium::card>

        @keystoneCan('update', $category)
        <x-atrium::card data-testid="category-details-card" :title="__('keystone::keystone.details')">
            <form method="POST" action="{{ route('atrium.keystone.categories.update', $category) }}" class="flex flex-wrap items-start gap-3">
                @csrf
                @method('PATCH')
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" :value="$category->labels[$locale] ?? null" wrapper="w-64" />
                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" :value="$category->sort_order" wrapper="w-28" />
                <x-atrium::form.input name="parent" :label="__('keystone::keystone.parent')" :hint="__('keystone::keystone.category_move_hint')" :value="$category->parent?->code" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-category" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <p class="text-sm text-on-surface dark:text-on-surface-dark">
            {{ __('keystone::keystone.filed_counts', ['products' => $category->products_count, 'models' => $category->product_models_count]) }}
        </p>

        <x-atrium::audit-trail source="keystone" :subject="$category" />
    </div>
</x-atrium::layout>
