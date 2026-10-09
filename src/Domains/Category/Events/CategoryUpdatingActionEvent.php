<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionStartingEvent;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;

/**
 * A category is about to be updated.
 */
final class CategoryUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public CategoryModel $category,
        public array $data,
    ) {}
}
