<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;
use RefactorCircus\Showroom\Domains\Asset\Policies\AssetPolicy;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Showroom\Domains\Association\Policies\AssociationTypePolicy;
use RefactorCircus\Showroom\Domains\Attribute\Mcp\Tools\CreateAttributeTool;
use RefactorCircus\Showroom\Domains\Attribute\Mcp\Tools\ListAttributesTool;
use RefactorCircus\Showroom\Domains\Attribute\Mcp\Tools\ShowAttributeTool;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Showroom\Domains\Attribute\Policies\AttributeGroupPolicy;
use RefactorCircus\Showroom\Domains\Attribute\Policies\AttributeOptionPolicy;
use RefactorCircus\Showroom\Domains\Attribute\Policies\AttributePolicy;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;
use RefactorCircus\Showroom\Domains\Category\Policies\CategoryPolicy;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Showroom\Domains\Channel\Policies\ChannelPolicy;
use RefactorCircus\Showroom\Domains\Channel\Policies\LocalePolicy;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Domains\Family\Policies\FamilyPolicy;
use RefactorCircus\Showroom\Domains\Family\Policies\FamilyVariantPolicy;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Showroom\Domains\Owner\Policies\OwnerPolicy;
use RefactorCircus\Showroom\Domains\Owner\Policies\OwnerTypePolicy;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;
use RefactorCircus\Showroom\Domains\Product\Policies\ProductPolicy;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Showroom\Domains\ProductModel\Policies\ProductModelPolicy;
use RefactorCircus\Showroom\ShowroomServiceProvider;
use RefactorCircus\Showroom\Tests\Fixtures\ReadOnlyAttributePolicy;
use Workbench\App\Models\User;

beforeEach(function (): void {
    config()->set('showroom.authorization', true);

    $this->ann = User::forceCreate(['name' => 'Ann', 'email' => 'ann@example.test', 'password' => 'x']);
});

/**
 * Register the policies again after a test changes `showroom.policies`, as
 * the provider does on boot.
 *
 * @param  array<class-string, class-string>  $policies
 */
function useShowroomPolicies(array $policies): void
{
    foreach ($policies as $model => $policy) {
        config()->set('showroom.policies.'.$model, $policy);
    }

    $provider = app()->getProvider(ShowroomServiceProvider::class);

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
    $this->getJson('/showroom/attributes')->assertForbidden();
    $this->postJson('/showroom/attributes', ['code' => 'color', 'type' => 'select'])->assertForbidden();
    mcpTool(ListAttributesTool::class)->assertHasErrors(['Unauthorized.']);
});

it('lets an authenticated user manage the catalog by default', function (): void {
    $this->actingAs($this->ann);

    $this->postJson('/showroom/attributes', ['code' => 'color', 'type' => 'select'])->assertCreated();
    $this->getJson('/showroom/attributes/color')->assertOk();
    mcpTool(ShowAttributeTool::class, ['attribute' => 'color'])->assertOk();
});

it('leaves the API to the route middleware while authorization is off', function (): void {
    config()->set('showroom.authorization', false);

    AttributeModel::factory()->create(['code' => 'name']);

    $this->getJson('/showroom/attributes/name')->assertOk();
    mcpTool(ShowAttributeTool::class, ['attribute' => 'name'])->assertOk();
});

it('applies a policy swapped in through the config', function (): void {
    useShowroomPolicies([AttributeModel::class => ReadOnlyAttributePolicy::class]);

    $this->actingAs($this->ann);
    AttributeModel::factory()->create(['code' => 'name']);

    $this->getJson('/showroom/attributes/name')->assertOk();
    $this->postJson('/showroom/attributes', ['code' => 'color', 'type' => 'select'])->assertForbidden();
    $this->patchJson('/showroom/attributes/name', ['labels' => ['en' => 'Name']])->assertForbidden();
    $this->deleteJson('/showroom/attributes/name')->assertForbidden();
    mcpTool(CreateAttributeTool::class, ['code' => 'color', 'type' => 'select'])->assertHasErrors(['Unauthorized.']);
});
