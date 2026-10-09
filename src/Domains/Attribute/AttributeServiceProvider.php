<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute;

use RefactorCircus\Keystone\Audit\AuditHooks;
use RefactorCircus\Keystone\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

class AttributeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // How the audit log (refactor-circus/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(AttributeModel::class, fn (AttributeModel $attribute): string => $attribute->label())
            ->label(AttributeGroupModel::class, fn (AttributeGroupModel $attributeGroup): string => $attributeGroup->label())
            ->label(AttributeOptionModel::class, fn (AttributeOptionModel $attributeOption): string => $attributeOption->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
