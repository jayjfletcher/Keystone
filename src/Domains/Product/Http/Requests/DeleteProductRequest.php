<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Product\Actions\DeleteProductAction;

final class DeleteProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->product());
    }

    public function rules(): array
    {
        return DeleteProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteProductAction::class)->execute($this->product());

        return new JsonResponse(null, 204);
    }
}
