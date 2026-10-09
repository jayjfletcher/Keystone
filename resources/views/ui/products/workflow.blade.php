{{-- Status and live version, the transitions open from here, completeness per
     channel and locale, and the latest history with revert. --}}
@use(RefactorCircus\Showroom\Atrium\Badges)
@use(RefactorCircus\Showroom\Atrium\ScreenAccess)

@php
    $transitionIcons = [
        'submit' => 'paper-airplane',
        'approve' => 'check-circle',
        'reject' => 'x-circle',
        'publish' => 'rocket-launch',
        'unpublish' => 'eye-slash',
        'archive' => 'archive-box',
        'restore' => 'arrow-uturn-up',
    ];
@endphp

<x-atrium::card :title="__('showroom::showroom.workflow')">
    <div class="flex flex-wrap items-center gap-3 text-sm" data-testid="workflow">
        <x-atrium::status-dot :variant="Badges::forStatus($product->status)" :label="__('showroom::showroom.status_'.$product->status->value)" data-status="{{ $product->status->value }}" data-testid="product-status" />
        <span>
            {{ __('showroom::showroom.published_version') }}:
            @if ($product->published_version)
                <span class="font-mono">v{{ $product->published_version }}</span>
                <span class="opacity-70">{{ $product->published_at?->diffForHumans() }}</span>
            @else
                <span class="opacity-70">{{ __('showroom::showroom.not_published') }}</span>
            @endif
        </span>
    </div>

    @if ($transitions !== [])
        @showroomCan('update', $product)
        <form method="POST" action="{{ route('atrium.showroom.products.transition', $product) }}" class="mt-4 flex flex-wrap items-start gap-3" data-testid="transition-form">
            @csrf
            <x-atrium::form.input name="comment" :label="__('showroom::showroom.comment')" wrapper="w-72" />
            <x-atrium::form.actions>
                @foreach ($transitions as $transition)
                    <x-atrium::icon-button :icon="$transitionIcons[$transition->value]" :label="__('showroom::showroom.transition_'.$transition->value)" :variant="$transition->value === 'reject' || $transition->value === 'archive' ? 'outline' : 'primary'" type="submit" name="transition" :value="$transition->value" data-testid="transition-{{ $transition->value }}" />
                @endforeach
            </x-atrium::form.actions>
        </form>
        @endshowroomCan
    @endif
</x-atrium::card>

<x-atrium::card :title="__('showroom::showroom.completeness')">
    @if ($product->completeness->isEmpty())
        <x-atrium::empty-state :title="__('showroom::showroom.no_completeness')" />
    @else
        <x-atrium::table compact>
            <x-slot:head>
                <x-atrium::table.row>
                    <x-atrium::table.cell heading>{{ __('showroom::showroom.channel') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('showroom::showroom.locale') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>%</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('showroom::showroom.missing') }}</x-atrium::table.cell>
                </x-atrium::table.row>
            </x-slot:head>

            @foreach ($product->completeness->sortBy(fn ($score) => $score->channel->code.'/'.$score->locale->code) as $score)
                <x-atrium::table.row data-completeness="{{ $score->channel->code }}/{{ $score->locale->code }}">
                    <x-atrium::table.cell class="font-mono text-xs">{{ $score->channel->code }}</x-atrium::table.cell>
                    <x-atrium::table.cell class="font-mono text-xs">{{ $score->locale->code }}</x-atrium::table.cell>
                    <x-atrium::table.cell>
                        <x-atrium::badge :variant="$score->ratio === 100 ? 'success' : ($score->ratio >= 50 ? 'warning' : 'danger')">{{ $score->ratio }}%</x-atrium::badge>
                    </x-atrium::table.cell>
                    <x-atrium::table.cell class="font-mono text-xs">{{ implode(', ', $score->missing_attributes ?? []) ?: __('showroom::showroom.none') }}</x-atrium::table.cell>
                </x-atrium::table.row>
            @endforeach
        </x-atrium::table>
    @endif
</x-atrium::card>

<x-atrium::card :title="__('showroom::showroom.history')">
    @if ($history->isEmpty())
        <x-atrium::empty-state :title="__('showroom::showroom.no_history')" />
    @else
        <ol class="flex flex-col gap-3" data-testid="history">
            @foreach ($history as $version)
                <li class="flex flex-wrap items-start justify-between gap-3 border-b border-outline pb-2 text-sm last:border-0 dark:border-outline-dark">
                    <div>
                        <p>
                            <span class="font-mono">v{{ $version->version }}</span>
                            <x-atrium::badge>{{ $version->action }}</x-atrium::badge>
                            <span class="opacity-70">{{ $version->created_at?->diffForHumans() }}</span>
                            @if ($version->author_id)
                                <span class="opacity-70">{{ __('showroom::showroom.by', ['author' => $version->author?->getAttribute('name') ?? $version->author_id]) }}</span>
                            @endif
                        </p>
                        @if ($version->comment)
                            <p class="mt-1 italic">{{ $version->comment }}</p>
                        @endif
                        @if ($version->changes)
                            <p class="mt-1 font-mono text-xs opacity-70">{{ implode(', ', array_keys($version->changes)) }}</p>
                        @endif
                    </div>

                    @unless ($loop->first || ! ScreenAccess::allows('update', $product))
                        <form method="POST" action="{{ route('atrium.showroom.products.revert', $product) }}">
                            @csrf
                            <input type="hidden" name="version" value="{{ $version->version }}">
                            <x-atrium::icon-button icon="arrow-uturn-left" :label="__('showroom::showroom.revert')" variant="ghost" size="sm" type="submit" data-testid="revert-{{ $version->version }}" />
                        </form>
                    @endunless
                </li>
            @endforeach
        </ol>
    @endif
</x-atrium::card>
