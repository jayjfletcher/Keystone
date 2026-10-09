@use(RefactorCircus\Keystone\Domains\Category\Models\CategoryModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.categories')">
    <x-atrium::page-header :title="__('keystone::keystone.categories')" :description="__('keystone::keystone.categories_description')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', CategoryModel::class)
        <x-atrium::card data-testid="new-tree-card" :title="__('keystone::keystone.new_tree')">
            <form method="POST" action="{{ route('atrium.keystone.categories.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-tree" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        @if ($trees->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_trees')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.label_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.children') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($trees as $tree)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline" href="{{ route('atrium.keystone.categories.show', $tree) }}">{{ $tree->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $tree->label() }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="tabular-nums">{{ $tree->children_count }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$trees" />
        @endif
    </div>
</x-atrium::layout>
