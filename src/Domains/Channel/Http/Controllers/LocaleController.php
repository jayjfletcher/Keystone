<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Channel\Http\Requests\DeleteLocaleRequest;
use JayI\Keystone\Domains\Channel\Http\Requests\IndexLocalesRequest;
use JayI\Keystone\Domains\Channel\Http\Requests\ShowLocaleRequest;
use JayI\Keystone\Domains\Channel\Http\Requests\StoreLocaleRequest;
use JayI\Keystone\Domains\Channel\Http\Requests\UpdateLocaleRequest;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

final class LocaleController
{
    public function index(IndexLocalesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreLocaleRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowLocaleRequest $request, LocaleModel $locale): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateLocaleRequest $request, LocaleModel $locale): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteLocaleRequest $request, LocaleModel $locale): JsonResponse
    {
        return $request->persist();
    }
}
