<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Keystone\Domains\Family\Actions\ShowFamilyAction;
use JayI\Keystone\Domains\Family\Resources\FamilyResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowFamilyMcpRequest extends FamilyRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->family());
    }

    protected function rules(): array
    {
        return ShowFamilyAction::rules() + [
            'family' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $family = app(ShowFamilyAction::class)->execute($this->family());

        return Response::structured((new FamilyResource($family))->resolve());
    }
}
