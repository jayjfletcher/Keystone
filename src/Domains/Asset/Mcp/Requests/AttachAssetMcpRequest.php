<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Keystone\Domains\Asset\Actions\AttachAssetAction;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class AttachAssetMcpRequest extends AssetRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->asset());
    }

    protected function rules(): array
    {
        return AttachAssetAction::rules() + [
            'asset' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['asset']);

        $asset = app(AttachAssetAction::class)->execute($this->asset(), $validated);

        return Response::structured((new AssetResource($asset))->resolve());
    }
}
