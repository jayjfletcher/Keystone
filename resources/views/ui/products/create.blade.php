<x-atrium::layout :title="__('showroom::showroom.new_product')">
    <x-atrium::page-header :title="__('showroom::showroom.new_product')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        <x-atrium::card>
            <form method="POST" action="{{ route('atrium.showroom.products.store') }}" class="flex max-w-xl flex-col gap-4">
                @csrf

                <x-atrium::form.input name="identifier" :label="__('showroom::showroom.identifier')" :hint="__('showroom::showroom.identifier_hint')" required />

                <x-atrium::form.select
                    name="family"
                    :label="__('showroom::showroom.family')"
                    :placeholder="__('showroom::showroom.no_family')"
                    :options="$families"
                    :selected="old('family')" />

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('showroom::showroom.create_product')" variant="primary" type="submit" data-testid="create-product" />
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
