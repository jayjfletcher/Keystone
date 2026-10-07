<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Family\Actions\DeleteFamilyAction;

final class DeleteFamilyRequest extends FamilyRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->family());
    }

    public function rules(): array
    {
        return DeleteFamilyAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteFamilyAction::class)->execute($this->family());

        return new JsonResponse(null, 204);
    }
}
