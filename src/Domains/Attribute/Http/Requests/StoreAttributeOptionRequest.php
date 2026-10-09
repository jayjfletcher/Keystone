<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeOptionAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeOptionResource;

final class StoreAttributeOptionRequest extends AttributeRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->catalogAttribute()) && $this->allows('create', AttributeOptionModel::class);
    }

    public function rules(): array
    {
        return CreateAttributeOptionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $option = app(CreateAttributeOptionAction::class)->execute($this->catalogAttribute(), $this->validated());

        return (new AttributeOptionResource($option))->response()->setStatusCode(201);
    }
}
