<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\ListFamiliesAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyResource;

final class IndexFamiliesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', FamilyModel::class);
    }

    public function rules(): array
    {
        return ListFamiliesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $families = app(ListFamiliesAction::class)->execute($this->validated());

        return FamilyResource::collection($families)->response();
    }
}
