<x-atrium::layout :title="$variant->label()">
    <x-atrium::page-header :title="$variant->label()" :description="$variant->code.' · '.$variant->family->label()">
        <x-slot:actions>
            @keystoneCan('view', $variant->family)
                <x-atrium::icon-button icon="arrow-left" :label="$variant->family->code" variant="outline" :href="route('atrium.keystone.families.show', $variant->family)" data-testid="variant-family" />
            @endkeystoneCan

            @keystoneCan('delete', $variant)
                <form method="POST" action="{{ route('atrium.keystone.family-variants.destroy', $variant) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-family-variant" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        <x-atrium::card :title="__('keystone::keystone.levels')">
            <x-atrium::table compact>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.level_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.axes') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('keystone::keystone.attributes') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                <x-atrium::table.row>
                    <x-atrium::table.cell>{{ __('keystone::keystone.common') }}</x-atrium::table.cell>
                    <x-atrium::table.cell>{{ __('keystone::keystone.none') }}</x-atrium::table.cell>
                    <x-atrium::table.cell class="font-mono text-xs">{{ $variant->attributesAt(0)->pluck('code')->join(', ') ?: __('keystone::keystone.none') }}</x-atrium::table.cell>
                </x-atrium::table.row>

                @foreach (range(1, $variant->levels) as $level)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>{{ $level }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $variant->axesAt($level)->pluck('code')->join(', ') }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $variant->attributesAt($level)->reject(fn ($attribute) => $variant->pivotOf($attribute)['is_axis'])->pluck('code')->join(', ') ?: __('keystone::keystone.none') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        </x-atrium::card>

        <x-atrium::card :title="__('keystone::keystone.product_models')">
            @if ($models->isEmpty())
                <x-atrium::empty-state :title="__('keystone::keystone.no_product_models')" />
            @else
                <ul class="flex flex-wrap gap-2">
                    @foreach ($models as $model)
                        <li><a class="font-mono text-sm underline-offset-2 hover:underline" href="{{ route('atrium.keystone.product-models.show', $model) }}">{{ $model->code }}</a></li>
                    @endforeach
                </ul>
            @endif
        </x-atrium::card>
    </div>
</x-atrium::layout>
