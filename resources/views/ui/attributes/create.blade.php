@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.new_attribute')">
    <x-atrium::page-header :title="__('keystone::keystone.new_attribute')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        <x-atrium::card>
            <form method="POST" action="{{ route('atrium.keystone.attributes.store') }}" class="flex max-w-xl flex-col gap-4">
                @csrf

                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required />

                <x-atrium::form.select
                    name="type"
                    :label="__('keystone::keystone.type')"
                    :hint="__('keystone::keystone.type_hint')"
                    :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->value])"
                    :selected="old('type')"
                    required />

                <x-atrium::form.select
                    name="group"
                    :label="__('keystone::keystone.group')"
                    :placeholder="__('keystone::keystone.no_group')"
                    :options="$groups"
                    :selected="old('group')" />

                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" />

                @include('keystone::ui.partials.flags', ['attribute' => null])

                <x-atrium::form.textarea name="settings" :label="__('keystone::keystone.settings')" :hint="__('keystone::keystone.settings_hint')" rows="3" class="font-mono" />

                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" value="0" wrapper="w-32" />

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create_attribute')" variant="primary" type="submit" data-testid="create-attribute" />
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
