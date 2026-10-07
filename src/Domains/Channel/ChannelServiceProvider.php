<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

class ChannelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Channel' => ChannelModel::class,
            'JayI\Keystone\Models\Locale' => LocaleModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(ChannelModel::class, fn (ChannelModel $channel): string => $channel->label())
            ->label(LocaleModel::class, fn (LocaleModel $locale): string => $locale->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
