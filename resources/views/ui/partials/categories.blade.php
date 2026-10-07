{{-- Category codes as a comma-separated list: scales to any number of trees. --}}
<div class="flex flex-col gap-2">
    <x-atrium::form.input
        name="categories"
        :label="__('keystone::keystone.categories')"
        :hint="__('keystone::keystone.categories_hint')"
        :value="$record->categories->pluck('code')->join(', ')"
        wrapper="w-full" />

    @if ($inheritedCategories->isNotEmpty())
        <p class="text-xs text-on-surface dark:text-on-surface-dark">
            {{ __('keystone::keystone.inherited_categories') }}:
            @foreach ($inheritedCategories as $category)
                <a class="font-mono underline-offset-2 hover:underline" href="{{ route('atrium.keystone.categories.show', $category) }}">{{ $category->code }}</a>@unless ($loop->last), @endunless
            @endforeach
        </p>
    @endif
</div>
