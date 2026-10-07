<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\ListOwnerTypesAction;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerTypeResource;

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
