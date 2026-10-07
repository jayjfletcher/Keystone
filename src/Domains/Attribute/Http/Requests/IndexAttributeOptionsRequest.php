<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributeOptionsAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeOptionResource;

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
