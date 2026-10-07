<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Asset\Actions\CreateAssetAction;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateAssetMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AssetModel::class);
    }

    protected function rules(): array
    {
        return CreateAssetAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $asset = app(CreateAssetAction::class)->execute($validated);

        return Response::structured((new AssetResource($asset))->resolve());
    }
}
