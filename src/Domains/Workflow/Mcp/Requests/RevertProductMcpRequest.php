<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Mcp\Requests;

use JayI\Keystone\Domains\Product\Mcp\Requests\ProductRequest;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use JayI\Keystone\Domains\Workflow\Actions\RevertProductAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class RevertProductMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    protected function rules(): array
    {
        return RevertProductAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['product']);

        $product = app(RevertProductAction::class)->execute($this->product(), $validated);

        return Response::structured((new ProductResource($product))->resolve());
    }
}
