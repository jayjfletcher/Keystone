<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Owner\Actions\DeleteOwnerAction;

final class DeleteOwnerRequest extends OwnerRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->owner());
    }

    public function rules(): array
    {
        return DeleteOwnerAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteOwnerAction::class)->execute($this->owner());

        return new JsonResponse(null, 204);
    }
}
