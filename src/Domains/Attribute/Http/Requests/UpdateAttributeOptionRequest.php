<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Attribute\Actions\UpdateAttributeOptionAction;
use RefactorCircus\Showroom\Domains\Attribute\Resources\AttributeOptionResource;

final class UpdateAttributeOptionRequest extends AttributeOptionRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->option());
    }

    public function rules(): array
    {
        return UpdateAttributeOptionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $option = app(UpdateAttributeOptionAction::class)->execute($this->option(), $this->validated());

        return (new AttributeOptionResource($option))->response();
    }
}
