@use(JayI\Keystone\Domains\Attribute\Models\AttributeModel)

<x-atrium::layout :title="__('keystone::keystone.attributes')">
    <x-atrium::page-header :title="__('keystone::keystone.attributes')">
        <x-slot:actions>
            @keystoneCan('create', AttributeModel::class)
                <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.new_attribute')" variant="primary" :href="route('atrium.keystone.attributes.create')" data-testid="new-attribute" />
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        {{-- Type options come from the enum itself, so a new case can never
             drift out of the filter list. --}}
        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.attributes.index') }}" class="flex flex-wrap items-end gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-56" />

                <x-atrium::form.select
                    name="type"
                    :label="__('keystone::keystone.type')"
                    :placeholder="__('keystone::keystone.all_types')"
                    :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->value])"
                    :selected="$filters['type'] ?? null"
                    wrapper="w-44" />

                <x-atrium::form.select
                    name="group"
                    :label="__('keystone::keystone.group')"
                    :placeholder="__('keystone::keystone.all_groups')"
                    :options="$groups"
                    :selected="$filters['group'] ?? null"
                    wrapper="w-48" />

                <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" data-testid="filter-attributes" />
                <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.attributes.index')" />
            </form>
        </x-atrium::card>

        @if ($catalogAttributes->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_attributes')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.type') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.group') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.flags') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($catalogAttributes as $attribute)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.keystone.attributes.show', $attribute) }}">{{ $attribute->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $attribute->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell><x-atrium::badge>{{ $attribute->type->value }}</x-atrium::badge></x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $attribute->group?->label() ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @if ($attribute->is_unique)<x-atrium::badge>{{ __('keystone::keystone.unique') }}</x-atrium::badge>@endif
                            @if ($attribute->is_localizable)<x-atrium::badge>{{ __('keystone::keystone.localizable') }}</x-atrium::badge>@endif
                            @if ($attribute->is_scopable)<x-atrium::badge>{{ __('keystone::keystone.scopable') }}</x-atrium::badge>@endif
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$catalogAttributes" />
        @endif
    </div>
</x-atrium::layout>
