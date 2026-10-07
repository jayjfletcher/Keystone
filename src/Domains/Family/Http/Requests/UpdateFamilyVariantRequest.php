<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Family\Actions\UpdateFamilyVariantAction;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;

final class UpdateFamilyVariantRequest extends FamilyVariantRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->familyVariant());
    }

    public function rules(): array
    {
        return UpdateFamilyVariantAction::rules();
    }

    public function persist(): JsonResponse
    {
        $familyVariant = app(UpdateFamilyVariantAction::class)->execute($this->familyVariant(), $this->validated());

        return (new FamilyVariantResource($familyVariant))->response();
    }
}
