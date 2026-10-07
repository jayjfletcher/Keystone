<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Impex\Domains\Run\Resources\RunResource;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Actions\StartImportAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
