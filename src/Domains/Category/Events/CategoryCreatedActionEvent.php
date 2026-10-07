<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

/**
 * A category was created.
 */
final class CategoryCreatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CategoryModel $category,
    ) {}
}
