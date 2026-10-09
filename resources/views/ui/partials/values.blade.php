@use(RefactorCircus\Showroom\Domains\Attribute\Enums\AttributeType)
@use(RefactorCircus\Showroom\Domains\Attribute\Services\Values)

{{-- One input per attribute, for the slot this form edits: the picked
     locale for localizable attributes and the picked channel for scopable
     ones. The slot rides along as hidden fields. --}}
@php
    $locale = $slot->locale;
@endphp

<input type="hidden" name="locale" value="{{ $slot->locale }}">
<input type="hidden" name="channel" value="{{ $slot->channel }}">

@if ($attributes->isEmpty())
    <x-atrium::empty-state :title="__('showroom::showroom.no_settable_attributes')" />
@else
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($attributes as $attribute)
            @php
                $code = $attribute->code;
                $name = 'v['.$code.']';
                $data = Values::get($values, $code, $attribute->is_scopable ? $slot->channel : null, $attribute->is_localizable ? $locale : null);
                $settings = $attribute->settings ?? [];
            @endphp

            <div class="flex flex-col gap-1.5" data-attribute="{{ $code }}">
                <label class="text-sm font-medium text-on-surface-strong dark:text-on-surface-dark-strong">
                    {{ $attribute->label() }}
                    <span class="font-mono text-xs font-normal opacity-60">{{ $code }} · {{ $attribute->type->value }}@if ($attribute->is_localizable) · {{ $locale }}@endif @if ($attribute->is_scopable) · {{ $slot->channel }}@endif</span>
                </label>

                @if (($attribute->is_localizable && $slot->locale === null) || ($attribute->is_scopable && $slot->channel === null))
                    <p class="text-xs text-on-surface/80 dark:text-on-surface-dark/80">{{ __('showroom::showroom.slot_unavailable') }}</p>
                @else
                    @switch($attribute->type)
                        @case(AttributeType::Textarea)
                            <x-atrium::form.textarea bare :name="$name" :value="$data" rows="3" />
                            @break

                        @case(AttributeType::Number)
                        @case(AttributeType::Decimal)
                            <x-atrium::form.input bare type="number" :name="$name" :value="$data" :step="$attribute->type === AttributeType::Number ? '1' : 'any'" />
                            @break

                        @case(AttributeType::Date)
                            <x-atrium::form.input bare type="date" :name="$name" :value="$data" />
                            @break

                        @case(AttributeType::Boolean)
                            <x-atrium::form.select bare :name="$name"
                                :placeholder="__('showroom::showroom.none')"
                                :options="['1' => __('showroom::showroom.yes'), '0' => __('showroom::showroom.no')]"
                                :selected="match ($data) { true => '1', false => '0', default => '' }" />
                            @break

                        @case(AttributeType::Select)
                            <x-atrium::form.select bare :name="$name"
                                :placeholder="__('showroom::showroom.none')"
                                :options="$attribute->options->mapWithKeys(fn ($option) => [$option->code => $option->label()])->all()"
                                :selected="is_string($data) ? $data : ''" />
                            @break

                        @case(AttributeType::Multiselect)
                            <input type="hidden" name="{{ $name }}[]" value="">
                            <div class="flex flex-wrap gap-3">
                                @foreach ($attribute->options as $option)
                                    <x-atrium::form.checkbox bare
                                        :id="'atrium-v-'.$code.'-'.$option->code"
                                        :name="$name.'[]'"
                                        :value="$option->code"
                                        :label="$option->label()"
                                        :checked="in_array($option->code, (array) $data, true)" />
                                @endforeach
                            </div>
                            @break

                        @case(AttributeType::Metric)
                            <div class="grid grid-cols-2 gap-2">
                                <x-atrium::form.input bare type="number" step="any" :name="$name.'[amount]'" :value="$data['amount'] ?? ''" />
                                <x-atrium::form.input bare :name="$name.'[unit]'" :value="$data['unit'] ?? ($settings['default_unit'] ?? '')" />
                            </div>
                            @break

                        @case(AttributeType::Price)
                            @php
                                $amounts = collect((array) $data)->mapWithKeys(fn ($price) => [$price['currency'] ?? '' => $price['amount'] ?? null]);
                                $currencies = ($settings['currencies'] ?? []) ?: ($amounts->keys()->filter()->all() ?: ['USD']);
                            @endphp
                            <div class="flex flex-wrap gap-2">
                                @foreach ($currencies as $currency)
                                    <x-atrium::form.input bare type="number" step="any" :name="$name.'['.$currency.']'" :value="$amounts[$currency] ?? ''" class="w-32">
                                        <x-slot:prefix><span class="font-mono text-xs">{{ $currency }}</span></x-slot:prefix>
                                    </x-atrium::form.input>
                                @endforeach
                            </div>
                            @break

                        @default
                            <x-atrium::form.input bare :name="$name" :value="$data" />
                    @endswitch

                    @php($error = $errors->first('values.'.$code.'*') ?: $errors->first('values.'.$code))
                    @if ($error)
                        <small class="text-xs text-danger">{{ $error }}</small>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
@endif
