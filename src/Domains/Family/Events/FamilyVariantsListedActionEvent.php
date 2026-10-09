<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

/**
 * Family variants were listed.
 */
final class FamilyVariantsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, FamilyVariantModel>  $familyVariants
     */
    public function __construct(
        public CursorPaginator $familyVariants,
    ) {}
}
