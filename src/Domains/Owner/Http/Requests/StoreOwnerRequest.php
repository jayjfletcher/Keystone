<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\CreateOwnerAction;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerResource;

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
