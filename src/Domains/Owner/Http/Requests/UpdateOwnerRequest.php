<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Owner\Actions\UpdateOwnerAction;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerResource;

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
