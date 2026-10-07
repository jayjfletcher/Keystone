<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer;

use JayI\Foundation\Support\ServiceProvider;

class TransferServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
