<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel;

use RefactorCircus\Foundation\Audit\AuditHooks;
use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

class ChannelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // How the audit log (refactor-circus/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(ChannelModel::class, fn (ChannelModel $channel): string => $channel->label())
            ->label(LocaleModel::class, fn (LocaleModel $locale): string => $locale->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
