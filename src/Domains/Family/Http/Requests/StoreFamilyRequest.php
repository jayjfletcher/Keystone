<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\CreateFamilyAction;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Resources\FamilyResource;

final class StoreFamilyRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', FamilyModel::class);
    }

    public function rules(): array
    {
        return CreateFamilyAction::rules();
    }

    public function persist(): JsonResponse
    {
        $family = app(CreateFamilyAction::class)->execute($this->validated());

        return (new FamilyResource($family))->response()->setStatusCode(201);
    }
}
