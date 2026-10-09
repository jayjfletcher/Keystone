<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

/**
 * An owner type is about to be updated.
 */
final class OwnerTypeUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public OwnerTypeModel $ownerType,
        public array $data,
    ) {}
}
