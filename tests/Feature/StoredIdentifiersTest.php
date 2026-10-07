<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Association\Models\AssociationModel;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\Workflow\Models\CompletenessModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;
use JayI\Keystone\Impex\ImpexIntegration;

/*
 * The models moved into domains and took a `Model` suffix. Anything an
 * application stored by name must still resolve, and new records must keep
 * writing the same value.
 */

it('keeps the morph aliases products, product models and owners are stored under', function (string $alias, string $model): void {
    expect(Relation::getMorphedModel($alias))->toBe($model)
        ->and((new $model)->getMorphClass())->toBe($alias);
})->with([
    ['keystone_product', ProductModel::class],
    ['keystone_product_model', ProductModelModel::class],
    ['keystone_owner', OwnerModel::class],
]);

it('resolves the class names the models had before they moved', function (string $old, string $model): void {
    expect(Relation::getMorphedModel($old))->toBe($model);
})->with([
    ['JayI\\Keystone\\Models\\Asset', AssetModel::class],
    ['JayI\\Keystone\\Models\\Association', AssociationModel::class],
    ['JayI\\Keystone\\Models\\AssociationType', AssociationTypeModel::class],
    ['JayI\\Keystone\\Models\\Attribute', AttributeModel::class],
    ['JayI\\Keystone\\Models\\AttributeGroup', AttributeGroupModel::class],
    ['JayI\\Keystone\\Models\\AttributeOption', AttributeOptionModel::class],
    ['JayI\\Keystone\\Models\\Category', CategoryModel::class],
    ['JayI\\Keystone\\Models\\Channel', ChannelModel::class],
    ['JayI\\Keystone\\Models\\Completeness', CompletenessModel::class],
    ['JayI\\Keystone\\Models\\Family', FamilyModel::class],
    ['JayI\\Keystone\\Models\\FamilyVariant', FamilyVariantModel::class],
    ['JayI\\Keystone\\Models\\Locale', LocaleModel::class],
    ['JayI\\Keystone\\Models\\Owner', OwnerModel::class],
    ['JayI\\Keystone\\Models\\OwnerType', OwnerTypeModel::class],
    ['JayI\\Keystone\\Models\\Product', ProductModel::class],
    ['JayI\\Keystone\\Models\\ProductModel', ProductModelModel::class],
    ['JayI\\Keystone\\Models\\Version', VersionModel::class],
]);

it('writes the old class name for models that never had an alias', function (string $old, string $model): void {
    expect((new $model)->getMorphClass())->toBe($old);
})->with([
    ['JayI\\Keystone\\Models\\Attribute', AttributeModel::class],
    ['JayI\\Keystone\\Models\\Category', CategoryModel::class],
    ['JayI\\Keystone\\Models\\Version', VersionModel::class],
]);

it('resolves version history stored under an alias or an old class name', function (string $type): void {
    $product = ProductModel::factory()->create();

    $version = VersionModel::query()->forceCreate([
        'id' => (string) Str::ulid(),
        'versionable_type' => $type,
        'versionable_id' => $product->getKey(),
        'version' => 1,
        'action' => 'created',
        'snapshot' => [],
        'created_at' => now(),
    ]);

    expect($version->fresh()?->versionable?->is($product))->toBeTrue();
})->with(['keystone_product', 'JayI\\Keystone\\Models\\Product']);

it('keeps the class names Impex stores for Keystone flows, sources and actions', function (): void {
    // impex_runs.flow_class and impex_batches.source / action hold these.
    expect(app(ImpexIntegration::class)->flows())->toMatchArray([
        ImpexIntegration::IMPORT => 'JayI\\Keystone\\Impex\\Flows\\ImportProductsFlow',
        ImpexIntegration::UPSERT => 'JayI\\Keystone\\Impex\\Flows\\UpsertProductsFlow',
        ImpexIntegration::EXPORT => 'JayI\\Keystone\\Impex\\Flows\\ExportProductsFlow',
    ]);

    foreach ([
        'JayI\\Keystone\\Impex\\Flows\\FeedFlow',
        'JayI\\Keystone\\Impex\\Sources\\FileProductSource',
        'JayI\\Keystone\\Impex\\Sources\\InlineProductSource',
        'JayI\\Keystone\\Impex\\Actions\\DeliverFeed',
        'JayI\\Keystone\\Impex\\Actions\\ExportProductPages',
        'JayI\\Keystone\\Impex\\Actions\\ImportProductRow',
        'JayI\\Keystone\\Impex\\Actions\\JoinProductExport',
    ] as $class) {
        expect(class_exists($class))->toBeTrue($class);
    }
});

it('keeps the class names of queued jobs', function (string $job): void {
    expect(class_exists($job))->toBeTrue();
})->with([
    'JayI\\Keystone\\Jobs\\PurgeAttributeValues',
    'JayI\\Keystone\\Jobs\\PurgeValueSlots',
    'JayI\\Keystone\\Jobs\\SyncProductIndex',
]);
