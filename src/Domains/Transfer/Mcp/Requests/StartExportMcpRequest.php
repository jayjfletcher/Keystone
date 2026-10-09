<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Impex\Domains\Run\Resources\RunResource;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Transfer\Actions\StartExportAction;

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
