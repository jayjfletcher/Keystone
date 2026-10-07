@use(JayI\Keystone\Domains\Owner\Models\OwnerTypeModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.owner_types')">
    <x-atrium::page-header :title="__('keystone::keystone.owner_types')" :description="__('keystone::keystone.owner_types_description')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', OwnerTypeModel::class)
        <x-atrium::card data-testid="new-owner-type-card" :title="__('keystone::keystone.new_owner_type')">
            <form method="POST" action="{{ route('atrium.keystone.owner-types.store') }}" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-wrap items-start gap-3">
                    <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                    <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                </div>

                @include('keystone::ui.owner-types.rules', ['type' => null])

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-owner-type" />
                </div>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        @if ($types->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_owner_types')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.parent_types') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.owners') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($types as $type)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline" href="{{ route('atrium.keystone.owner-types.show', $type) }}">{{ $type->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $type->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">
                            {{ $type->restricts_parents ? ($type->parentTypes->pluck('code')->join(', ') ?: __('keystone::keystone.none')) : __('keystone::keystone.any') }}
                        </x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $type->owners_count }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$types" />
        @endif
    </div>
</x-atrium::layout>
