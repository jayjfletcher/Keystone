<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Owner\Actions\DeleteOwnerTypeAction;

final class DeleteOwnerTypeRequest extends OwnerTypeRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->ownerType());
    }

    public function rules(): array
    {
        return DeleteOwnerTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteOwnerTypeAction::class)->execute($this->ownerType());

        return new JsonResponse(null, 204);
    }
}
