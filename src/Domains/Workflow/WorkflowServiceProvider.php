<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Workflow\Models\CompletenessModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;
use JayI\Keystone\Domains\Workflow\Services\Versions;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One instance, so a revert or transition's action context reaches the
        // update it performs.
        $this->app->singleton(Versions::class);
    }

    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Completeness' => CompletenessModel::class,
            'JayI\Keystone\Models\Version' => VersionModel::class,
        ]);

        // How the audit log (jayi/keen) names a version: what it versions, and its number.
        $this->app->make(AuditHooks::class)
            ->label(VersionModel::class, fn (VersionModel $version): string => $version->versionable_type.' v'.$version->version);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
