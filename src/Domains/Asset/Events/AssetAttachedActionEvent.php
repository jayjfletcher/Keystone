<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

/**
 * An asset was linked to a record.
 */
final class AssetAttachedActionEvent implements ActionFinishedEvent
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
