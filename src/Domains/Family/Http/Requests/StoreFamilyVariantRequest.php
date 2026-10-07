<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\CreateFamilyVariantAction;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;

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
