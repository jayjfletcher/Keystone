<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributesAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

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
