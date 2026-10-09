<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\UpdateAttributeGroupAction;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

final class UpdateAttributeGroupRequest extends AttributeGroupRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->group());
    }

    public function rules(): array
    {
        return UpdateAttributeGroupAction::rules();
    }

    public function persist(): JsonResponse
    {
        $group = app(UpdateAttributeGroupAction::class)->execute($this->group(), $this->validated());

        return (new AttributeGroupResource($group))->response();
    }
}
