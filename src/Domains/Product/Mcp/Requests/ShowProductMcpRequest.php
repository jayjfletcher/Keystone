<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Requests;

use JayI\Keystone\Domains\Product\Actions\ShowProductAction;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowProductMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    protected function rules(): array
    {
        return ShowProductAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $product = app(ShowProductAction::class)->execute($this->product(), $validated);

        return Response::structured((new ProductResource($product))->resolve());
    }
}
