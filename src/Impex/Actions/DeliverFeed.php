<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex\Actions;

use Illuminate\Support\Facades\Storage;
use JayI\Impex\Impex;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use RuntimeException;

/**
 * Sends a feed file to its destination through Impex's recorded HTTP
 * client, so the delivery lands in the ledger with its run and response.
 */
final class DeliverFeed
{
    public function __construct(private readonly Impex $impex) {}

    /**
     * @return array{status: int}
     */
    public function execute(string $asset, string $url, string $channel, string $runId): array
    {
        $file = AssetModel::query()->where('code', $asset)->firstOrFail();
        $stream = Storage::disk($file->disk)->readStream($file->path);

        if (! is_resource($stream)) {
            throw new RuntimeException(sprintf('The file of asset "%s" cannot be read.', $asset));
        }

        // The HTTP client takes the stream over and closes it.
        $response = $this->impex->http($channel, $runId)
            ->withHeaders(['Content-Type' => $file->mime_type ?? 'application/octet-stream'])
            ->send('POST', $url, ['body' => $stream]);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf('The feed destination answered with status %d.', $response->status()));
        }

        return ['status' => $response->status()];
    }
}
