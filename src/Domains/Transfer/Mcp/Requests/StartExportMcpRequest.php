<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Impex\Domains\Run\Resources\RunResource;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Actions\StartExportAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class StartExportMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModel::class);
    }

    protected function rules(): array
    {
        return StartExportAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $run = app(StartExportAction::class)->execute($validated);

        return Response::structured((new RunResource($run))->resolve());
    }
}
