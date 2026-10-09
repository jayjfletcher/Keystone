<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Transfer\Http\Requests\StartExportRequest;
use RefactorCircus\Keystone\Domains\Transfer\Http\Requests\StartImportRequest;

final class ImpexController
{
    public function import(StartImportRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function export(StartExportRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
