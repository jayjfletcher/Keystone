<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

/**
 * The Asset `updated` Eloquent event.
 */
final class AssetUpdatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AssetModel $asset) {}

    public function model(): Model
    {
        return $this->asset;
    }

    public function hook(): string
    {
        return 'updated';
    }
}
