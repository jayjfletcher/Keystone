<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Workflow\Models\CompletenessModel;

/**
 * The Completeness `replicating` Eloquent event.
 */
final class CompletenessReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CompletenessModel $completeness) {}

    public function model(): Model
    {
        return $this->completeness;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
