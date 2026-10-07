{{-- Pick which locale and channel the value form below edits. --}}
@if ($slot->locales !== [] || $slot->channels !== [])
    <x-atrium::card>
    <form method="GET" class="flex flex-wrap items-start gap-3" data-testid="slot-picker">
        @if ($slot->locales !== [])
            <x-atrium::form.select name="locale" :label="__('keystone::keystone.locale')" :options="$slot->locales" :selected="$slot->locale" wrapper="w-44" />
        @endif
        @if ($slot->channels !== [])
            <x-atrium::form.select name="channel" :label="__('keystone::keystone.channel')" :options="$slot->channels" :selected="$slot->channel" wrapper="w-44" />
        @endif
        <x-atrium::form.actions>
            <x-atrium::icon-button icon="arrows-right-left" :label="__('keystone::keystone.switch')" variant="outline" type="submit" />
        </x-atrium::form.actions>
    </form>
    </x-atrium::card>
@endif
