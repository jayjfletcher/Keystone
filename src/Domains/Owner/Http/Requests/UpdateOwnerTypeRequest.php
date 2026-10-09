<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Owner\Actions\UpdateOwnerTypeAction;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerTypeResource;

final class UpdateOwnerTypeRequest extends OwnerTypeRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->ownerType());
    }

    public function rules(): array
    {
        return UpdateOwnerTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $ownerType = app(UpdateOwnerTypeAction::class)->execute($this->ownerType(), $this->validated());

        return (new OwnerTypeResource($ownerType))->response();
    }
}
