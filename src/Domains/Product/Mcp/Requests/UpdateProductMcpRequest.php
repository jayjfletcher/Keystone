<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Requests;

use JayI\Keystone\Domains\Product\Actions\UpdateProductAction;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateProductMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    protected function rules(): array
    {
        return UpdateProductAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['product']);

        $product = app(UpdateProductAction::class)->execute($this->product(), $validated);

        return Response::structured((new ProductResource($product))->resolve());
    }
}
