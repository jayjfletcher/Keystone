<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\AttachAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\DeleteAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\DetachAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\IndexAssetsRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\ShowAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\StoreAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Http\Requests\UpdateAssetRequest;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

final class AssetController
{
    public function index(IndexAssetsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowAssetRequest $request, AssetModel $asset): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAssetRequest $request, AssetModel $asset): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAssetRequest $request, AssetModel $asset): JsonResponse
    {
        return $request->persist();
    }

    public function attach(AttachAssetRequest $request, AssetModel $asset): JsonResponse
    {
        return $request->persist();
    }

    public function detach(DetachAssetRequest $request, AssetModel $asset): JsonResponse
    {
        return $request->persist();
    }
}
