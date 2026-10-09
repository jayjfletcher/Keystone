<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Asset\Actions\ListAssetsAction;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Asset\Resources\AssetResource;

final class ListAssetsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AssetModel::class);
    }

    protected function rules(): array
    {
        return ListAssetsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $assets = app(ListAssetsAction::class)->execute($validated);

        return $this->structuredCollection(
            AssetResource::collection($assets)->resolve(),
            ['next_cursor' => $assets->nextCursor()?->encode()],
        );
    }
}
