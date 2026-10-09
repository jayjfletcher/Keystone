<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Showroom\Domains\Asset\Events\AssetsListedActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Events\AssetsListingActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;

final class ListAssetsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:191'],
            // A MIME type, or a family of them such as "image/*".
            'type' => ['sometimes', 'nullable', 'string', 'max:191'],
            'product' => ['sometimes', 'nullable', 'string', 'max:191'],
            'product_model' => ['sometimes', 'nullable', 'string', 'max:191'],
            'owner' => ['sometimes', 'nullable', 'string', 'max:191'],
            'role' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('showroom.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AssetModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        AssetsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        AssetsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AssetModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = AssetModel::query()->orderByDesc('created_at')->orderByDesc('id');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(fn (Builder $builder): Builder => $builder
                ->search($search)
                ->orWhere('filename', 'like', '%'.$search.'%'));
        }

        if (is_string($filters['type'] ?? null) && $filters['type'] !== '') {
            $type = $filters['type'];

            str_ends_with($type, '/*') || str_ends_with($type, '/')
                ? $query->where('mime_type', 'like', rtrim($type, '/*').'/%')
                : $query->where('mime_type', $type);
        }

        $role = is_string($filters['role'] ?? null) && $filters['role'] !== '' ? $filters['role'] : null;

        foreach (['product' => ['products', 'identifier'], 'product_model' => ['productModels', 'code'], 'owner' => ['owners', 'code']] as $filter => [$relation, $key]) {
            if (is_string($filters[$filter] ?? null) && $filters[$filter] !== '') {
                $target = $filters[$filter];

                $query->whereHas($relation, fn (Builder $linked): Builder => $linked
                    ->where($key, $target)
                    ->when($role !== null, fn (Builder $linked): Builder => $linked->where('showroom_asset_links.role', $role)));
            }
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('showroom.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
