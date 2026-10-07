<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Workflow\Models\CompletenessModel;

/**
 * The Completeness `saving` Eloquent event.
 */
final class CompletenessSavingEvent implements ModelLifecycleEvent
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
        return 'saving';
    }
}
