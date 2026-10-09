<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Actions\CreateOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerTypeResource;

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
