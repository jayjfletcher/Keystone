<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Asset\Actions\ListAssetsAction;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Resources\AssetResource;

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
