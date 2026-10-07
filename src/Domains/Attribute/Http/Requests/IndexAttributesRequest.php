<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributesAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeResource;

final class IndexAttributesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AttributeModel::class);
    }

    public function rules(): array
    {
        return ListAttributesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $attributes = app(ListAttributesAction::class)->execute($this->validated());

        return AttributeResource::collection($attributes)->response();
    }
}
