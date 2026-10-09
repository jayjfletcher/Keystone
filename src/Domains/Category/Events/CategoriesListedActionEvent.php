<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

/**
 * Categories were listed.
 */
final class CategoriesListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, CategoryModel>  $categories
     */
    public function __construct(
        public CursorPaginator $categories,
    ) {}
}
