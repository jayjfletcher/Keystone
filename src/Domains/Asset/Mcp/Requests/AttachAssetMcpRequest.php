<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Asset\Actions\AttachAssetAction;
use RefactorCircus\Showroom\Domains\Asset\Resources\AssetResource;

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
