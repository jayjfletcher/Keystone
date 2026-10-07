<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Asset\Actions\DeleteAssetAction;

final class DeleteAssetRequest extends AssetRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->asset());
    }

    public function rules(): array
    {
        return DeleteAssetAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteAssetAction::class)->execute($this->asset());

        return new JsonResponse(null, 204);
    }
}
