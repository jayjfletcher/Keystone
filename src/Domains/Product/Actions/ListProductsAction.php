<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Actions;

use Illuminate\Validation\Rule;
use JayI\Keystone\Domains\Attribute\Data\ValueFilter;
use JayI\Keystone\Domains\Product\Enums\ProductStatus;
use JayI\Keystone\Domains\Product\Events\ProductsListedActionEvent;
use JayI\Keystone\Domains\Product\Events\ProductsListingActionEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Data\Filter;
use JayI\Keystone\Domains\Search\Data\ProductQuery;
use JayI\Keystone\Domains\Search\Support\ProductPage;

/**
 * Lists and searches products through the configured search engine.
 */
final class ListProductsAction
{
    public function __construct(private readonly SearchEngine $engine) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $sortable = [];

        foreach (ProductQuery::SORTABLE as $field) {
            $sortable[] = $field;
            $sortable[] = '-'.$field;
        }

        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'family' => ['sometimes', 'nullable', 'string', 'max:100'],
            'enabled' => ['sometimes', 'nullable', 'boolean'],
            'parent' => ['sometimes', 'nullable', 'string', 'max:191'],
            // Owned by this owner or anything beneath it.
            'owner' => ['sometimes', 'nullable', 'string', 'max:191'],
            // In this category or any category beneath it.
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', Rule::enum(ProductStatus::class)],
            'published' => ['sometimes', 'nullable', 'boolean'],
            // At least `min` percent complete on a channel, in one locale or all of its locales.
            'complete' => ['sometimes', 'nullable', 'array'],
            'complete.scope' => ['required_with:complete', 'string', 'exists:keystone_channels,code'],
            'complete.locale' => ['sometimes', 'nullable', 'string', 'exists:keystone_locales,code'],
            'complete.min' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'filters' => ['sometimes', 'array', 'list', 'max:20'],
            'filters.*' => ['array'],
            'filters.*.attribute' => ['required', 'string', 'max:100'],
            'filters.*.operator' => ['required', 'string', Rule::in(Filter::OPERATORS)],
            'filters.*.value' => ['nullable'],
            'filters.*.locale' => ['sometimes', 'nullable', 'string', 'max:20'],
            'filters.*.scope' => ['sometimes', 'nullable', 'string', 'max:100'],
            'facets' => ['sometimes', 'array', 'list', 'max:10'],
            'facets.*' => ['string', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in($sortable)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
            // Return only one channel's and some locales' values.
        ] + ValueFilter::rules();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters = []): ProductPage
    {
        ProductsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        ProductsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function perform(array $filters): ProductPage
    {
        $query = ProductQuery::fromArray($filters);
        $results = $this->engine->search($query);

        // The engine decides the order; the database supplies the records.
        $products = ProductModel::query()
            ->with(['family.labelAttribute', 'assets', 'parent.assets', 'parent.parent.assets', 'completeness.channel', 'completeness.locale'])
            ->whereKey($results->ids)
            ->get()
            ->sortBy(fn (ProductModel $product): int|false => array_search($product->id, $results->ids, true))
            ->values()
            ->each(fn (ProductModel $product): ProductModel => $product->useValueFilter(ValueFilter::fromArray($filters)));

        $page = new ProductPage($products, $results->total, $query->perPage, $query->page, [
            'path' => request()->url(),
        ]);

        $page->facets = $results->facets;

        return $page;
    }
}
