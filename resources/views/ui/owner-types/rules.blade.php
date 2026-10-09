{{-- The chain rules of an owner type; shared by the create and edit forms. --}}
@php($restricted = $type?->restricts_parents ?? false)
@php($chosen = $type?->parentTypes->pluck('code')->all() ?? [])

<div class="flex flex-col gap-3">
    <input type="hidden" name="any_parent" value="0">
    <x-atrium::form.checkbox name="any_parent" :label="__('showroom::showroom.any_parent')" :checked="! $restricted" />

    <div>
        <p class="mb-1 text-xs font-medium uppercase opacity-70">{{ __('showroom::showroom.parent_types') }}</p>
        <div class="flex flex-wrap gap-3">
            @forelse ($all as $code)
                <label class="flex items-center gap-1.5 text-sm">
                    <x-atrium::form.checkbox bare :id="'atrium-parent-types-'.$code" name="parent_types[]" :value="$code" :checked="in_array($code, $chosen, true)" />
                    <span class="font-mono text-xs">{{ $code }}</span>
                </label>
            @empty
                <span class="text-sm opacity-60">{{ __('showroom::showroom.none') }}</span>
            @endforelse
        </div>
    </div>

    <input type="hidden" name="can_be_root" value="0">
    <x-atrium::form.checkbox name="can_be_root" :label="__('showroom::showroom.can_be_root')" :checked="$type?->can_be_root ?? true" />

    <input type="hidden" name="owns_products" value="0">
    <x-atrium::form.checkbox name="owns_products" :label="__('showroom::showroom.owns_products')" :checked="$type?->owns_products ?? true" />
</div>
