<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Attribute\Actions\CreateAttributeGroupAction;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Showroom\Domains\Attribute\Resources\AttributeGroupResource;

final class CreateAttributeGroupMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeGroupModel::class);
    }

    protected function rules(): array
    {
        return CreateAttributeGroupAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $group = app(CreateAttributeGroupAction::class)->execute($validated);

        return Response::structured((new AttributeGroupResource($group))->resolve());
    }
}
