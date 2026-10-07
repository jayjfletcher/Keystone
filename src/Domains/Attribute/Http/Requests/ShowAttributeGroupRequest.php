<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Actions\ShowAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

final class ShowAttributeGroupRequest extends AttributeGroupRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->group());
    }

    public function rules(): array
    {
        return ShowAttributeGroupAction::rules();
    }

    public function persist(): JsonResponse
    {
        $group = app(ShowAttributeGroupAction::class)->execute($this->group());

        return (new AttributeGroupResource($group))->response();
    }
}
