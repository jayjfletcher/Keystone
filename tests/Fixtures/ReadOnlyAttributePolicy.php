<?php

declare(strict_types=1);

namespace JayI\Keystone\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JayI\Keystone\Domains\Attribute\Policies\AttributePolicy;

/**
 * An application policy that lets anyone read attributes and no one change them.
 */
final class ReadOnlyAttributePolicy extends AttributePolicy
{
    public function create(Model $user): bool
    {
        return false;
    }

    public function update(Model $user, Model $model): bool
    {
        return false;
    }

    public function delete(Model $user, Model $model): bool
    {
        return false;
    }
}
