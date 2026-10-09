<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\ListFamilyVariantsAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyVariantResource;

final class IndexFamilyVariantsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', FamilyVariantModel::class);
    }

    public function rules(): array
    {
        return ListFamilyVariantsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $familyVariants = app(ListFamilyVariantsAction::class)->execute($this->validated());

        return FamilyVariantResource::collection($familyVariants)->response();
    }
}
