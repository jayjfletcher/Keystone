<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\UpdateAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

final class UpdateAttributeRequest extends AttributeRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->catalogAttribute());
    }

    public function rules(): array
    {
        return UpdateAttributeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $attribute = app(UpdateAttributeAction::class)->execute($this->catalogAttribute(), $this->validated());

        return (new AttributeResource($attribute))->response();
    }
}
