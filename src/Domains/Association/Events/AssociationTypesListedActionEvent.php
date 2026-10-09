<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

/**
 * Association types were listed.
 */
final class AssociationTypesListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, AssociationTypeModel>  $associationTypes
     */
    public function __construct(
        public CursorPaginator $associationTypes,
    ) {}
}
