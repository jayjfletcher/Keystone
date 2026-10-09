<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

/**
 * A category was updated.
 */
final class CategoryUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CategoryModel $category,
    ) {}
}
