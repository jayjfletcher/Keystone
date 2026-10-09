<x-atrium::layout :title="__('keystone::keystone.transfers')">
    <x-atrium::page-header :title="__('keystone::keystone.transfers')" :description="__('keystone::keystone.transfers_description')" />

    <div class="mt-5 flex flex-col gap-4">
        <x-atrium::flash />

        @unless ($active)
            <x-atrium::alert variant="warning">{{ __('keystone::keystone.impex_missing') }}</x-atrium::alert>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @keystoneCan('create', \RefactorCircus\Keystone\Domains\Product\Models\ProductModel::class)
                <x-atrium::card data-testid="import-card" :title="__('keystone::keystone.import')">
                    <form method="POST" action="{{ route('atrium.keystone.transfers.import') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
                        @csrf
                        <x-atrium::form.file name="file" :label="__('keystone::keystone.import_file')" :hint="__('keystone::keystone.import_file_hint')" required />
                        <x-atrium::form.select name="mode" :label="__('keystone::keystone.mode')" :options="['upsert' => __('keystone::keystone.mode_upsert'), 'create' => __('keystone::keystone.mode_create'), 'update' => __('keystone::keystone.mode_update')]" selected="upsert" wrapper="w-56" />
                        <div>
                            <x-atrium::icon-button icon="arrow-up-tray" :label="__('keystone::keystone.start_import')" variant="primary" type="submit" data-testid="start-import" />
                        </div>
                    </form>
                </x-atrium::card>
                @endkeystoneCan

                @keystoneCan('viewAny', \RefactorCircus\Keystone\Domains\Product\Models\ProductModel::class)
                <x-atrium::card data-testid="export-card" :title="__('keystone::keystone.export')">
                    <form method="POST" action="{{ route('atrium.keystone.transfers.export') }}" class="flex flex-col gap-3">
                        @csrf
                        <div class="flex flex-wrap gap-3">
                            <x-atrium::form.select name="format" :label="__('keystone::keystone.format')" :options="['jsonl' => 'JSONL', 'csv' => 'CSV']" selected="jsonl" wrapper="w-32" />
                            <x-atrium::form.select name="family" :label="__('keystone::keystone.family')" :placeholder="__('keystone::keystone.all_families')" :options="$families" wrapper="w-44" />
                            <x-atrium::form.select name="category" :label="__('keystone::keystone.category')" :placeholder="__('keystone::keystone.none')" :options="$trees" wrapper="w-44" />
                            <x-atrium::form.select name="scope" :label="__('keystone::keystone.channel')" :placeholder="__('keystone::keystone.none')" :options="$channels" wrapper="w-44" />
                        </div>
                        <input type="hidden" name="published" value="0">
                        <x-atrium::form.checkbox name="published" :label="__('keystone::keystone.export_published')" />
                        <div>
                            <x-atrium::icon-button icon="arrow-down-tray" :label="__('keystone::keystone.start_export')" variant="primary" type="submit" data-testid="start-export" />
                        </div>
                    </form>
                </x-atrium::card>
                @endkeystoneCan
            </div>

            <x-atrium::card :title="__('keystone::keystone.recent_runs')">
                @if ($runs->isEmpty())
                    <x-atrium::empty-state :title="__('keystone::keystone.no_runs')" />
                @else
                    <x-atrium::table compact>
                        <x-slot:head>
                            <x-atrium::table.row>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.flow') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.status') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.result') }}</x-atrium::table.cell>
                                <x-atrium::table.cell heading>{{ __('keystone::keystone.updated') }}</x-atrium::table.cell>
                            </x-atrium::table.row>
                        </x-slot:head>

                        @foreach ($runs as ['run' => $run, 'result' => $result])
                            <x-atrium::table.row data-run="{{ $run->flow }}">
                                <x-atrium::table.cell class="font-mono text-xs">
                                    {{-- Linked only when Impex's dashboard would let the viewer open the run. --}}
                                    @if (Route::has('atrium.impex.runs.show') && class_exists(\RefactorCircus\Impex\Atrium\ScreenAccess::class) && \RefactorCircus\Impex\Atrium\ScreenAccess::allows('view', $run))
                                        <a class="underline-offset-2 hover:underline" href="{{ route('atrium.impex.runs.show', $run) }}">{{ $run->flow }}</a>
                                    @else
                                        {{ $run->flow }}
                                    @endif
                                </x-atrium::table.cell>
                                <x-atrium::table.cell><x-atrium::status-dot :variant="\RefactorCircus\Keystone\Atrium\Badges::forRun($run->status->value)" :label="$run->status->value" data-status="{{ $run->status->value }}" /></x-atrium::table.cell>
                                <x-atrium::table.cell class="text-xs">
                                    @if (is_array($result) && isset($result['asset']))
                                        <a class="font-mono underline-offset-2 hover:underline" href="{{ route('atrium.keystone.assets.show', $result['asset']) }}">{{ $result['asset'] }}</a>
                                        · {{ __('keystone::keystone.records', ['count' => $result['count'] ?? 0]) }}
                                    @elseif (is_array($result) && isset($result['total']))
                                        {{ __('keystone::keystone.import_counts', ['succeeded' => $result['succeeded'] ?? 0, 'failed' => $result['failed'] ?? 0, 'total' => $result['total']]) }}
                                    @elseif ($run->error)
                                        <span class="text-danger">{{ $run->error['message'] ?? '' }}</span>
                                    @endif
                                </x-atrium::table.cell>
                                <x-atrium::table.cell>{{ $run->updated_at?->diffForHumans() }}</x-atrium::table.cell>
                            </x-atrium::table.row>
                        @endforeach
                    </x-atrium::table>
                @endif
            </x-atrium::card>
        @endunless
    </div>
</x-atrium::layout>
