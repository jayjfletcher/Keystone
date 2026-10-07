<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Http\Requests\ProductRequest;
use JayI\Keystone\Domains\Workflow\Actions\ListProductVersionsAction;
use JayI\Keystone\Domains\Workflow\Resources\VersionSummaryResource;

final class IndexProductVersionsRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    public function rules(): array
    {
        return ListProductVersionsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $versions = app(ListProductVersionsAction::class)->execute($this->product(), $this->validated());

        return VersionSummaryResource::collection($versions)->response();
    }
}
