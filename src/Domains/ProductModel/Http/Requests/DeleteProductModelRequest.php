<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\ProductModel\Actions\DeleteProductModelAction;

final class DeleteProductModelRequest extends ProductModelRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->productModel());
    }

    public function rules(): array
    {
        return DeleteProductModelAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteProductModelAction::class)->execute($this->productModel());

        return new JsonResponse(null, 204);
    }
}
