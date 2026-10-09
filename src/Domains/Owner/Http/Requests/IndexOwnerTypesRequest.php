<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Actions\ListOwnerTypesAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerTypeResource;

final class IndexOwnerTypesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', OwnerTypeModel::class);
    }

    public function rules(): array
    {
        return ListOwnerTypesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $ownerTypes = app(ListOwnerTypesAction::class)->execute($this->validated());

        return OwnerTypeResource::collection($ownerTypes)->response();
    }
}
