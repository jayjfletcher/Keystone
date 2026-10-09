<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RefactorCircus\Showroom\Showroom
 */
class Showroom extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RefactorCircus\Showroom\Showroom::class;
    }
}
