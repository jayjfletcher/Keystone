<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Asset\Actions\ListAssetsAction;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;
use Laravel\Mcp\ResponseFactory;

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
