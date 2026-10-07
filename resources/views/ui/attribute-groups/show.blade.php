@php($locale = app()->getLocale())

<x-atrium::layout :title="$group->label()">
    <x-atrium::page-header :title="$group->label()" :description="$group->code">
        <x-slot:actions>
            @keystoneCan('delete', $group)
                <form method="POST" action="{{ route('atrium.keystone.attribute-groups.destroy', $group) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-group" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        @keystoneCan('update', $group)
        <x-atrium::card data-testid="group-details-card" :title="__('keystone::keystone.details')">
            <form method="POST" action="{{ route('atrium.keystone.attribute-groups.update', $group) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                @method('PATCH')
                <x-atrium::form.input
                    :name="'labels['.$locale.']'"
                    :label="__('keystone::keystone.label_field', ['locale' => $locale])"
                    :value="$group->labels[$locale] ?? null"
                    wrapper="w-64" />
                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" :value="$group->sort_order" wrapper="w-28" />
                <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-group" />
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card :title="__('keystone::keystone.group_attributes')">
            @if ($group->groupedAttributes->isEmpty())
                <x-atrium::empty-state :title="__('keystone::keystone.no_group_attributes')" />
            @else
                <x-atrium::table compact>
                    <x-slot:head>
                        <x-atrium::table.row>
                            <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                            <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                            <x-atrium::table.cell heading>{{ __('keystone::keystone.type') }}</x-atrium::table.cell>
                        </x-atrium::table.row>
                    </x-slot:head>

                    @foreach ($group->groupedAttributes as $attribute)
                        <x-atrium::table.row>
                            <x-atrium::table.cell>
                                <a class="font-mono underline-offset-2 hover:underline"
                                   href="{{ route('atrium.keystone.attributes.show', $attribute) }}">{{ $attribute->code }}</a>
                            </x-atrium::table.cell>
                            <x-atrium::table.cell>{{ $attribute->label() }}</x-atrium::table.cell>
                            <x-atrium::table.cell><x-atrium::badge>{{ $attribute->type->value }}</x-atrium::badge></x-atrium::table.cell>
                        </x-atrium::table.row>
                    @endforeach
                </x-atrium::table>
            @endif
        </x-atrium::card>
    </div>
</x-atrium::layout>
