<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Transfer;

use RefactorCircus\Keystone\Support\ServiceProvider;

class TransferServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
