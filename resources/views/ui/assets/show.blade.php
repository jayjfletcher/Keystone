@php($locale = app()->getLocale())

<x-atrium::layout :title="$asset->label()">
    <x-atrium::page-header :title="$asset->label()" :description="$asset->filename">
        <x-slot:actions>
            <x-atrium::icon-button icon="arrow-top-right-on-square" :label="__('keystone::keystone.open_file')" variant="outline" :href="$asset->url()" target="_blank" />

            @keystoneCan('delete', $asset)
                <form method="POST" action="{{ route('atrium.keystone.assets.destroy', $asset) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-asset" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-atrium::flash class="lg:col-span-3" />

        <x-atrium::card>
            @if ($asset->isImage())
                <img src="{{ $asset->url() }}" alt="{{ $asset->label() }}" class="w-full rounded-radius object-contain">
            @endif

            <x-atrium::description-list @class(['mt-3' => $asset->isImage()])>
                <x-atrium::description-list.item :term="__('keystone::keystone.code')" class="font-mono">{{ $asset->code }}</x-atrium::description-list.item>
                <x-atrium::description-list.item :term="__('keystone::keystone.type')">{{ $asset->mime_type ?? __('keystone::keystone.none') }}</x-atrium::description-list.item>
                <x-atrium::description-list.item :term="__('keystone::keystone.size')" class="tabular-nums">{{ number_format($asset->size / 1024, 1) }} KB</x-atrium::description-list.item>
                <x-atrium::description-list.item :term="__('keystone::keystone.checksum')" class="break-all font-mono text-xs">{{ $asset->checksum }}</x-atrium::description-list.item>
            </x-atrium::description-list>
        </x-atrium::card>

        <div class="flex flex-col gap-5 lg:col-span-2">
            @keystoneCan('update', $asset)
            <x-atrium::card :title="__('keystone::keystone.details')" data-testid="asset-details-card">
                <form method="POST" action="{{ route('atrium.keystone.assets.update', $asset) }}" enctype="multipart/form-data" class="flex flex-wrap items-start gap-3">
                    @csrf
                    @method('PATCH')
                    <x-atrium::form.input :name="'labels['.$locale.']'" :label="__('keystone::keystone.label_field', ['locale' => $locale])" :value="$asset->labels[$locale] ?? null" wrapper="w-64" />
                    <x-atrium::form.file name="file" :label="__('keystone::keystone.replace_file')" wrapper="w-72" />
                    <x-atrium::form.actions>
                        <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-asset" />
                    </x-atrium::form.actions>
                </form>
            </x-atrium::card>
            @endkeystoneCan

            <x-atrium::card :title="__('keystone::keystone.links')">
                @php($canLink = \JayI\Keystone\Atrium\ScreenAccess::allows('update', $asset))
                @php($links = [
                    'product' => $asset->products,
                    'product_model' => $asset->productModels,
                    'owner' => $asset->owners,
                ])

                @if (collect($links)->flatten()->isEmpty())
                    <x-atrium::empty-state :title="__('keystone::keystone.no_links')" />
                @else
                    <x-atrium::table compact>
                        <x-slot:head>
                            <x-atrium::table.row>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.type') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.target') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.role') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading></x-atrium::table.cell>
                            </x-atrium::table.row>
                        </x-slot:head>

                        @foreach ($links as $type => $records)
                            @foreach ($records as $record)
                                @php($target = $type === 'product' ? $record->identifier : $record->code)
                                <x-atrium::table.row>
                                    <x-atrium::table.cell>{{ str_replace('_', ' ', $type) }}</x-atrium::table.cell>
                                    <x-atrium::table.cell class="font-mono text-xs">{{ $target }}</x-atrium::table.cell>
                                    <x-atrium::table.cell>{{ $record->pivot->role }}</x-atrium::table.cell>
                                    <x-atrium::table.cell>
                                        @if ($canLink)
                                        <form method="POST" action="{{ route('atrium.keystone.assets.detach', $asset) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="{{ $type }}">
                                            <input type="hidden" name="target" value="{{ $target }}">
                                            <input type="hidden" name="role" value="{{ $record->pivot->role }}">
                                            <x-atrium::icon-button icon="link-slash" :label="__('keystone::keystone.unlink')" variant="ghost" size="sm" type="submit" data-testid="detach-asset" />
                                        </form>
                                        @endif
                                    </x-atrium::table.cell>
                                </x-atrium::table.row>
                            @endforeach
                        @endforeach
                    </x-atrium::table>
                @endif

                @if ($canLink)
                <form method="POST" action="{{ route('atrium.keystone.assets.attach', $asset) }}" class="mt-4 flex flex-wrap items-start gap-3">
                    @csrf
                    <x-atrium::form.select name="type" :label="__('keystone::keystone.type')" :options="$types" required wrapper="w-40" />
                    <x-atrium::form.input name="target" :label="__('keystone::keystone.target')" :hint="__('keystone::keystone.target_hint')" required wrapper="w-48" />
                    <x-atrium::form.input name="role" :label="__('keystone::keystone.role')" value="media" wrapper="w-36" />
                    <x-atrium::form.actions>
                        <x-atrium::icon-button icon="link" :label="__('keystone::keystone.link')" variant="primary" type="submit" data-testid="attach-asset" />
                    </x-atrium::form.actions>
                </form>
                @endif
            </x-atrium::card>
        </div>

        <x-atrium::audit-trail source="keystone" :subject="$asset" class="lg:col-span-3" />
    </div>
</x-atrium::layout>
