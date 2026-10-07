<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Actions\DeleteAttributeGroupAction;

final class DeleteAttributeGroupRequest extends AttributeGroupRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->group());
    }

    public function rules(): array
    {
        return DeleteAttributeGroupAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteAttributeGroupAction::class)->execute($this->group());

        return new JsonResponse(null, 204);
    }
}
