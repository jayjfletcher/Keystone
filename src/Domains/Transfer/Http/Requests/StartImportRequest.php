<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Impex\Domains\Run\Resources\RunResource;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Transfer\Actions\StartImportAction;

/**
 * A queued import run; follow it through Impex.
 */
final class StartImportRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModel::class);
    }

    public function rules(): array
    {
        return StartImportAction::rules();
    }

    public function persist(): JsonResponse
    {
        $run = app(StartImportAction::class)->execute($this->validated());

        return (new RunResource($run))->response()->setStatusCode(202);
    }
}
