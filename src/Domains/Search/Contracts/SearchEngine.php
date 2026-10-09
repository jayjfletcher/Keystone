<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Search\Contracts;

use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Search\Data\ProductQuery;
use RefactorCircus\Keystone\Domains\Search\Data\SearchResults;

/**
 * Where products are searched. Chosen with `keystone.search.engine`, or bind
 * your own implementation of this contract.
 */
interface SearchEngine
{
    /**
     * Whether the engine keeps an index of its own that writes must feed.
     * The database engine reads the products table and needs none.
     */
    public function maintainsIndex(): bool;

    /**
     * Add or replace products in the index. Each product arrives with its
     * family and parent chain loaded.
     *
     * @param  iterable<int, ProductModel>  $products
     */
    public function index(iterable $products): void;

    /**
     * Remove products from the index by id.
     *
     * @param  array<int, string>  $ids
     */
    public function remove(array $ids): void;

    /**
     * Find product ids in the order the query asks for.
     */
    public function search(ProductQuery $query): SearchResults;

    /**
     * Drop and recreate the index, empty.
     */
    public function reset(): void;
}
