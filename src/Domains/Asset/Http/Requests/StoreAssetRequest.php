<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Asset\Actions\CreateAssetAction;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;

final class StoreAssetRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AssetModel::class);
    }

    public function rules(): array
    {
        return CreateAssetAction::rules();
    }

    public function persist(): JsonResponse
    {
        $asset = app(CreateAssetAction::class)->execute($this->validated());

        return (new AssetResource($asset))->response()->setStatusCode(201);
    }
}
