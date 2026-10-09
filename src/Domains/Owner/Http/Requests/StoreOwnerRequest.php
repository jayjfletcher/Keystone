<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Actions\CreateOwnerAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerResource;

final class StoreOwnerRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', OwnerModel::class);
    }

    public function rules(): array
    {
        return CreateOwnerAction::rules();
    }

    public function persist(): JsonResponse
    {
        $owner = app(CreateOwnerAction::class)->execute($this->validated());

        return (new OwnerResource($owner))->response()->setStatusCode(201);
    }
}
