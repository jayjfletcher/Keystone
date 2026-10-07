@use(JayI\Keystone\Domains\Asset\Models\AssetModel)
@php($locale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.assets')">
    <x-atrium::page-header :title="__('keystone::keystone.assets')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('create', AssetModel::class)
        <x-atrium::card :title="__('keystone::keystone.upload_asset')">
            <form method="POST" action="{{ route('atrium.keystone.assets.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.file name="file" :label="__('keystone::keystone.file')" required wrapper="w-72" />
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.asset_code_hint')" wrapper="w-48" />
                <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="arrow-up-tray" :label="__('keystone::keystone.upload')" variant="primary" type="submit" data-testid="upload-asset" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.keystone.assets.index') }}" class="flex flex-wrap items-start gap-3">
                <x-atrium::form.input name="search" :label="__('keystone::keystone.search')" :value="$filters['search'] ?? null" wrapper="w-56" />
                <x-atrium::form.select
                    name="type"
                    :label="__('keystone::keystone.type')"
                    :placeholder="__('keystone::keystone.all_types')"
                    :options="['image/*' => __('keystone::keystone.images'), 'application/pdf' => 'PDF', 'video/*' => __('keystone::keystone.videos')]"
                    :selected="$filters['type'] ?? null"
                    wrapper="w-40" />
                <x-atrium::form.input name="product" :label="__('keystone::keystone.product')" :value="$filters['product'] ?? null" wrapper="w-44" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="funnel" :label="__('keystone::keystone.filter')" variant="primary" type="submit" />
                    <x-atrium::icon-button icon="x-mark" :label="__('keystone::keystone.clear')" variant="ghost" :href="route('atrium.keystone.assets.index')" />
                </x-atrium::form.actions>
            </form>
        </x-atrium::card>

        @if ($assets->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_assets')" />
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($assets as $asset)
                    @include('keystone::ui.assets.tile', ['asset' => $asset, 'caption' => $asset->code])
                @endforeach
            </div>

            <x-atrium::pagination :paginator="$assets" />
        @endif
    </div>
</x-atrium::layout>
