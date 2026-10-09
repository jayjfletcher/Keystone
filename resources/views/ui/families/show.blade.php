@use(RefactorCircus\Keystone\Atrium\ScreenAccess)
@use(RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel)
@php($locale = app()->getLocale())
@php($canUpdate = ScreenAccess::allows('update', $family))

<x-atrium::layout :title="$family->label()">
    <x-atrium::page-header :title="$family->label()" :description="$family->code">
        <x-slot:actions>
            @keystoneCan('delete', $family)
                <form method="POST" action="{{ route('atrium.keystone.families.destroy', $family) }}">
                    @csrf
                    @method('DELETE')
                    <x-atrium::icon-button icon="trash" :label="__('keystone::keystone.delete')" variant="danger" type="submit" data-testid="delete-family" />
                </form>
            @endkeystoneCan
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5">
        <x-atrium::flash class="mb-4" />

        {{-- One form: the Action replaces the whole membership, so every row is
             posted back, with a remove box, plus one attribute to add. --}}
        <form method="POST" action="{{ route('atrium.keystone.families.update', $family) }}">
            @csrf
            @method('PATCH')

            <fieldset class="flex min-w-0 flex-col gap-5" @disabled(! $canUpdate)>

                <x-atrium::card :title="__('keystone::keystone.details')">
                    <div class="flex flex-wrap items-start gap-3">
                        <x-atrium::form.input
                            :name="'labels['.$locale.']'"
                            :label="__('keystone::keystone.label_field', ['locale' => $locale])"
                            :value="$family->labels[$locale] ?? null"
                            wrapper="w-64" />

                        <x-atrium::form.select
                            name="label_attribute"
                            :label="__('keystone::keystone.label_attribute')"
                            :hint="__('keystone::keystone.label_attribute_hint')"
                            :placeholder="__('keystone::keystone.no_label_attribute')"
                            :options="$labelCandidates"
                            :selected="$family->labelAttribute?->code"
                            wrapper="w-64" />

                        <x-atrium::form.input name="sort_order" type="number" min="0" :label="__('keystone::keystone.sort_order')" :value="$family->sort_order" wrapper="w-28" />
                    </div>
                </x-atrium::card>

                <x-atrium::card :title="__('keystone::keystone.family_attributes')">
                    @if ($family->familyAttributes->isEmpty())
                        <x-atrium::empty-state :title="__('keystone::keystone.no_family_attributes')" />
                    @else
                        <x-atrium::table compact>
                            <x-slot:head>
                                <x-atrium::table.row>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.code') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.type') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.group') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.required') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.required_channels') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.sort_order') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell heading>{{ __('keystone::keystone.remove') }}</x-atrium::table.cell>
                                </x-atrium::table.row>
                            </x-slot:head>

                            @foreach ($family->familyAttributes as $index => $attribute)
                                <x-atrium::table.row>
                                    <x-atrium::table.cell>
                                        <input type="hidden" name="attributes[{{ $index }}][attribute]" value="{{ $attribute->code }}">
                                        <a class="font-mono underline-offset-2 hover:underline"
                                           href="{{ route('atrium.keystone.attributes.show', $attribute) }}">{{ $attribute->code }}</a>
                                    </x-atrium::table.cell>
                                    <x-atrium::table.cell><x-atrium::badge>{{ $attribute->type->value }}</x-atrium::badge></x-atrium::table.cell>
                                    <x-atrium::table.cell>{{ $attribute->group?->label() ?? __('keystone::keystone.none') }}</x-atrium::table.cell>
                                    <x-atrium::table.cell>
                                        <input type="hidden" name="attributes[{{ $index }}][is_required]" value="0">
                                        <x-atrium::form.checkbox bare :name="'attributes['.$index.'][is_required]'" :checked="(bool) $attribute->pivot->is_required" />
                                    </x-atrium::table.cell>
                                    <x-atrium::table.cell>
                                        @php($channels = $attribute->pivot->required_channels)
                                        <x-atrium::form.input bare :name="'attributes['.$index.'][required_channels]'"
                                            :value="implode(', ', is_string($channels) ? (json_decode($channels, true) ?? []) : (array) $channels)"
                                            :placeholder="__('keystone::keystone.required_channels_hint')"
                                            class="w-40" />
                                    </x-atrium::table.cell>
                                    <x-atrium::table.cell>
                                        <x-atrium::form.input bare type="number" min="0" :name="'attributes['.$index.'][sort_order]'" :value="$attribute->pivot->sort_order" class="w-20" />
                                    </x-atrium::table.cell>
                                    <x-atrium::table.cell>
                                        <x-atrium::form.checkbox bare :name="'attributes['.$index.'][remove]'" />
                                    </x-atrium::table.cell>
                                </x-atrium::table.row>
                            @endforeach
                        </x-atrium::table>
                    @endif

                    @if ($canUpdate)
                    <div class="mt-4 flex flex-wrap items-start gap-3">
                        <x-atrium::form.select
                            name="add_attribute"
                            :label="__('keystone::keystone.add_attribute')"
                            :placeholder="__('keystone::keystone.choose_attribute')"
                            :options="$available->mapWithKeys(fn ($attribute) => [$attribute->code => $attribute->code.' ('.$attribute->type->value.')'])"
                            wrapper="w-72" />

                        <input type="hidden" name="add_required" value="0">
                        <x-atrium::form.actions>
                            <x-atrium::form.checkbox name="add_required" :label="__('keystone::keystone.required')" wrapper="h-9 w-auto justify-center" />
                        </x-atrium::form.actions>
                    </div>
                    @endif
                </x-atrium::card>

                @if ($canUpdate)
                    <div>
                        <x-atrium::icon-button icon="check" :label="__('keystone::keystone.save')" variant="primary" type="submit" data-testid="save-family" />
                    </div>
                @endif
            </fieldset>
        </form>

        <x-atrium::card :title="__('keystone::keystone.family_variants')" class="mt-5">
            @if ($family->variants->isNotEmpty())
                <ul class="mb-4 flex flex-wrap gap-2">
                    @foreach ($family->variants as $variant)
                        <li><a class="font-mono text-sm underline-offset-2 hover:underline" href="{{ route('atrium.keystone.family-variants.show', $variant) }}">{{ $variant->code }}</a></li>
                    @endforeach
                </ul>
            @endif

            {{-- Two fixed levels; leave the second empty for a one-level variant. --}}
            @keystoneCan('create', FamilyVariantModel::class)
            <form method="POST" action="{{ route('atrium.keystone.family-variants.store') }}" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="family" value="{{ $family->code }}">
                <x-atrium::form.input name="code" :label="__('keystone::keystone.code')" required wrapper="w-64" />

                @foreach ([0, 1] as $level)
                    <fieldset class="rounded-radius border border-outline p-3 dark:border-outline-dark">
                        <legend class="px-1 text-sm font-medium">{{ __('keystone::keystone.level', ['level' => $level + 1]) }}</legend>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (['axes' => __('keystone::keystone.axes'), 'attributes' => __('keystone::keystone.attributes')] as $kind => $heading)
                                <div>
                                    <p class="mb-1 text-xs font-medium uppercase opacity-70">{{ $heading }}</p>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach ($family->familyAttributes as $attribute)
                                            <label class="flex items-center gap-1.5 text-sm">
                                                <x-atrium::form.checkbox bare :id="'atrium-levels-'.$level.'-'.$kind.'-'.$attribute->code" :name="'levels['.$level.']['.$kind.'][]'" :value="$attribute->code" />
                                                <span class="font-mono text-xs">{{ $attribute->code }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <div>
                    <x-atrium::icon-button icon="plus" :label="__('keystone::keystone.create_family_variant')" variant="primary" type="submit" data-testid="create-family-variant" />
                </div>
            </form>
            @endkeystoneCan
        </x-atrium::card>

        <x-atrium::audit-trail source="keystone" :subject="$family" class="mt-5" />
    </div>
</x-atrium::layout>
