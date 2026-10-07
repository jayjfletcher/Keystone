<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Owner\Actions\ShowOwnerTypeAction;
use JayI\Keystone\Domains\Owner\Resources\OwnerTypeResource;

final class ShowOwnerTypeRequest extends OwnerTypeRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->ownerType());
    }

    public function rules(): array
    {
        return ShowOwnerTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $ownerType = app(ShowOwnerTypeAction::class)->execute($this->ownerType());

        return (new OwnerTypeResource($ownerType))->response();
    }
}
