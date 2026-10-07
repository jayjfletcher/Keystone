<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Category\Events\CategoryDeletedActionEvent;
use JayI\Keystone\Domains\Category\Events\CategoryDeletingActionEvent;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;
use JayI\Keystone\Exceptions\ModelInUseException;

final class DeleteCategoryAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly ProductIndex $index) {}

    public function execute(CategoryModel $category): CategoryModel
    {
        CategoryDeletingActionEvent::dispatch($category);

        $result = $this->perform($category);

        CategoryDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * A category with children is refused: trees are pruned leaf first. A
     * leaf's product assignments go with it, as in any PIM.
     */
    private function perform(CategoryModel $category): CategoryModel
    {
        $children = $category->children()->count();

        if ($children > 0) {
            throw ModelInUseException::categoryHasChildren($category->code, $children);
        }

        /** @var array<int, string> $channels */
        $channels = ChannelModel::query()->where('category_tree_id', $category->id)->orderBy('code')->pluck('code')->all();

        if ($channels !== []) {
            throw ModelInUseException::categoryTreeOfChannels($category->code, $channels);
        }

        return DB::transaction(function () use ($category): CategoryModel {
            $products = $this->index->productIdsInCategories($category);

            $category->delete();

            $this->index->queue($products);

            return $category;
        });
    }
}
