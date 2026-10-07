<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

/**
 * A category is about to be deleted.
 */
final class CategoryDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CategoryModel $category,
    ) {}
}
