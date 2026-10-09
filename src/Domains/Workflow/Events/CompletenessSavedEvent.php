<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Workflow\Models\CompletenessModel;

/**
 * The Completeness `saved` Eloquent event.
 */
final class CompletenessSavedEvent implements ModelLifecycleEvent
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
        return 'saved';
    }
}
