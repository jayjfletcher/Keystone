<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Impex\Flows;

use RefactorCircus\Impex\Domains\Flow\Support\Flow;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Impex\Actions\DeliverFeed;
use RefactorCircus\Keystone\Impex\Actions\ExportProductPages;
use RefactorCircus\Keystone\Impex\Actions\JoinProductExport;

/**
 * `keystone:feed:{name}` — a syndication feed from `keystone.impex.feeds`:
 * a channel's published products, in its locales and category tree, to a
 * file asset, and optionally delivered over HTTP.
 *
 * Every feed has its own slug, so Impex's scheduler can run it by name.
 */
final class FeedFlow extends Flow
{
    public const string PREFIX = 'keystone:feed:';

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $run = $this->context()->run;
        $name = substr($run->flow, strlen(self::PREFIX));

        $this->tag('keystone', 'feed');
        $this->tag('feed', $name);

        // Recorded once, so a config change mid-run cannot shift the replay.
        /** @var array{channel?: string, format?: string, url?: string|null, ledger_channel?: string, deliver_through?: string|null} $feed */
        $feed = $this->sideEffect('feed', fn (): array => (array) config('keystone.impex.feeds.'.$name, []));

        $query = $this->sideEffect('query', function () use ($feed): array {
            $channel = ChannelModel::query()->with(['locales', 'categoryTree'])->where('code', $feed['channel'] ?? '')->firstOrFail();

            return array_filter([
                'scope' => $channel->code,
                'locales' => $channel->locales->pluck('code')->all(),
                'category' => $channel->categoryTree?->code,
            ], fn (mixed $value): bool => $value !== null);
        });

        $format = $feed['format'] ?? 'jsonl';

        $pages = $this->action(ExportProductPages::class, $query, $run->id, true)->run();

        $export = $this->action(
            JoinProductExport::class,
            $run->id,
            (int) $pages['parts'],
            (int) $pages['count'],
            $format,
            'feed-'.$name.'-'.strtolower($run->id),
        )->run();

        $url = is_string($feed['url'] ?? null) ? $feed['url'] : '';
        $through = is_string($feed['deliver_through'] ?? null) ? $feed['deliver_through'] : null;

        if ($url !== '' || $through !== null) {
            $export['delivery'] = $this->action(DeliverFeed::class, $export['asset'], $url, $feed['ledger_channel'] ?? 'keystone-feeds', $run->id, $through)
                ->tries(3)
                ->run();
        }

        return $export;
    }
}
