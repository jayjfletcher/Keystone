<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\ListOwnerTypesAction;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerTypeResource;
use Laravel\Mcp\ResponseFactory;

final class ListOwnerTypesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', OwnerTypeModel::class);
    }

    protected function rules(): array
    {
        return ListOwnerTypesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $ownerTypes = app(ListOwnerTypesAction::class)->execute($validated);

        return $this->structuredCollection(
            OwnerTypeResource::collection($ownerTypes)->resolve(),
            ['next_cursor' => $ownerTypes->nextCursor()?->encode()],
        );
    }
}
