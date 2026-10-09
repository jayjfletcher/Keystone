<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Workflow\Models\VersionModel;

/**
 * The Version `replicating` Eloquent event.
 */
final class VersionReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public VersionModel $version) {}

    public function model(): Model
    {
        return $this->version;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
