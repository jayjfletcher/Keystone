@use(JayI\Keystone\Domains\Attribute\Enums\AttributeType)

{{-- The axis values a new variant or sub-model needs, posted as values. --}}
@foreach ($axes as $axis)
    @php($name = 'values['.$axis->code.'][0][data]')

    <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface-strong dark:text-on-surface-dark-strong">{{ $axis->label() }}</label>

        @if ($axis->type === AttributeType::Select)
            <x-atrium::form.select bare required :name="$name"
                :options="$axis->options()->get()->mapWithKeys(fn ($option) => [$option->code => $option->label()])->all()" />
        @elseif ($axis->type === AttributeType::Boolean)
            <x-atrium::form.select bare required :name="$name"
                :options="['1' => __('keystone::keystone.yes'), '0' => __('keystone::keystone.no')]" />
        @else
            <div class="grid grid-cols-2 gap-2">
                <x-atrium::form.input bare required type="number" step="any" :name="$name.'[amount]'" />
                <x-atrium::form.input bare required :name="$name.'[unit]'" :value="$axis->settings['default_unit'] ?? ''" />
            </div>
        @endif

        <input type="hidden" name="values[{{ $axis->code }}][0][locale]" value="">
        <input type="hidden" name="values[{{ $axis->code }}][0][scope]" value="">
    </div>
@endforeach
