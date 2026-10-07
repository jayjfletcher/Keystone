@use(JayI\Keystone\Domains\Attribute\Services\Values)

@if ($inherited !== [])
    <x-atrium::card :title="__('keystone::keystone.inherited_values')">
        <x-atrium::description-list>
            @foreach (Values::toStandard($inherited) as $code => $slots)
                @foreach ($slots as $slot)
                    <x-atrium::description-list.item
                        :term="$code.($slot['locale'] ? ' · '.$slot['locale'] : '').($slot['scope'] ? ' · '.$slot['scope'] : '')">{{ is_scalar($slot['data']) ? (is_bool($slot['data']) ? ($slot['data'] ? __('keystone::keystone.yes') : __('keystone::keystone.no')) : $slot['data']) : json_encode($slot['data']) }}</x-atrium::description-list.item>
                @endforeach
            @endforeach
        </x-atrium::description-list>
    </x-atrium::card>
@endif
