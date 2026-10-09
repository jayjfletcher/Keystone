<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Family\Actions\CreateFamilyVariantAction;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Domains\Family\Resources\FamilyVariantResource;

final class StoreFamilyVariantRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', FamilyVariantModel::class);
    }

    public function rules(): array
    {
        return CreateFamilyVariantAction::rules();
    }

    public function persist(): JsonResponse
    {
        $familyVariant = app(CreateFamilyVariantAction::class)->execute($this->validated());

        return (new FamilyVariantResource($familyVariant))->response()->setStatusCode(201);
    }
}
