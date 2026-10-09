<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Asset\Policies\AssetPolicy;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Association\Policies\AssociationTypePolicy;
use RefactorCircus\Keystone\Domains\Attribute\Mcp\Tools\CreateAttributeTool;
use RefactorCircus\Keystone\Domains\Attribute\Mcp\Tools\ListAttributesTool;
use RefactorCircus\Keystone\Domains\Attribute\Mcp\Tools\ShowAttributeTool;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Domains\Attribute\Policies\AttributeGroupPolicy;
use RefactorCircus\Keystone\Domains\Attribute\Policies\AttributeOptionPolicy;
use RefactorCircus\Keystone\Domains\Attribute\Policies\AttributePolicy;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Category\Policies\CategoryPolicy;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Keystone\Domains\Channel\Policies\ChannelPolicy;
use RefactorCircus\Keystone\Domains\Channel\Policies\LocalePolicy;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\Family\Policies\FamilyPolicy;
use RefactorCircus\Keystone\Domains\Family\Policies\FamilyVariantPolicy;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Domains\Owner\Policies\OwnerPolicy;
use RefactorCircus\Keystone\Domains\Owner\Policies\OwnerTypePolicy;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Product\Policies\ProductPolicy;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\ProductModel\Policies\ProductModelPolicy;
use RefactorCircus\Keystone\KeystoneServiceProvider;
use RefactorCircus\Keystone\Tests\Fixtures\ReadOnlyAttributePolicy;
use Workbench\App\Models\User;

beforeEach(function (): void {
    config()->set('keystone.authorization', true);

    $this->ann = User::forceCreate(['name' => 'Ann', 'email' => 'ann@example.test', 'password' => 'x']);
});

/**
 * Register the policies again after a test changes `keystone.policies`, as
 * the provider does on boot.
 *
 * @param  array<class-string, class-string>  $policies
 */
function useKeystonePolicies(array $policies): void
{
    foreach ($policies as $model => $policy) {
        config()->set('keystone.policies.'.$model, $policy);
    }

    $provider = app()->getProvider(KeystoneServiceProvider::class);

    (fn () => $this->registerPolicies())->call($provider);
}

it('registers the policies from the config', function (): void {
    expect(Gate::getPolicyFor(AttributeGroupModel::class))->toBeInstanceOf(AttributeGroupPolicy::class)
        ->and(Gate::getPolicyFor(AttributeModel::class))->toBeInstanceOf(AttributePolicy::class)
        ->and(Gate::getPolicyFor(AttributeOptionModel::class))->toBeInstanceOf(AttributeOptionPolicy::class)
        ->and(Gate::getPolicyFor(FamilyModel::class))->toBeInstanceOf(FamilyPolicy::class)
        ->and(Gate::getPolicyFor(FamilyVariantModel::class))->toBeInstanceOf(FamilyVariantPolicy::class)
        ->and(Gate::getPolicyFor(ProductModelModel::class))->toBeInstanceOf(ProductModelPolicy::class)
        ->and(Gate::getPolicyFor(ProductModel::class))->toBeInstanceOf(ProductPolicy::class)
        ->and(Gate::getPolicyFor(OwnerTypeModel::class))->toBeInstanceOf(OwnerTypePolicy::class)
        ->and(Gate::getPolicyFor(OwnerModel::class))->toBeInstanceOf(OwnerPolicy::class)
        ->and(Gate::getPolicyFor(CategoryModel::class))->toBeInstanceOf(CategoryPolicy::class)
        ->and(Gate::getPolicyFor(AssetModel::class))->toBeInstanceOf(AssetPolicy::class)
        ->and(Gate::getPolicyFor(LocaleModel::class))->toBeInstanceOf(LocalePolicy::class)
        ->and(Gate::getPolicyFor(ChannelModel::class))->toBeInstanceOf(ChannelPolicy::class)
        ->and(Gate::getPolicyFor(AssociationTypeModel::class))->toBeInstanceOf(AssociationTypePolicy::class);
});

it('refuses a guest once authorization is on', function (): void {
    $this->getJson('/keystone/attributes')->assertForbidden();
    $this->postJson('/keystone/attributes', ['code' => 'color', 'type' => 'select'])->assertForbidden();
    mcpTool(ListAttributesTool::class)->assertHasErrors(['Unauthorized.']);
});

it('lets an authenticated user manage the catalog by default', function (): void {
    $this->actingAs($this->ann);

    $this->postJson('/keystone/attributes', ['code' => 'color', 'type' => 'select'])->assertCreated();
    $this->getJson('/keystone/attributes/color')->assertOk();
    mcpTool(ShowAttributeTool::class, ['attribute' => 'color'])->assertOk();
});

it('leaves the API to the route middleware while authorization is off', function (): void {
    config()->set('keystone.authorization', false);

    AttributeModel::factory()->create(['code' => 'name']);

    $this->getJson('/keystone/attributes/name')->assertOk();
    mcpTool(ShowAttributeTool::class, ['attribute' => 'name'])->assertOk();
});

it('applies a policy swapped in through the config', function (): void {
    useKeystonePolicies([AttributeModel::class => ReadOnlyAttributePolicy::class]);

    $this->actingAs($this->ann);
    AttributeModel::factory()->create(['code' => 'name']);

    $this->getJson('/keystone/attributes/name')->assertOk();
    $this->postJson('/keystone/attributes', ['code' => 'color', 'type' => 'select'])->assertForbidden();
    $this->patchJson('/keystone/attributes/name', ['labels' => ['en' => 'Name']])->assertForbidden();
    $this->deleteJson('/keystone/attributes/name')->assertForbidden();
    mcpTool(CreateAttributeTool::class, ['code' => 'color', 'type' => 'select'])->assertHasErrors(['Unauthorized.']);
});
