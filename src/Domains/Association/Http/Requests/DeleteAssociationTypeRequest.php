<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Association\Actions\DeleteAssociationTypeAction;

final class DeleteAssociationTypeRequest extends AssociationTypeRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->associationType());
    }

    public function rules(): array
    {
        return DeleteAssociationTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteAssociationTypeAction::class)->execute($this->associationType());

        return new JsonResponse(null, 204);
    }
}
