<div class="flex flex-col gap-4">
    <x-atrium::card :title="__('keystone::keystone.api')">
        <x-atrium::description-list>
            <x-atrium::description-list.item :term="__('keystone::keystone.authorization')">{{ $authorization ? __('keystone::keystone.on') : __('keystone::keystone.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('keystone::keystone.routes_prefix')" class="font-mono">{{ ($routes['enabled'] ?? false) ? '/'.($routes['prefix'] ?? '') : __('keystone::keystone.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('keystone::keystone.per_page')" class="tabular-nums">{{ $pagination['per_page'] ?? '' }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('keystone::keystone.max_per_page')" class="tabular-nums">{{ $pagination['max_per_page'] ?? '' }}</x-atrium::description-list.item>
        </x-atrium::description-list>
    </x-atrium::card>

    <x-atrium::card :title="__('keystone::keystone.mcp')">
        <x-atrium::description-list>
            <x-atrium::description-list.item :term="__('keystone::keystone.web_transport')" class="font-mono">{{ ($mcp['web']['enabled'] ?? false) ? '/'.($mcp['web']['route'] ?? '') : __('keystone::keystone.off') }}</x-atrium::description-list.item>
            <x-atrium::description-list.item :term="__('keystone::keystone.local_transport')" class="font-mono">{{ ($mcp['local']['enabled'] ?? false) ? ($mcp['local']['handle'] ?? '') : __('keystone::keystone.off') }}</x-atrium::description-list.item>
        </x-atrium::description-list>
    </x-atrium::card>
</div>
