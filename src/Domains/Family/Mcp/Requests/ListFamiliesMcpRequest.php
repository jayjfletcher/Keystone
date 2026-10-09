<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\ListFamiliesAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyResource;

final class ListFamiliesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', FamilyModel::class);
    }

    protected function rules(): array
    {
        return ListFamiliesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $families = app(ListFamiliesAction::class)->execute($validated);

        return $this->structuredCollection(
            FamilyResource::collection($families)->resolve(),
            ['next_cursor' => $families->nextCursor()?->encode()],
        );
    }
}
