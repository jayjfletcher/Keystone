{{-- The fields of a channel; shared by the create and edit forms. --}}
<div class="flex flex-col gap-3">
    <div>
        <p class="mb-1 text-xs font-medium uppercase opacity-70">{{ __('keystone::keystone.locales') }}</p>
        <div class="flex flex-wrap gap-3">
            @forelse ($allLocales as $code)
                <label class="flex items-center gap-1.5 text-sm">
                    <x-atrium::form.checkbox bare :id="'atrium-locales-'.$code" name="locales[]" :value="$code" :checked="in_array($code, $channel?->locales->pluck('code')->all() ?? [], true)" />
                    <span class="font-mono text-xs">{{ $code }}</span>
                </label>
            @empty
                <span class="text-sm opacity-60">{{ __('keystone::keystone.no_locales') }}</span>
            @endforelse
        </div>
    </div>

    <div class="flex flex-wrap items-end gap-3">
        <x-atrium::form.input name="currencies" :label="__('keystone::keystone.currencies')" :hint="__('keystone::keystone.currencies_hint')" :value="implode(', ', $channel?->currencies ?? [])" wrapper="w-56" />
        <x-atrium::form.select name="category_tree" :label="__('keystone::keystone.category_tree')" :placeholder="__('keystone::keystone.none')" :options="$trees" :selected="$channel?->categoryTree?->code" wrapper="w-56" />
    </div>
</div>
