<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Concerns;

use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

/**
 * Resolving the owner a product or root product model is assigned to.
 */
trait AssignsOwners
{
    /**
     * @throws ValidationException
     */
    private function ownerFor(mixed $code): ?OwnerModel
    {
        if (! is_string($code)) {
            return null;
        }

        $owner = OwnerModel::query()->with('type')->where('code', $code)->firstOrFail();

        if (! $owner->type->owns_products) {
            throw ValidationException::withMessages([
                'owner' => sprintf('Owner "%s" is a %s, which does not own products.', $owner->code, $owner->type->code),
            ]);
        }

        return $owner;
    }

    /**
     * @throws ValidationException
     */
    private function refuseInheritedOwner(string $what): never
    {
        throw ValidationException::withMessages([
            'owner' => sprintf('A %s takes the owner of its root product model.', $what),
        ]);
    }
}
