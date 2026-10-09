<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

final class CreateAttributeMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeModel::class);
    }

    protected function rules(): array
    {
        return CreateAttributeAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $attribute = app(CreateAttributeAction::class)->execute($validated);

        return Response::structured((new AttributeResource($attribute))->resolve());
    }
}
