@use(JayI\Keystone\Domains\Association\Models\AssociationTypeModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.association_types')">
    <x-atrium::page-header :title="__('keystone::keystone.association_types')" :description="__('keystone::keystone.association_types_description')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', AssociationTypeModel::class)
        <x-atrium::card data-testid="new-association-type-card" :title="__('keystone::keystone.new_association_type')">
            <form method="POST" action="{{ route('atrium.keystone.association-types.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.actions>
                    <input type="hidden" name="is_two_way" value="0">
                    <x-atrium::form.checkbox name="is_two_way" :label="__('keystone::keystone.two_way')" wrapper="h-9 w-auto justify-center" />
                    <input type="hidden" name="is_quantified" value="0">
                    <x-atrium::form.checkbox name="is_quantified" :label="__('keystone::keystone.quantified')" wrapper="h-9 w-auto justify-center" />
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-association-type" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        @if ($types->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_association_types')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.flags') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.associations') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading></x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($types as $type)
                    <x-atrium::table.row>
                        <x-atrium::table.cell class="font-mono">{{ $type->code }}</x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $type->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @if ($type->is_two_way)<x-atrium::badge>{{ __('keystone::keystone.two_way') }}</x-atrium::badge>@endif
                            @if ($type->is_quantified)<x-atrium::badge>{{ __('keystone::keystone.quantified') }}</x-atrium::badge>@endif
                        </x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $type->associations_count }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @keystoneCan('delete', $type)
                                <form method="POST" action="{{ route('atrium.keystone.association-types.destroy', $type) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="ghost" size="sm" type="submit" data-testid="delete-association-type" />
                                </form>
                            @endkeystoneCan
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        @endif
    </div>
</x-atrium::layout>
