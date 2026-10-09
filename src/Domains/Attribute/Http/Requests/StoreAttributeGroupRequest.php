<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeGroupAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

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
