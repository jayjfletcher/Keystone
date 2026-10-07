<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Keystone\Domains\Asset\Actions\DeleteAssetAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteAssetMcpRequest extends AssetRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->asset());
    }

    protected function rules(): array
    {
        return DeleteAssetAction::rules() + [
            'asset' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $asset = app(DeleteAssetAction::class)->execute($this->asset());

        return Response::structured(['deleted' => true, 'code' => $asset->code]);
    }
}
