<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Owner\Actions\UpdateOwnerAction;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerResource;

final class UpdateOwnerRequest extends OwnerRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->owner());
    }

    public function rules(): array
    {
        return UpdateOwnerAction::rules();
    }

    public function persist(): JsonResponse
    {
        $owner = app(UpdateOwnerAction::class)->execute($this->owner(), $this->validated());

        return (new OwnerResource($owner))->response();
    }
}
