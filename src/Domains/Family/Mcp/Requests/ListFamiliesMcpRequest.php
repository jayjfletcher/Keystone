<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\ListFamiliesAction;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Resources\FamilyResource;
use Laravel\Mcp\ResponseFactory;

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
