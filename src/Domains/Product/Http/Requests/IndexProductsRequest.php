<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Product\Actions\ListProductsAction;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Product\Resources\ProductResource;

final class IndexProductsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModel::class);
    }

    public function rules(): array
    {
        return ListProductsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $products = app(ListProductsAction::class)->execute($this->validated());

        return ProductResource::collection($products)->additional(['facets' => (object) $products->facets])->response();
    }
}
