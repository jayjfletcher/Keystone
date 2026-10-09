<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Impex\ImpexIntegration;

/*
 * Values stored by name - the short morph aliases, the classes Impex records
 * for Keystone's flows, queued jobs - must keep resolving.
 */

it('keeps the morph aliases products, product models and owners are stored under', function (string $alias, string $model): void {
    expect(Relation::getMorphedModel($alias))->toBe($model)
        ->and((new $model)->getMorphClass())->toBe($alias);
})->with([
    ['keystone_product', ProductModel::class],
    ['keystone_product_model', ProductModelModel::class],
    ['keystone_owner', OwnerModel::class],
]);

it('keeps the class names Impex stores for Keystone flows, sources and actions', function (): void {
    // impex_runs.flow_class and impex_batches.source / action hold these.
    expect(app(ImpexIntegration::class)->flows())->toMatchArray([
        ImpexIntegration::IMPORT => 'RefactorCircus\\Keystone\\Impex\\Flows\\ImportProductsFlow',
        ImpexIntegration::UPSERT => 'RefactorCircus\\Keystone\\Impex\\Flows\\UpsertProductsFlow',
        ImpexIntegration::EXPORT => 'RefactorCircus\\Keystone\\Impex\\Flows\\ExportProductsFlow',
    ]);

    foreach ([
        'RefactorCircus\\Keystone\\Impex\\Flows\\FeedFlow',
        'RefactorCircus\\Keystone\\Impex\\Sources\\FileProductSource',
        'RefactorCircus\\Keystone\\Impex\\Sources\\InlineProductSource',
        'RefactorCircus\\Keystone\\Impex\\Actions\\DeliverFeed',
        'RefactorCircus\\Keystone\\Impex\\Actions\\ExportProductPages',
        'RefactorCircus\\Keystone\\Impex\\Actions\\ImportProductRow',
        'RefactorCircus\\Keystone\\Impex\\Actions\\JoinProductExport',
    ] as $class) {
        expect(class_exists($class))->toBeTrue($class);
    }
});

it('keeps the class names of queued jobs', function (string $job): void {
    expect(class_exists($job))->toBeTrue();
})->with([
    'RefactorCircus\\Keystone\\Jobs\\PurgeAttributeValues',
    'RefactorCircus\\Keystone\\Jobs\\PurgeValueSlots',
    'RefactorCircus\\Keystone\\Jobs\\SyncProductIndex',
]);
