<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\ListFamilyVariantsAction;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;

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
