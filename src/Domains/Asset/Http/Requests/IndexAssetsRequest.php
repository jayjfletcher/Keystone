<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Asset\Actions\ListAssetsAction;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Asset\Resources\AssetResource;

final class IndexAssetsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AssetModel::class);
    }

    public function rules(): array
    {
        return ListAssetsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $assets = app(ListAssetsAction::class)->execute($this->validated());

        return AssetResource::collection($assets)->response();
    }
}
