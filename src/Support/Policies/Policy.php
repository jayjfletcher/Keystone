<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Support\Policies;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Keystone\Policies\Policy as BasePolicy;

/**
 * The bundled catalog policy: any authenticated user may read and manage.
 *
 * Each policy is registered from `showroom.policies`, so an application
 * restricts the catalog by pointing a model at its own class there.
 */
abstract class Policy extends BasePolicy
{
    public function viewAny(Model $user): bool
    {
        return true;
    }

    public function view(Model $user, Model $model): bool
    {
        return true;
    }

    public function create(Model $user): bool
    {
        return true;
    }

    public function update(Model $user, Model $model): bool
    {
        return true;
    }

    public function delete(Model $user, Model $model): bool
    {
        return true;
    }
}
