<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Impex\Domains\Run\Resources\RunResource;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Actions\StartExportAction;

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
