<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributeGroupsAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

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
