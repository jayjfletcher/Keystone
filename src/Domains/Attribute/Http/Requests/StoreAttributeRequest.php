<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeResource;

final class StoreAttributeRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeModel::class);
    }

    public function rules(): array
    {
        return CreateAttributeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $attribute = app(CreateAttributeAction::class)->execute($this->validated());

        return (new AttributeResource($attribute))->response()->setStatusCode(201);
    }
}
