@php($locale = app()->getLocale())

<x-atrium::layout :title="__('showroom::showroom.new_attribute')">
    <x-atrium::page-header :title="__('showroom::showroom.new_attribute')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        <x-atrium::card>
            <form method="POST" action="{{ route('atrium.showroom.attributes.store') }}" class="flex max-w-xl flex-col gap-4">
                @csrf

                <x-atrium::form.input name="code" :label="__('showroom::showroom.code')" :hint="__('showroom::showroom.code_hint')" required />

                <x-atrium::form.select
                    name="type"
                    :label="__('showroom::showroom.type')"
                    :hint="__('showroom::showroom.type_hint')"
                    :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->value])"
                    :selected="old('type')"
                    required />

                <x-atrium::form.select
                    name="group"
                    :label="__('showroom::showroom.group')"
                    :placeholder="__('showroom::showroom.no_group')"
                    :options="$groups"
                    :selected="old('group')" />

                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('showroom::showroom.label_field', ['locale' => $locale])" />

                @include('showroom::ui.partials.flags', ['attribute' => null])

                <x-atrium::form.textarea name="settings" :label="__('showroom::showroom.settings')" :hint="__('showroom::showroom.settings_hint')" rows="3" class="font-mono" />

                <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('showroom::showroom.sort_order')" value="0" wrapper="w-32" />

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('showroom::showroom.create_attribute')" variant="primary" type="submit" data-testid="create-attribute" />
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
