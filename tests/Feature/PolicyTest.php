<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Policies\AssetPolicy;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Policies\AssociationTypePolicy;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\CreateAttributeTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ListAttributesTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ShowAttributeTool;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use JayI\Keystone\Domains\Attribute\Policies\AttributeGroupPolicy;
use JayI\Keystone\Domains\Attribute\Policies\AttributeOptionPolicy;
use JayI\Keystone\Domains\Attribute\Policies\AttributePolicy;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Category\Policies\CategoryPolicy;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Channel\Policies\ChannelPolicy;
use JayI\Keystone\Domains\Channel\Policies\LocalePolicy;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Family\Policies\FamilyPolicy;
use JayI\Keystone\Domains\Family\Policies\FamilyVariantPolicy;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Owner\Policies\OwnerPolicy;
use JayI\Keystone\Domains\Owner\Policies\OwnerTypePolicy;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Product\Policies\ProductPolicy;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\ProductModel\Policies\ProductModelPolicy;
use JayI\Keystone\KeystoneServiceProvider;
use JayI\Keystone\Tests\Fixtures\ReadOnlyAttributePolicy;
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
