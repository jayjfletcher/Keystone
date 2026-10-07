<x-atrium::layout :title="__('keystone::keystone.new_product')">
    <x-atrium::page-header :title="__('keystone::keystone.new_product')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        <x-atrium::card>
            <form method="POST" action="{{ route('atrium.keystone.products.store') }}" class="flex max-w-xl flex-col gap-4">
                @csrf

                <x-atrium::form.input name="identifier" :label="__('keystone::keystone.identifier')" :hint="__('keystone::keystone.identifier_hint')" required />

                <x-atrium::form.select
                    name="family"
                    :label="__('keystone::keystone.family')"
                    :placeholder="__('keystone::keystone.no_family')"
                    :options="$families"
                    :selected="old('family')" />

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create_product')" variant="primary" type="submit" data-testid="create-product" />
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
