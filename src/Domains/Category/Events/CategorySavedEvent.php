<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

/**
 * The Category `saved` Eloquent event.
 */
final class CategorySavedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CategoryModel $category) {}

    public function model(): Model
    {
        return $this->category;
    }

    public function hook(): string
    {
        return 'saved';
    }
}
