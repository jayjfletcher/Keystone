<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Actions;

use RefactorCircus\Keystone\Domains\Category\Events\CategoryShowingActionEvent;
use RefactorCircus\Keystone\Domains\Category\Events\CategoryShownActionEvent;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;

final class ShowCategoryAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(CategoryModel $category): CategoryModel
    {
        CategoryShowingActionEvent::dispatch($category);

        $result = $this->perform($category);

        CategoryShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(CategoryModel $category): CategoryModel
    {
        $category->load(['parent', 'children'])->loadCount(['products', 'productModels']);

        return $category->setRelation('chain', $category->chain());
    }
}
