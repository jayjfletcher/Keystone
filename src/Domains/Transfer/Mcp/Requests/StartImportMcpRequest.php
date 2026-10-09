<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Impex\Domains\Run\Resources\RunResource;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Transfer\Actions\StartImportAction;

final class StartImportMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModel::class);
    }

    protected function rules(): array
    {
        return StartImportAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $run = app(StartImportAction::class)->execute($validated);

        return Response::structured((new RunResource($run))->resolve());
    }
}
