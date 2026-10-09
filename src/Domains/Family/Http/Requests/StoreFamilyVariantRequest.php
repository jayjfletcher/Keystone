<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\CreateFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyVariantResource;

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
