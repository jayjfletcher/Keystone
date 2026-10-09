<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\DeleteAttributeAction;

final class DeleteAttributeRequest extends AttributeRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->catalogAttribute());
    }

    public function rules(): array
    {
        return DeleteAttributeAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteAttributeAction::class)->execute($this->catalogAttribute());

        return new JsonResponse(null, 204);
    }
}
