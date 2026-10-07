<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeOptionAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeOptionResource;

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
