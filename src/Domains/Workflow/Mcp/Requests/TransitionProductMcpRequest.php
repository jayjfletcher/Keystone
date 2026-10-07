<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Mcp\Requests;

use JayI\Keystone\Domains\Product\Mcp\Requests\ProductRequest;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use JayI\Keystone\Domains\Workflow\Actions\TransitionProductAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class TransitionProductMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    protected function rules(): array
    {
        return TransitionProductAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['product']);

        $product = app(TransitionProductAction::class)->execute($this->product(), $validated);

        return Response::structured((new ProductResource($product))->resolve());
    }
}
