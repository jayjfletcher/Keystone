{{-- A record's associations (own and inherited), with one-at-a-time add and
     remove. Inherited ones are shown but removed on the model they come from. --}}
@php
    $presented = app(\RefactorCircus\Showroom\Domains\Association\Services\Associations::class)->present($record);
    $types = \RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel::query()->orderBy('code')->get();
    $own = $record->associations->groupBy(fn ($association) => $association->type->code);
    // Adding or removing one is saved as an update of the record.
    $canUpdate = \RefactorCircus\Showroom\Atrium\ScreenAccess::allows('update', $record);
@endphp

<x-atrium::card :title="__('showroom::showroom.associations')">
    @if ($presented['associations'] === [] && $presented['quantified_associations'] === [])
        <x-atrium::empty-state :title="__('showroom::showroom.no_associations')" />
    @else
        <div class="flex flex-col gap-3">
            @foreach ([...$presented['associations'], ...$presented['quantified_associations']] as $code => $groups)
                <div data-association-type="{{ $code }}">
                    <p class="mb-1 font-mono text-xs font-medium uppercase opacity-70">{{ $code }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach (['products' => 'products', 'product_models' => 'product_models'] as $kind => $key)
                            @foreach ($groups[$key] as $item)
                                @php
                                    $identifier = is_array($item) ? $item['identifier'] : $item;
                                    $isOwn = $own->get($code, collect())->contains(fn ($association) => ($association->target?->identifier ?? $association->target?->code) === $identifier);
                                    $href = $kind === 'products' ? route('atrium.showroom.products.show', $identifier) : route('atrium.showroom.product-models.show', $identifier);
                                @endphp
                                <li class="flex items-center gap-1.5 rounded-radius border border-outline px-2 py-1 text-sm dark:border-outline-dark">
                                    <a class="font-mono underline-offset-2 hover:underline" href="{{ $href }}">{{ $identifier }}</a>
                                    @if (is_array($item))<span class="text-xs opacity-70">× {{ $item['quantity'] }}</span>@endif
                                    @if ($isOwn && $canUpdate)
                                        <form method="POST" action="{{ route('atrium.showroom.associations.remove') }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="source_kind" value="{{ $sourceKind }}">
                                            <input type="hidden" name="source" value="{{ $sourceKey }}">
                                            <input type="hidden" name="type" value="{{ $code }}">
                                            <input type="hidden" name="target_kind" value="{{ $kind }}">
                                            <input type="hidden" name="target" value="{{ $identifier }}">
                                            <x-atrium::icon-button icon="x-mark" :label="__('showroom::showroom.remove')" variant="ghost" size="sm" type="submit" data-testid="remove-association" />
                                        </form>
                                    @elseif (! $isOwn)
                                        <span class="text-xs opacity-60">{{ __('showroom::showroom.inherited') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    @if ($types->isNotEmpty() && $canUpdate)
        <form method="POST" action="{{ route('atrium.showroom.associations.add') }}" class="mt-4 flex flex-wrap items-start gap-3">
            @csrf
            <input type="hidden" name="source_kind" value="{{ $sourceKind }}">
            <input type="hidden" name="source" value="{{ $sourceKey }}">
            <x-atrium::form.select name="type" :label="__('showroom::showroom.association_type')" :options="$types->pluck('code', 'code')->all()" required wrapper="w-44" />
            <x-atrium::form.select name="target_kind" :label="__('showroom::showroom.target_kind')" :options="['products' => __('showroom::showroom.product'), 'product_models' => __('showroom::showroom.product_model')]" required wrapper="w-40" />
            <x-atrium::form.input name="target" :label="__('showroom::showroom.target')" required wrapper="w-44" />
            <x-atrium::form.input name="quantity" type="number" min="1" :label="__('showroom::showroom.quantity')" :hint="__('showroom::showroom.quantity_hint')" wrapper="w-28" />
            <x-atrium::form.actions>
                <x-atrium::icon-button icon="plus" :label="__('showroom::showroom.add')" variant="primary" type="submit" data-testid="add-association" />
            </x-atrium::form.actions>
        </form>
    @endif
</x-atrium::card>
