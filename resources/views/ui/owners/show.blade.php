@php($locale = app()->getLocale())

<x-atrium::layout :title="$owner->label()">
    <x-atrium::page-header :title="$owner->label()" :description="$owner->code.' · '.$owner->type->code">
        <x-slot:actions>
            @keystoneCan('viewAny', \JayI\Keystone\Domains\Product\Models\ProductModel::class)
                <x-atrium::icon-button icon="cube" :label="__('keystone::keystone.products')" variant="outline" :href="route('atrium.keystone.products.index', ['owner' => $owner->code])" data-testid="owner-products" />
            @endkeystoneCan

            @keystoneCan('delete', $owner)
                <form method="POST" action="{{ route('atrium.keystone.owners.destroy', $owner) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-owner" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        <nav aria-label="{{ __('keystone::keystone.chain') }}" class="flex flex-wrap items-center gap-1 text-sm" data-testid="owner-chain">
            @foreach ($owner->getRelation('chain') as $link)
                @unless ($loop->first)<span class="opacity-50">›</span>@endunless
                @if ($link->is($owner))
                    <span class="font-medium">{{ $link->label() }}</span>
                @else
                    <a class="underline-offset-2 hover:underline" href="{{ route('atrium.keystone.owners.show', $link) }}">{{ $link->label() }}</a>
                @endif
                <span class="font-mono text-xs opacity-60">{{ $link->type->code }}</span>
            @endforeach
        </nav>

        @keystoneCan('update', $owner)
        <x-atrium::card data-testid="owner-details-card" :title="__('keystone::keystone.details')">
            <form method="POST" action="{{ route('atrium.keystone.owners.update', $owner) }}" class="flex flex-wrap items-start gap-3">
                @csrf
                @method('PATCH')
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" :value="$owner->labels[$locale] ?? null" wrapper="w-64" />
                <x-atrium::form.input name="parent" :label="__('keystone::keystone.parent')" :hint="__('keystone::keystone.move_hint')" :value="$owner->parent?->code" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-owner" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card :title="__('keystone::keystone.children')">
            @if ($owner->children->isNotEmpty())
                <ul class="mb-4 flex flex-wrap gap-3">
                    @foreach ($owner->children as $child)
                        <li class="text-sm">
                            <a class="font-mono underline-offset-2 hover:underline" href="{{ route('atrium.keystone.owners.show', $child) }}">{{ $child->code }}</a>
                            <span class="text-xs opacity-60">{{ $child->type->code }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @keystoneCan('create', \JayI\Keystone\Domains\Owner\Models\OwnerModel::class)
            <form method="POST" action="{{ route('atrium.keystone.owners.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <input type="hidden" name="parent" value="{{ $owner->code }}">
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-48" />
                <x-atrium::form.select name="type" :label="__('keystone::keystone.type')" :options="$types" required wrapper="w-44" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add_child')" variant="primary" type="submit" data-testid="add-child-owner" />
                </x-atrium::form.actions>
            </form>
            @endkeystoneCan
        </x-atrium::card>

        @include('keystone::ui.partials.assets', [
            'own' => $owner->assets,
            'inheritedAssets' => collect(),
            'linkType' => 'owner',
            'linkTarget' => $owner->code,
        ])

        <p class="text-sm text-on-surface dark:text-on-surface-dark">
            {{ __('keystone::keystone.owned_counts', ['products' => $owner->products_count, 'models' => $owner->product_models_count]) }}
        </p>

        <x-atrium::audit-trail source="keystone" :subject="$owner" />
    </div>
</x-atrium::layout>
