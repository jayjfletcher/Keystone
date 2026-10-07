<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

final class StoreAttributeGroupRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeGroupModel::class);
    }

    public function rules(): array
    {
        return CreateAttributeGroupAction::rules();
    }

    public function persist(): JsonResponse
    {
        $group = app(CreateAttributeGroupAction::class)->execute($this->validated());

        return (new AttributeGroupResource($group))->response()->setStatusCode(201);
    }
}
