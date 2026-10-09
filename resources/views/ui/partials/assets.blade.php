{{-- The assets of a product, product model or owner, with forms to upload
     and link a new file or link an existing asset by code. --}}
<x-atrium::card :title="__('keystone::keystone.assets')">
    @if ($own->isEmpty() && $inheritedAssets->isEmpty())
        <x-atrium::empty-state :title="__('keystone::keystone.no_assets_here')" />
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            @foreach ($own as $asset)
                @include('keystone::ui.assets.tile', ['asset' => $asset, 'caption' => $asset->pivot->role.' · '.$asset->code])
            @endforeach
            @foreach ($inheritedAssets as $asset)
                @include('keystone::ui.assets.tile', ['asset' => $asset, 'caption' => $asset->pivot->role.' · '.__('keystone::keystone.inherited')])
            @endforeach
        </div>
    @endif

    {{-- Uploading creates an asset and links it, which updates that asset. --}}
    @keystoneCan('create', \RefactorCircus\Keystone\Domains\Asset\Models\AssetModel::class)
    <div class="mt-4 flex flex-col gap-3">
        <form method="POST" action="{{ route('atrium.keystone.assets.upload') }}" enctype="multipart/form-data" class="flex flex-wrap items-start gap-3">
            @csrf
            <input type="hidden" name="type" value="{{ $linkType }}">
            <input type="hidden" name="target" value="{{ $linkTarget }}">
            <x-atrium::form.file name="file" :label="__('keystone::keystone.upload_and_link')" required wrapper="w-72" />
            <x-atrium::form.input name="role" :label="__('keystone::keystone.role')" value="image" wrapper="w-32" />
            <x-atrium::form.actions>
                <x-atrium::icon-button icon="arrow-up-tray" :label="__('keystone::keystone.upload')" variant="primary" type="submit" data-testid="upload-and-link" />
            </x-atrium::form.actions>
        </form>
    </div>
    @endkeystoneCan
</x-atrium::card>
