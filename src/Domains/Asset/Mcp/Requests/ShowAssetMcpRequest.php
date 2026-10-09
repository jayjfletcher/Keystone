<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Asset\Actions\ShowAssetAction;
use RefactorCircus\Keystone\Domains\Asset\Resources\AssetResource;

final class ShowAssetMcpRequest extends AssetRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->asset());
    }

    protected function rules(): array
    {
        return ShowAssetAction::rules() + [
            'asset' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $asset = app(ShowAssetAction::class)->execute($this->asset());

        return Response::structured((new AssetResource($asset))->resolve());
    }
}
