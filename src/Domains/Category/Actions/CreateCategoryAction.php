<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Actions;

use Illuminate\Support\Facades\DB;
use RefactorCircus\Keystone\Domains\Category\Events\CategoryCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Category\Events\CategoryCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Support\Concerns\MovesInTree;

final class CreateCategoryAction
{
    use MovesInTree;

    /**
     * A category without a parent starts a new tree.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9_]*$/', 'unique:keystone_categories,code'],
            'parent' => ['sometimes', 'nullable', 'string', 'exists:keystone_categories,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): CategoryModel
    {
        CategoryCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        CategoryCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): CategoryModel
    {
        return DB::transaction(function () use ($data): CategoryModel {
            $parent = is_string($data['parent'] ?? null) ? CategoryModel::query()->where('code', $data['parent'])->firstOrFail() : null;

            $category = CategoryModel::query()->create([
                'code' => $data['code'],
                'labels' => $data['labels'] ?? [],
                'sort_order' => $data['sort_order'] ?? 0,
                'path' => '',
            ]);

            $this->place($category, $parent);

            return $category->load('parent');
        });
    }
}
