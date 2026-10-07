@use(JayI\Keystone\Domains\Channel\Models\ChannelModel)
@php($uiLocale = app()->getLocale())

<x-atrium::layout :title="__('keystone::keystone.channels')">
    <x-atrium::page-header :title="__('keystone::keystone.channels')" :description="__('keystone::keystone.channels_description')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @keystoneCan('viewAny', \JayI\Keystone\Domains\Channel\Models\LocaleModel::class)
        <x-atrium::card :title="__('keystone::keystone.locales')" data-testid="locales-card">
            @if ($locales->isNotEmpty())
                <ul class="mb-4 flex flex-wrap gap-3">
                    @foreach ($locales as $locale)
                        <li class="flex items-center gap-2 rounded-radius border border-outline px-2 py-1 text-sm dark:border-outline-dark" data-locale="{{ $locale->code }}">
                            <span class="font-mono">{{ $locale->code }}</span>
                            <span class="opacity-70">{{ $locale->label() !== $locale->code ? $locale->label() : '' }}</span>
                            @keystoneCan('delete', $locale)
                                <form method="POST" action="{{ route('atrium.keystone.locales.destroy', $locale) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="ghost" size="sm" type="submit" data-testid="delete-locale" />
                                </form>
                            @endkeystoneCan
                        </li>
                    @endforeach
                </ul>
            @endif

            @keystoneCan('create', \JayI\Keystone\Domains\Channel\Models\LocaleModel::class)
            <form method="POST" action="{{ route('atrium.keystone.locales.store') }}" class="flex flex-wrap items-start gap-3">
                @csrf
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.locale_code_hint')" required wrapper="w-44" />
                <x-atrium::form.input :name="'labels['.$uiLocale.']'" :label="__('keystone::keystone.label_field', ['locale' => $uiLocale])" wrapper="w-56" />
                <x-atrium::form.actions>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.add')" variant="primary" type="submit" data-testid="create-locale" />
                </x-atrium::form.actions>
            </form>
            @endkeystoneCan
        </x-atrium::card>
        @endkeystoneCan

        @keystoneCan('create', ChannelModel::class)
        <x-atrium::card data-testid="new-channel-card" :title="__('keystone::keystone.new_channel')">
            <form method="POST" action="{{ route('atrium.keystone.channels.store') }}" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-wrap items-start gap-3">
                    <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" :hint="__('keystone::keystone.code_hint')" required wrapper="w-56" />
                    <x-atrium::form.input :name="'labels['.$uiLocale.']'" :label="__('keystone::keystone.label_field', ['locale' => $uiLocale])" wrapper="w-56" />
                </div>
                @include('keystone::ui.channels.fields', ['channel' => null])
                <div>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create')" variant="primary" type="submit" data-testid="create-channel" />
                </div>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        @if ($channels->isEmpty())
            <x-atrium::empty-state :title="__('keystone::keystone.no_channels')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.locales') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.currencies') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.category_tree') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($channels as $channel)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-mono font-medium underline-offset-2 hover:underline" href="{{ route('atrium.keystone.channels.show', $channel) }}">{{ $channel->code }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $channel->locales->pluck('code')->join(', ') }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ implode(', ', $channel->currencies ?? []) }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $channel->categoryTree?->code ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        @endif
    </div>
</x-atrium::layout>
