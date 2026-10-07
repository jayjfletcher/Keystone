<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

/**
 * The Family `creating` Eloquent event.
 */
final class FamilyCreatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public FamilyModel $family) {}

    public function model(): Model
    {
        return $this->family;
    }

    public function hook(): string
    {
        return 'creating';
    }
}
