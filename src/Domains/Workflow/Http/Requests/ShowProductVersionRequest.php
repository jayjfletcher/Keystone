<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Http\Requests\ProductRequest;
use JayI\Keystone\Domains\Workflow\Actions\ShowProductVersionAction;
use JayI\Keystone\Domains\Workflow\Resources\VersionResource;

final class ShowProductVersionRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    public function rules(): array
    {
        return ShowProductVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $version = app(ShowProductVersionAction::class)->execute($this->product(), (string) $this->route('version'));

        return (new VersionResource($version))->response();
    }
}
