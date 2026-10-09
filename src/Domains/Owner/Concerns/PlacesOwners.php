<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Concerns;

use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Support\Concerns\MovesInTree;

/**
 * Where an owner may sit in its chain, shared by create and move.
 */
trait PlacesOwners
{
    use MovesInTree;

    /**
     * Check the owner type's rules and, for an existing owner, that the new
     * parent is not the owner itself or beneath it.
     *
     * @throws ValidationException
     */
    private function checkPlacement(OwnerModel $owner, OwnerTypeModel $type, ?OwnerModel $parent): void
    {
        if ($parent === null) {
            if (! $type->can_be_root) {
                throw ValidationException::withMessages([
                    'parent' => sprintf('A %s needs a parent owner.', $type->code),
                ]);
            }

            return;
        }

        $parent->loadMissing('type');

        if (! $type->allowsParent($parent->type)) {
            throw ValidationException::withMessages([
                'parent' => sprintf(
                    'A %s cannot sit under a %s. Allowed parents: %s.',
                    $type->code,
                    $parent->type->code,
                    $type->parentTypes->pluck('code')->join(', ') ?: 'none',
                ),
            ]);
        }

        if ($owner->exists && $owner->contains($parent)) {
            throw ValidationException::withMessages([
                'parent' => sprintf('Owner "%s" cannot move under itself or its own descendant "%s".', $owner->code, $parent->code),
            ]);
        }
    }
}
