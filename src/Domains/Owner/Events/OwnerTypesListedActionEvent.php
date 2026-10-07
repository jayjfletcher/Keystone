<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * Owner types were listed.
 */
final class OwnerTypesListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, OwnerTypeModel>  $ownerTypes
     */
    public function __construct(
        public CursorPaginator $ownerTypes,
    ) {}
}
