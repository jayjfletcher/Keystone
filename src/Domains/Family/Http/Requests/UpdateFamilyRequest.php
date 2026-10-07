<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Family\Actions\UpdateFamilyAction;
use JayI\Keystone\Domains\Family\Resources\FamilyResource;

final class UpdateFamilyRequest extends FamilyRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->family());
    }

    public function rules(): array
    {
        return UpdateFamilyAction::rules();
    }

    public function persist(): JsonResponse
    {
        $family = app(UpdateFamilyAction::class)->execute($this->family(), $this->validated());

        return (new FamilyResource($family))->response();
    }
}
