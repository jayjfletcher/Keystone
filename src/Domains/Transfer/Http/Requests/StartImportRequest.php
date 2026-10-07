<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Impex\Domains\Run\Resources\RunResource;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Actions\StartImportAction;

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
