<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Category\Events\CategoryUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Category\Events\CategoryUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;
use RefactorCircus\Keystone\Support\Concerns\MovesInTree;

final class UpdateCategoryAction
{
    use MovesInTree;

    /**
     * Sending `parent` moves the category with its whole branch — within its
     * tree, into another, or to the root as a tree of its own.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
            'parent' => ['sometimes', 'nullable', 'string', 'exists:keystone_categories,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function __construct(private readonly ProductIndex $index) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(CategoryModel $category, array $data): CategoryModel
    {
        CategoryUpdatingActionEvent::dispatch($category, $data);

        $result = $this->perform($category, $data);

        CategoryUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(CategoryModel $category, array $data): CategoryModel
    {
        return DB::transaction(function () use ($category, $data): CategoryModel {
            if (array_key_exists('labels', $data)) {
                $category->labels = is_array($data['labels']) ? $data['labels'] : [];
            }

            if (array_key_exists('sort_order', $data)) {
                $category->sort_order = (int) $data['sort_order'];
            }

            $category->save();

            if (array_key_exists('parent', $data)) {
                $parent = is_string($data['parent']) ? CategoryModel::query()->where('code', $data['parent'])->firstOrFail() : null;

                if ($parent?->id !== $category->parent_id) {
                    if ($parent !== null && $category->contains($parent)) {
                        throw ValidationException::withMessages([
                            'parent' => sprintf('Category "%s" cannot move under itself or its own descendant "%s".', $category->code, $parent->code),
                        ]);
                    }

                    $this->place($category, $parent);

                    // Products beneath now sit in a different branch.
                    $this->index->queue($this->index->productIdsInCategories($category));
                }
            }

            return $category->load('parent');
        });
    }
}
