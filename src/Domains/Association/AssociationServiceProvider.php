<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association;

use Illuminate\Database\Eloquent\Model;
use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Association\Models\AssociationModel;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

class AssociationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Association' => AssociationModel::class,
            'JayI\Keystone\Models\AssociationType' => AssociationTypeModel::class,
        ]);

        // How the audit log (jayi/keen) names them: a type by the current
        // locale's label, else its code; a link by its type and both ends.
        $this->app->make(AuditHooks::class)
            ->label(AssociationTypeModel::class, fn (AssociationTypeModel $type): string => $type->label())
            ->label(AssociationModel::class, fn (AssociationModel $association): string => $association->type->label().': '
                .self::end($association->source, $association->source_id).' → '.self::end($association->target, $association->target_id));

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * A product by its identifier, a product model by its code.
     */
    private static function end(?Model $record, string $id): string
    {
        return match (true) {
            $record instanceof ProductModel => $record->identifier,
            $record instanceof ProductModelModel => $record->code,
            default => $id,
        };
    }
}
