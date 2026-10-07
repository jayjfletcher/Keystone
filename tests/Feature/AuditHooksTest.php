<?php

declare(strict_types=1);

use JayI\Foundation\Audit\AuditHooks;
use JayI\Keystone\Domains\Association\Models\AssociationModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;
use JayI\Keystone\Tests\Fixtures\Catalog;

/**
 * What the audit log (jayi/keen) calls Keystone's records, where the default
 * naming attributes would fall short.
 */
beforeEach(function (): void {
    Catalog::apparel();
});

it('names labelled records by the current locale label, else the code', function (): void {
    $hooks = app(AuditHooks::class);

    $this->patchJson('/keystone/families/shirts', ['labels' => ['en' => 'Shirts', 'fr' => 'Chemises']])->assertOk();

    expect($hooks->labelFor(FamilyModel::query()->where('code', 'shirts')->firstOrFail()))->toBe('Shirts')
        ->and($hooks->labelFor(AttributeModel::query()->where('code', 'color')->firstOrFail()))->toBe('color');

    app()->setLocale('fr');

    expect($hooks->labelFor(FamilyModel::query()->where('code', 'shirts')->firstOrFail()))->toBe('Chemises');
});

it('names an association by its type and both ends', function (): void {
    $this->postJson('/keystone/association-types', ['code' => 'cross_sell', 'labels' => ['en' => 'Cross-sell']])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'TEE'])->assertCreated();
    $this->postJson('/keystone/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_size'])->assertCreated();
    $this->patchJson('/keystone/products/TEE', ['associations' => ['cross_sell' => ['product_models' => ['polo']]]])->assertOk();

    expect(app(AuditHooks::class)->labelFor(AssociationModel::query()->firstOrFail()))->toBe('Cross-sell: TEE → polo');
});

it('names a version by what it versions and its number', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'TEE'])->assertCreated();
    $this->patchJson('/keystone/products/TEE', ['enabled' => false])->assertOk();

    $version = VersionModel::query()->firstOrFail();

    expect(app(AuditHooks::class)->labelFor($version))->toBe($version->versionable_type.' v'.$version->version);
});
