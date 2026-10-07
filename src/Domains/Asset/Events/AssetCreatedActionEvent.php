<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

/**
 * An asset was created.
 */
final class AssetCreatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AssetModel $asset,
    ) {}
}
