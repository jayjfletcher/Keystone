@php($locale = app()->getLocale())

<x-atrium::layout :title="$type->label()">
    <x-atrium::page-header :title="$type->label()" :description="$type->code">
        <x-slot:actions>
            @keystoneCan('viewAny', \RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel::class)
                <x-atrium::icon-button icon="building-storefront" :label="__('keystone::keystone.owners').' ('.$type->owners_count.')'" variant="outline" :href="route('atrium.keystone.owners.index', ['type' => $type->code])" data-testid="type-owners" />
            @endkeystoneCan

            @keystoneCan('delete', $type)
                <form method="POST" action="{{ route('atrium.keystone.owner-types.destroy', $type) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-owner-type" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        @keystoneCan('update', $type)
        <x-atrium::card data-testid="owner-type-details-card" :title="__('keystone::keystone.details')">
            <form method="POST" action="{{ route('atrium.keystone.owner-types.update', $type) }}" class="flex flex-col gap-4">
                @csrf
                @method('PATCH')
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" :value="$type->labels[$locale] ?? null" wrapper="w-64" />

                @include('keystone::ui.owner-types.rules', ['all' => array_values(array_diff($all, [])), 'type' => $type])

                <div>
                    <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-owner-type" />
                </div>
            </form>
        </x-atrium::card>
        @endkeystoneCan
    </div>
</x-atrium::layout>
