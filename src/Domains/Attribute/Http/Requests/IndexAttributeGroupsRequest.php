<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributeGroupsAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

final class IndexAttributeGroupsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AttributeGroupModel::class);
    }

    public function rules(): array
    {
        return ListAttributeGroupsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $groups = app(ListAttributeGroupsAction::class)->execute($this->validated());

        return AttributeGroupResource::collection($groups)->response();
    }
}
