@use(RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.attribute_groups')">
    <x-atrium::page-header :title="__('keystone::keystone.attribute_groups')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', AttributeGroupModel::class)
        <x-atrium::card data-testid="new-group-card" :title="__('keystone::keystone.new_group')">
            <form method="POST" action="{{ route('atrium.keystone.attribute-groups.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" value="0" wrapper="w-28" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create_group')" variant="primary" type="submit" data-testid="create-group" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        @if ($groups->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_groups')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.attributes_count') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.sort_order') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($groups as $group)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.keystone.attribute-groups.show', $group) }}">{{ $group->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $group->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $group->grouped_attributes_count }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $group->sort_order }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$groups" />
        @endif
    </div>
</x-atrium::layout>
