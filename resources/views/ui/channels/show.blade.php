@php($uiLocale = app()->getLocale())

<x-atrium::layout :title="$channel->label()">
    <x-atrium::page-header :title="$channel->label()" :description="$channel->code">
        <x-slot:actions>
            @keystoneCan('delete', $channel)
                <form method="POST" action="{{ route('atrium.keystone.channels.destroy', $channel) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-channel" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        @keystoneCan('update', $channel)
        <x-atrium::card data-testid="channel-details-card" :title="__('keystone::keystone.details')">
            <form method="POST" action="{{ route('atrium.keystone.channels.update', $channel) }}" class="flex flex-col gap-4">
                @csrf
                @method('PATCH')
                <x-atrium::form.input :name="'labels['.$uiLocale.']'" :label="__('keystone::keystone.label_field', ['locale' => $uiLocale])" :value="$channel->labels[$uiLocale] ?? null" wrapper="w-64" />
                @include('keystone::ui.channels.fields')
                <div>
                    <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-channel" />
                </div>
            </form>
        </x-atrium::card>
        @endkeystoneCan

        <p class="text-sm text-on-surface dark:text-on-surface-dark">{{ __('keystone::keystone.channel_delete_note') }}</p>

        <x-atrium::audit-trail source="keystone" :subject="$channel" />
    </div>
</x-atrium::layout>
