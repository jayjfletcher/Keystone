<div class="flex flex-col gap-4">
    <x-atrium::card :title="__('showroom::showroom.api')">
        <x-atrium::description-list>
            <x-atrium::description-list.item :term="__('showroom::showroom.authorization')">{{ $authorization ? __('showroom::showroom.on') : __('showroom::showroom.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('showroom::showroom.routes_prefix')" class="font-mono">{{ ($routes['enabled'] ?? false) ? '/'.($routes['prefix'] ?? '') : __('showroom::showroom.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('showroom::showroom.per_page')" class="tabular-nums">{{ $pagination['per_page'] ?? '' }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('showroom::showroom.max_per_page')" class="tabular-nums">{{ $pagination['max_per_page'] ?? '' }}</x-atrium::description-list.item>
        </x-atrium::description-list>
    </x-atrium::card>

    <x-atrium::card :title="__('showroom::showroom.mcp')">
        <x-atrium::description-list>
            <x-atrium::description-list.item :term="__('showroom::showroom.web_transport')" class="font-mono">{{ ($mcp['web']['enabled'] ?? false) ? '/'.($mcp['web']['route'] ?? '') : __('showroom::showroom.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('showroom::showroom.local_transport')" class="font-mono">{{ ($mcp['local']['enabled'] ?? false) ? ($mcp['local']['handle'] ?? '') : __('showroom::showroom.off') }}</x-atrium::description-list.item>
        </x-atrium::description-list>
    </x-atrium::card>
</div>
