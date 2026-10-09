<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

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
