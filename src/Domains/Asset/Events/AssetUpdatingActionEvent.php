<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

/**
 * An asset is about to be updated.
 */
final class AssetUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public AssetModel $asset,
        public array $data,
    ) {}
}
