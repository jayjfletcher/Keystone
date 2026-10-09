<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Family\Actions\ShowFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyVariantResource;

final class ShowFamilyVariantRequest extends FamilyVariantRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->familyVariant());
    }

    public function rules(): array
    {
        return ShowFamilyVariantAction::rules();
    }

    public function persist(): JsonResponse
    {
        $familyVariant = app(ShowFamilyVariantAction::class)->execute($this->familyVariant());

        return (new FamilyVariantResource($familyVariant))->response();
    }
}
