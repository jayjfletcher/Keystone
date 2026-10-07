<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Product\Actions\ListProductsAction;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use Laravel\Mcp\ResponseFactory;

final class ListProductsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModel::class);
    }

    protected function rules(): array
    {
        return ListProductsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $products = app(ListProductsAction::class)->execute($validated);

        return $this->structuredCollection(ProductResource::collection($products)->resolve(), [
            'total' => $products->total(),
            'page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'facets' => (object) $products->facets,
        ]);
    }
}
