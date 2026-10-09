<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;

/**
 * The Asset `retrieved` Eloquent event.
 */
final class AssetRetrievedEvent implements ModelLifecycleEvent
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
        return 'retrieved';
    }
}
