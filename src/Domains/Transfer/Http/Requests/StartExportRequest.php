<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Impex\Domains\Run\Resources\RunResource;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Transfer\Actions\StartExportAction;

/**
 * A queued export run; its result names the file asset.
 */
final class StartExportRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModel::class);
    }

    public function rules(): array
    {
        return StartExportAction::rules();
    }

    public function persist(): JsonResponse
    {
        $run = app(StartExportAction::class)->execute($this->validated());

        return (new RunResource($run))->response()->setStatusCode(202);
    }
}
