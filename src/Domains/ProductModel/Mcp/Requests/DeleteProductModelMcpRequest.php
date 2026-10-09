<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\DeleteProductModelAction;

final class DeleteProductModelMcpRequest extends ProductModelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->productModel());
    }

    protected function rules(): array
    {
        return DeleteProductModelAction::rules() + [
            'product_model' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $productModel = app(DeleteProductModelAction::class)->execute($this->productModel());

        return Response::structured(['deleted' => true, 'code' => $productModel->code]);
    }
}
