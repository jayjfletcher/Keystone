<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Transfer\Http\Requests\StartExportRequest;
use JayI\Keystone\Domains\Transfer\Http\Requests\StartImportRequest;

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
