<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributeOptionsAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeOptionResource;

final class IndexAttributeOptionsRequest extends AttributeRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->catalogAttribute()) && $this->allows('viewAny', AttributeOptionModel::class);
    }

    public function rules(): array
    {
        return ListAttributeOptionsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $options = app(ListAttributeOptionsAction::class)->execute($this->catalogAttribute(), $this->validated());

        return AttributeOptionResource::collection($options)->response();
    }
}
