<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * The OwnerType `creating` Eloquent event.
 */
final class OwnerTypeCreatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public OwnerTypeModel $ownerType) {}

    public function model(): Model
    {
        return $this->ownerType;
    }

    public function hook(): string
    {
        return 'creating';
    }
}
