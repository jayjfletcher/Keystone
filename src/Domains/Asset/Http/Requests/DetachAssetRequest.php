<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Asset\Actions\DetachAssetAction;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;

final class DetachAssetRequest extends AssetRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->asset());
    }

    public function rules(): array
    {
        return DetachAssetAction::rules();
    }

    public function persist(): JsonResponse
    {
        $asset = app(DetachAssetAction::class)->execute($this->asset(), $this->validated());

        return (new AssetResource($asset))->response();
    }
}
