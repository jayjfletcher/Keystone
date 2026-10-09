<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\DeleteLocaleRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\IndexLocalesRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\ShowLocaleRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\StoreLocaleRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\UpdateLocaleRequest;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

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
