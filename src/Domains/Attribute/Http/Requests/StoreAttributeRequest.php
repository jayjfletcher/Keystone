<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

final class StoreAttributeRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeModel::class);
    }

    public function rules(): array
    {
        return CreateAttributeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $attribute = app(CreateAttributeAction::class)->execute($this->validated());

        return (new AttributeResource($attribute))->response()->setStatusCode(201);
    }
}
