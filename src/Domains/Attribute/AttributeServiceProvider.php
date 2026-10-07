<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

class AttributeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Attribute' => AttributeModel::class,
            'JayI\Keystone\Models\AttributeGroup' => AttributeGroupModel::class,
            'JayI\Keystone\Models\AttributeOption' => AttributeOptionModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(AttributeModel::class, fn (AttributeModel $attribute): string => $attribute->label())
            ->label(AttributeGroupModel::class, fn (AttributeGroupModel $attributeGroup): string => $attributeGroup->label())
            ->label(AttributeOptionModel::class, fn (AttributeOptionModel $attributeOption): string => $attributeOption->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
