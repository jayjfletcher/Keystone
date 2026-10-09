@use(RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.owners')">
    <x-atrium::page-header :title="__('keystone::keystone.owners')">
        <x-slot:actions>
            @keystoneCan('viewAny', \RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel::class)
                <x-atrium::icon-button icon="identification" :label="__('keystone::keystone.owner_types')" variant="outline" :href="route('atrium.keystone.owner-types.index')" data-testid="owner-types" />
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', OwnerModel::class)
        <x-atrium::card data-testid="new-owner-card" :title="__('keystone::keystone.new_owner')">
            @if ($types === [])
                <x-atrium::empty-state :title="__('keystone::keystone.no_owner_types')" />
            @else
                <form method="POST" action="{{ route('atrium.keystone.owners.store') }}" class="flex flex-wrap items-start gap-3">
                    @csrf
                    <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-48" />
                    <x-atrium::form.select name="type" :label="__('keystone::keystone.type')" :options="$types" required wrapper="w-44" />
                    <x-atrium::form.input name="parent" :label="__('keystone::keystone.parent')" :hint="__('keystone::keystone.parent_owner_hint')" wrapper="w-48" />
                    <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                    <x-atrium::form.actions>
                        <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-owner" />
                    </x-atrium::form.actions>
                </form>
            @endif
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.owners.index') }}" class="flex flex-wrap items-start gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-56" />
                <x-atrium::form.select name="type" :label="__('keystone::keystone.type')" :placeholder="__('keystone::keystone.all_types')" :options="$types" :selected="$filters['type'] ?? null" wrapper="w-44" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" />
                    <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.owners.index')" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>

        @if ($owners->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_owners')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.type') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.parent') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.children') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($owners as $owner)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline" href="{{ route('atrium.keystone.owners.show', $owner) }}">{{ $owner->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $owner->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell><x-atrium::badge>{{ $owner->type->code }}</x-atrium::badge></x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $owner->parent?->code ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $owner->children_count }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$owners" />
        @endif
    </div>
</x-atrium::layout>
