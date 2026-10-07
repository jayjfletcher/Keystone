<?php

declare(strict_types=1);

namespace JayI\Keystone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JayI\Keystone\Keystone
 */
class Keystone extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JayI\Keystone\Keystone::class;
    }
}
