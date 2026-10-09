<x-atrium::layout :title="$variant->label()">
    <x-atrium::page-header :title="$variant->label()" :description="$variant->code.' · '.$variant->family->label()">
        <x-slot:actions>
            @showroomCan('view', $variant->family)
                <x-atrium::icon-button icon="arrow-left" :label="$variant->family->code" variant="outline" :href="route('atrium.showroom.families.show', $variant->family)" data-testid="variant-family" />
            @endshowroomCan

            @showroomCan('delete', $variant)
                <form method="POST" action="{{ route('atrium.showroom.family-variants.destroy', $variant) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('showroom::showroom.delete')" variant="danger" type="submit" data-testid="delete-family-variant" />
                </form>
            @endshowroomCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5 flex flex-col gap-5">
        <x-atrium::flash />

        <x-atrium::card :title="__('showroom::showroom.levels')">
            <x-atrium::table compact>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('showroom::showroom.level_column') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('showroom::showroom.axes') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('showroom::showroom.attributes') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                <x-atrium::table.row>
                    <x-atrium::table.cell>{{ __('showroom::showroom.common') }}</x-atrium::table.cell>
                    <x-atrium::table.cell>{{ __('showroom::showroom.none') }}</x-atrium::table.cell>
                    <x-atrium::table.cell class="font-mono text-xs">{{ $variant->attributesAt(0)->pluck('code')->join(', ') ?: __('showroom::showroom.none') }}</x-atrium::table.cell>
                </x-atrium::table.row>

                @foreach (range(1, $variant->levels) as $level)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>{{ $level }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $variant->axesAt($level)->pluck('code')->join(', ') }}</x-atrium::table.cell>
                        <x-atrium::table.cell class="font-mono text-xs">{{ $variant->attributesAt($level)->reject(fn ($attribute) => $variant->pivotOf($attribute)['is_axis'])->pluck('code')->join(', ') ?: __('showroom::showroom.none') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        </x-atrium::card>

        <x-atrium::card :title="__('showroom::showroom.product_models')">
            @if ($models->isEmpty())
                <x-atrium::empty-state :title="__('showroom::showroom.no_product_models')" />
            @else
                <ul class="flex flex-wrap gap-2">
                    @foreach ($models as $model)
                        <li><a class="font-mono text-sm underline-offset-2 hover:underline" href="{{ route('atrium.showroom.product-models.show', $model) }}">{{ $model->code }}</a></li>
                    @endforeach
                </ul>
            @endif
        </x-atrium::card>
    </div>
</x-atrium::layout>
