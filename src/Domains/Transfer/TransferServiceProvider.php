<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Transfer;

use RefactorCircus\Foundation\Support\ServiceProvider;

class TransferServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
