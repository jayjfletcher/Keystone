<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\DeleteAttributeOptionAction;

final class DeleteAttributeOptionRequest extends AttributeOptionRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->option());
    }

    public function rules(): array
    {
        return DeleteAttributeOptionAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteAttributeOptionAction::class)->execute($this->option());

        return new JsonResponse(null, 204);
    }
}
