<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Asset\Actions\AttachAssetAction;
use RefactorCircus\Keystone\Domains\Asset\Resources\AssetResource;

final class AttachAssetRequest extends AssetRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->asset());
    }

    public function rules(): array
    {
        return AttachAssetAction::rules();
    }

    public function persist(): JsonResponse
    {
        $asset = app(AttachAssetAction::class)->execute($this->asset(), $this->validated());

        return (new AssetResource($asset))->response();
    }
}
