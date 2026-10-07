{{-- A hidden 0 precedes each box, so unticking one is sent rather than omitted. --}}
<div class="flex flex-col gap-2">
    @foreach (['is_unique', 'is_localizable', 'is_scopable'] as $flag)
        <input type="hidden" name="{{ $flag }}" value="0">
        <x-atrium::form.checkbox :name="$flag" :label="__('keystone::keystone.'.$flag)" :checked="(bool) ($attribute?->{$flag} ?? false)" />
    @endforeach
</div>
