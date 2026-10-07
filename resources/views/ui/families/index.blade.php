@use(JayI\Keystone\Domains\Family\Models\FamilyModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.families')">
    <x-atrium::page-header :title="__('keystone::keystone.families')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', FamilyModel::class)
        <x-atrium::card data-testid="new-family-card" :title="__('keystone::keystone.new_family')">
            <form method="POST" action="{{ route('atrium.keystone.families.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" value="0" wrapper="w-28" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create_family')" variant="primary" type="submit" data-testid="create-family" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.families.index') }}" class="flex flex-wrap items-start gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" data-testid="filter-families" />
                    <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.families.index')" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>

        @if ($families->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_families')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.attributes_count') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_attribute') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($families as $family)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.keystone.families.show', $family) }}">{{ $family->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $family->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $family->family_attributes_count }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $family->labelAttribute?->code ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$families" />
        @endif
    </div>
</x-atrium::layout>
