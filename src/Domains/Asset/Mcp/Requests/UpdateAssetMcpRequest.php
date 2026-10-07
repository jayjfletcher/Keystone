<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Keystone\Domains\Asset\Actions\UpdateAssetAction;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateAssetMcpRequest extends AssetRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->asset());
    }

    protected function rules(): array
    {
        return UpdateAssetAction::rules() + [
            'asset' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['asset']);

        $asset = app(UpdateAssetAction::class)->execute($this->asset(), $validated);

        return Response::structured((new AssetResource($asset))->resolve());
    }
}
