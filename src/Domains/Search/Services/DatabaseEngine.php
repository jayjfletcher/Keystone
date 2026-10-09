<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Search\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use RefactorCircus\Showroom\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Attribute\Services\Values;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;
use RefactorCircus\Showroom\Domains\Search\Contracts\SearchEngine;
use RefactorCircus\Showroom\Domains\Search\Data\Filter;
use RefactorCircus\Showroom\Domains\Search\Data\ProductQuery;
use RefactorCircus\Showroom\Domains\Search\Data\SearchResults;
use RefactorCircus\Showroom\Domains\Search\Exceptions\UnsupportedSearchException;
use RefactorCircus\Showroom\Domains\Search\Support\SqlFragment;

/**
 * Searches the products table directly, with no index to keep.
 *
 * A variant's values live on up to three rows — itself, its product model,
 * and that model's parent — so each value condition is checked on all three.
 * Fine for thousands of products; use a search engine beyond that.
 */
final class DatabaseEngine implements SearchEngine
{
    /**
     * The `values` column of each level a value may live on.
     */
    private const array LEVELS = ['showroom_products.values', 'showroom_level1.values', 'showroom_level2.values'];

    public function maintainsIndex(): bool
    {
        return false;
    }

    public function index(iterable $products): void
    {
        //
    }

    public function remove(array $ids): void
    {
        //
    }

    public function reset(): void
    {
        //
    }

    public function search(ProductQuery $query): SearchResults
    {
        $builder = ProductModel::query()
            ->leftJoin('showroom_product_models as showroom_level1', 'showroom_level1.id', '=', 'showroom_products.parent_id')
            ->leftJoin('showroom_product_models as showroom_level2', 'showroom_level2.id', '=', 'showroom_level1.parent_id');

        if ($query->search !== null) {
            $term = '%'.$query->search.'%';

            $builder->where(function (Builder $builder) use ($term): void {
                $builder->where('showroom_products.identifier', 'like', $term);

                foreach (self::LEVELS as $column) {
                    $builder->orWhere(new SqlFragment($this->asText($column)), 'like', $term);
                }
            });
        }

        if ($query->family !== null) {
            $family = $query->family;

            $builder->whereHas('family', fn (Builder $families): Builder => $families->where('code', $family));
        }

        if ($query->enabled !== null) {
            $builder->where('showroom_products.enabled', $query->enabled);
        }

        if ($query->parent !== null) {
            $builder->where(fn (Builder $builder): Builder => $builder
                ->whereIn('showroom_level1.code', [$query->parent])
                ->orWhereIn('showroom_level2.code', [$query->parent]));
        }

        if ($query->owner !== null) {
            $owner = OwnerModel::query()->where('code', $query->owner)->first();
            $owners = $owner === null ? [] : OwnerModel::query()->subtreeOf($owner)->pluck('id')->all();

            $builder->where(fn (Builder $builder): Builder => $builder
                ->whereIn('showroom_products.owner_id', $owners)
                ->orWhereIn('showroom_level1.owner_id', $owners)
                ->orWhereIn('showroom_level2.owner_id', $owners));
        }

        if ($query->category !== null) {
            $category = CategoryModel::query()->where('code', $query->category)->first();
            $categories = $category === null ? [] : CategoryModel::query()->subtreeOf($category)->pluck('id')->all();

            $builder->where(function (Builder $builder) use ($categories): void {
                $builder->whereExists(fn (QueryBuilder $sub) => $sub->from('showroom_category_product')
                    ->whereColumn('showroom_category_product.product_id', 'showroom_products.id')
                    ->whereIn('showroom_category_product.category_id', $categories));

                foreach (['showroom_level1', 'showroom_level2'] as $level) {
                    $builder->orWhereExists(fn (QueryBuilder $sub) => $sub->from('showroom_category_product_model')
                        ->whereColumn('showroom_category_product_model.product_model_id', $level.'.id')
                        ->whereIn('showroom_category_product_model.category_id', $categories));
                }
            });
        }

        if ($query->status !== null) {
            $builder->where('showroom_products.status', $query->status);
        }

        if ($query->published !== null) {
            $query->published
                ? $builder->whereNotNull('showroom_products.published_version')
                : $builder->whereNull('showroom_products.published_version');
        }

        if ($query->updatedSince !== null) {
            $builder->where('showroom_products.changed_at', '>=', $query->updatedSince);
        }

        if ($query->complete !== null) {
            $complete = $query->complete;

            foreach ($complete->locales() ?: ['-'] as $locale) {
                $builder->whereExists(fn (QueryBuilder $sub) => $sub->from('showroom_product_completeness')
                    ->join('showroom_channels', 'showroom_channels.id', '=', 'showroom_product_completeness.channel_id')
                    ->join('showroom_locales', 'showroom_locales.id', '=', 'showroom_product_completeness.locale_id')
                    ->whereColumn('showroom_product_completeness.product_id', 'showroom_products.id')
                    ->where('showroom_channels.code', $complete->scope)
                    ->where('showroom_locales.code', $locale)
                    ->where('showroom_product_completeness.ratio', '>=', $complete->min));
            }
        }

        $types = $this->types($query->filters);

        foreach ($query->filters as $filter) {
            $this->applyFilter($builder, $filter, $types[$filter->attribute] ?? AttributeType::Text);
        }

        $total = (clone $builder)->count('showroom_products.id');

        /** @var array<int, string> $ids */
        $ids = $builder
            ->orderBy('showroom_products.'.$query->sort, $query->direction)
            ->orderBy('showroom_products.id')
            ->offset($query->offset())
            ->limit($query->perPage)
            ->pluck('showroom_products.id')
            ->all();

        return new SearchResults($ids, $total);
    }

    /**
     * @param  Builder<ProductModel>  $builder
     */
    private function applyFilter(Builder $builder, Filter $filter, AttributeType $type): void
    {
        $path = implode('->', [$filter->attribute, $filter->scope ?? Values::ALL_CHANNELS, $filter->locale ?? Values::ALL_LOCALES]);

        if ($type === AttributeType::Metric) {
            $path .= '->amount';
        }

        if ($type === AttributeType::Price) {
            throw UnsupportedSearchException::filter('database', $filter->attribute, $filter->operator);
        }

        $match = match ($filter->operator) {
            '=', '!=' => fn (QueryBuilder $level, string $column) => $this->equals($level, $column.'->'.$path, $type, $filter->value),
            'in', 'not_in' => fn (QueryBuilder $level, string $column) => $level->where(function (QueryBuilder $any) use ($column, $path, $type, $filter): void {
                foreach ($filter->values() as $value) {
                    $any->orWhere(fn (QueryBuilder $one) => $this->equals($one, $column.'->'.$path, $type, $value));
                }
            }),
            '>', '>=', '<', '<=' => fn (QueryBuilder $level, string $column) => $level->where(
                new SqlFragment($this->asNumber($column.'->'.$path)),
                $filter->operator,
                $filter->value,
            ),
            'empty', 'not_empty' => fn (QueryBuilder $level, string $column) => $level->whereNotNull($column.'->'.$path),
            default => throw UnsupportedSearchException::filter('database', $filter->attribute, $filter->operator),
        };

        // A value sits on exactly one level, so "any level matches" is exact,
        // and each level's clause is false, never null, when the value is
        // absent — which keeps the negated forms honest.
        $anyLevel = function (QueryBuilder $query) use ($match): void {
            foreach (self::LEVELS as $column) {
                $query->orWhere(fn (QueryBuilder $level) => $match($level->whereNotNull($column), $column));
            }
        };

        in_array($filter->operator, ['!=', 'not_in', 'empty'], true)
            ? $builder->getQuery()->whereNot($anyLevel)
            : $builder->getQuery()->where($anyLevel);
    }

    private function equals(QueryBuilder $query, string $path, AttributeType $type, mixed $value): QueryBuilder
    {
        if ($type === AttributeType::Multiselect) {
            return $query->whereJsonContains($path, $value);
        }

        if ($type === AttributeType::Boolean) {
            return $query->where($path, filter_var($value, FILTER_VALIDATE_BOOLEAN));
        }

        return $query->whereNotNull($path)->where($path, is_bool($value) ? $value : (string) $value);
    }

    /**
     * @param  array<int, Filter>  $filters
     * @return array<string, AttributeType>
     */
    private function types(array $filters): array
    {
        if ($filters === []) {
            return [];
        }

        return AttributeModel::query()
            ->whereIn('code', array_map(fn (Filter $filter): string => $filter->attribute, $filters))
            ->get(['code', 'type'])
            ->mapWithKeys(fn (AttributeModel $attribute): array => [$attribute->code => $attribute->type])
            ->all();
    }

    private function asText(string $column): string
    {
        $wrapped = $this->grammar()->wrap($column);

        return $this->driver() === 'pgsql' ? 'CAST('.$wrapped.' AS TEXT)' : $wrapped;
    }

    private function asNumber(string $path): string
    {
        $wrapped = $this->grammar()->wrap($path);

        return match ($this->driver()) {
            'sqlite' => 'CAST('.$wrapped.' AS REAL)',
            'pgsql' => 'CAST('.$wrapped.' AS NUMERIC)',
            default => 'CAST('.$wrapped.' AS DECIMAL(30,10))',
        };
    }

    private function grammar(): Grammar
    {
        return ProductModel::query()->getQuery()->getGrammar();
    }

    private function driver(): string
    {
        return (new ProductModel)->getConnection()->getDriverName();
    }
}
