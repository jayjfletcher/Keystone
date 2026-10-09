<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Actions\ListOwnerTypesAction;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerTypeResource;

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
