<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Asset\Actions\CreateAssetAction;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Asset\Resources\AssetResource;

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
