<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RefactorCircus\Keystone\Keystone
 */
class Keystone extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RefactorCircus\Keystone\Keystone::class;
    }
}
