<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Product\Actions\DeleteProductAction;

final class DeleteProductMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->product());
    }

    protected function rules(): array
    {
        return DeleteProductAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $product = app(DeleteProductAction::class)->execute($this->product());

        return Response::structured(['deleted' => true, 'identifier' => $product->identifier]);
    }
}
