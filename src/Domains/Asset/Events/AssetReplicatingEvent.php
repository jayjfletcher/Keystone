<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;

/**
 * The Asset `replicating` Eloquent event.
 */
final class AssetReplicatingEvent implements ModelLifecycleEvent
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
        return 'replicating';
    }
}
