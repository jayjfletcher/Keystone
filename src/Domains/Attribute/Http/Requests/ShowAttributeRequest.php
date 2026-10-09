<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ShowAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

final class ShowAttributeRequest extends AttributeRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->catalogAttribute());
    }

    public function rules(): array
    {
        return ShowAttributeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $attribute = app(ShowAttributeAction::class)->execute($this->catalogAttribute());

        return (new AttributeResource($attribute))->response();
    }
}
