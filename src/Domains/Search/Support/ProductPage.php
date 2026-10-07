<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * One page of product search results, with the facets the engine counted.
 *
 * @extends LengthAwarePaginator<int, ProductModel>
 */
final class ProductPage extends LengthAwarePaginator
{
    /**
     * @var array<string, array<string, int>>
     */
    public array $facets = [];
}
