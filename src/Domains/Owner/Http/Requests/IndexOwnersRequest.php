<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Actions\ListOwnersAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerResource;

final class IndexOwnersRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', OwnerModel::class);
    }

    public function rules(): array
    {
        return ListOwnersAction::rules();
    }

    public function persist(): JsonResponse
    {
        $owners = app(ListOwnersAction::class)->execute($this->validated());

        return OwnerResource::collection($owners)->response();
    }
}
