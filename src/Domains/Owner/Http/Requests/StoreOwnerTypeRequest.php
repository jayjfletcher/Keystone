<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\CreateOwnerTypeAction;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerTypeResource;

final class StoreOwnerTypeRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', OwnerTypeModel::class);
    }

    public function rules(): array
    {
        return CreateOwnerTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $ownerType = app(CreateOwnerTypeAction::class)->execute($this->validated());

        return (new OwnerTypeResource($ownerType))->response()->setStatusCode(201);
    }
}
