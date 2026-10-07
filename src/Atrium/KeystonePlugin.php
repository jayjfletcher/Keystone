<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use JayI\Atrium\Domains\Navigation\Data\NavGroup;
use JayI\Atrium\Domains\Navigation\Data\NavItem;
use JayI\Atrium\Domains\Plugins\Support\Plugin;
use JayI\Atrium\Domains\Search\Data\SearchResult;
use JayI\Atrium\Domains\Search\Data\SearchSource;
use JayI\Atrium\Domains\Settings\Data\SettingsPanel;
use JayI\Atrium\Domains\Widgets\Data\WidgetDefinition;
use JayI\Atrium\Support\Icons;
use JayI\Foundation\Auth\Authorizer;
use JayI\Foundation\Packages\PackageRegistry;
use JayI\Keystone\Atrium\Http\Controllers\AssetUiController;
use JayI\Keystone\Atrium\Http\Controllers\AssociationUiController;
use JayI\Keystone\Atrium\Http\Controllers\AttributeGroupUiController;
use JayI\Keystone\Atrium\Http\Controllers\AttributeUiController;
use JayI\Keystone\Atrium\Http\Controllers\CategoryUiController;
use JayI\Keystone\Atrium\Http\Controllers\ChannelUiController;
use JayI\Keystone\Atrium\Http\Controllers\FamilyUiController;
use JayI\Keystone\Atrium\Http\Controllers\FamilyVariantUiController;
use JayI\Keystone\Atrium\Http\Controllers\OwnerTypeUiController;
use JayI\Keystone\Atrium\Http\Controllers\OwnerUiController;
use JayI\Keystone\Atrium\Http\Controllers\ProductModelUiController;
use JayI\Keystone\Atrium\Http\Controllers\ProductUiController;
use JayI\Keystone\Atrium\Http\Controllers\TransferUiController;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Product\Enums\ProductStatus;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\Workflow\Models\CompletenessModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;

/**
 * Registers Keystone inside the Atrium dashboard.
 */
class KeystonePlugin extends Plugin
{
    /**
     * The records search looks through.
     *
     * @var array<int, class-string<Model>>
     */
    private const array SEARCHED = [ProductModel::class, AssetModel::class, CategoryModel::class, OwnerModel::class, FamilyModel::class, AttributeModel::class, AttributeGroupModel::class];

    /**
     * Features from `keystone.atrium.features` that switch Keystone in Atrium
     * on and off as a whole. A feature class that is not installed, such as
     * KeystoneSupportFeature without jayi/pennantplus, is skipped.
     *
     * @return array<int, string>
     */
    public function features(): array
    {
        return $this->featuresFromConfig('keystone.atrium.features');
    }

    /**
     * The package's section in the sidebar rail: its icon and its place.
     */
    public function navigationGroups(): array
    {
        return [
            NavGroup::make(__('keystone::keystone.catalog'))->icon(Icons::svg('cube'))->sort(20),
        ];
    }

    /**
     * Each item shows only to those who may open its page: the policy
     * ability its list asks, as the JSON API asks it.
     */
    public function navigation(): array
    {
        $group = __('keystone::keystone.catalog');

        return [
            NavItem::make(__('keystone::keystone.products'))->icon(Icons::svg('cube'))->route('atrium.keystone.products.index')->group($group)->sort(1)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModel::class, $request)),
            NavItem::make(__('keystone::keystone.product_models'))->icon(Icons::svg('square-3-stack-3d'))->route('atrium.keystone.product-models.index')->group($group)->sort(2)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModelModel::class, $request)),
            NavItem::make(__('keystone::keystone.categories'))->icon(Icons::svg('folder'))->route('atrium.keystone.categories.index')->group($group)->sort(3)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', CategoryModel::class, $request)),
            NavItem::make(__('keystone::keystone.assets'))->icon(Icons::svg('photo'))->route('atrium.keystone.assets.index')->group($group)->sort(4)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', AssetModel::class, $request)),
            NavItem::make(__('keystone::keystone.owners'))->icon(Icons::svg('building-storefront'))->route('atrium.keystone.owners.index')->group($group)->sort(3)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', OwnerModel::class, $request)),
            NavItem::make(__('keystone::keystone.families'))->icon(Icons::svg('rectangle-group'))->route('atrium.keystone.families.index')->group($group)->sort(5)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', FamilyModel::class, $request)),
            NavItem::make(__('keystone::keystone.attributes'))->icon(Icons::svg('tag'))->route('atrium.keystone.attributes.index')->group($group)->sort(10)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', AttributeModel::class, $request)),
            NavItem::make(__('keystone::keystone.channels'))->icon(Icons::svg('globe-alt'))->route('atrium.keystone.channels.index')->group($group)->sort(30)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ChannelModel::class, $request)),
            NavItem::make(__('keystone::keystone.transfers'))->icon(Icons::svg('arrows-up-down'))->route('atrium.keystone.transfers.index')->group($group)->sort(40)
                ->authorize(fn (Request $request): bool => ScreenAccess::transfers($request)),
            NavItem::make(__('keystone::keystone.association_types'))->icon(Icons::svg('link'))->route('atrium.keystone.association-types.index')->group($group)->sort(35)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', AssociationTypeModel::class, $request)),
            NavItem::make(__('keystone::keystone.attribute_groups'))->icon(Icons::svg('squares-2x2'))->route('atrium.keystone.attribute-groups.index')->group($group)->sort(20)
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', AttributeGroupModel::class, $request)),

            // The package's own audit log, while an audit log is installed.
            $this->historyNavItem('keystone')->group($group)->sort(90),
        ];
    }

    public function routes(): void
    {
        Route::name('keystone.')->group(function (): void {
            Route::get('keystone/products', [ProductUiController::class, 'index'])->name('products.index');
            Route::get('keystone/products/create', [ProductUiController::class, 'create'])->name('products.create');
            Route::post('keystone/products', [ProductUiController::class, 'store'])->name('products.store');
            Route::get('keystone/products/{product:identifier}', [ProductUiController::class, 'show'])->name('products.show');
            Route::patch('keystone/products/{product:identifier}', [ProductUiController::class, 'update'])->name('products.update');
            Route::delete('keystone/products/{product:identifier}', [ProductUiController::class, 'destroy'])->name('products.destroy');
            Route::post('keystone/products/{product:identifier}/transitions', [ProductUiController::class, 'transition'])->name('products.transition');
            Route::post('keystone/products/{product:identifier}/revert', [ProductUiController::class, 'revert'])->name('products.revert');

            Route::get('keystone/product-models', [ProductModelUiController::class, 'index'])->name('product-models.index');
            Route::post('keystone/product-models', [ProductModelUiController::class, 'store'])->name('product-models.store');
            Route::get('keystone/product-models/{productModel:code}', [ProductModelUiController::class, 'show'])->name('product-models.show');
            Route::patch('keystone/product-models/{productModel:code}', [ProductModelUiController::class, 'update'])->name('product-models.update');
            Route::delete('keystone/product-models/{productModel:code}', [ProductModelUiController::class, 'destroy'])->name('product-models.destroy');

            Route::get('keystone/transfers', [TransferUiController::class, 'index'])->name('transfers.index');
            Route::post('keystone/transfers/import', [TransferUiController::class, 'import'])->name('transfers.import');
            Route::post('keystone/transfers/export', [TransferUiController::class, 'export'])->name('transfers.export');

            Route::get('keystone/association-types', [AssociationUiController::class, 'index'])->name('association-types.index');
            Route::post('keystone/association-types', [AssociationUiController::class, 'store'])->name('association-types.store');
            Route::delete('keystone/association-types/{associationType:code}', [AssociationUiController::class, 'destroy'])->name('association-types.destroy');
            Route::post('keystone/associations', [AssociationUiController::class, 'add'])->name('associations.add');
            Route::delete('keystone/associations', [AssociationUiController::class, 'remove'])->name('associations.remove');

            Route::get('keystone/channels', [ChannelUiController::class, 'index'])->name('channels.index');
            Route::post('keystone/channels', [ChannelUiController::class, 'store'])->name('channels.store');
            Route::get('keystone/channels/{channel:code}', [ChannelUiController::class, 'show'])->name('channels.show');
            Route::patch('keystone/channels/{channel:code}', [ChannelUiController::class, 'update'])->name('channels.update');
            Route::delete('keystone/channels/{channel:code}', [ChannelUiController::class, 'destroy'])->name('channels.destroy');
            Route::post('keystone/locales', [ChannelUiController::class, 'storeLocale'])->name('locales.store');
            Route::delete('keystone/locales/{locale:code}', [ChannelUiController::class, 'destroyLocale'])->name('locales.destroy');

            Route::get('keystone/assets', [AssetUiController::class, 'index'])->name('assets.index');
            Route::post('keystone/assets', [AssetUiController::class, 'store'])->name('assets.store');
            Route::post('keystone/assets/upload', [AssetUiController::class, 'upload'])->name('assets.upload');
            Route::get('keystone/assets/{asset:code}', [AssetUiController::class, 'show'])->name('assets.show');
            Route::patch('keystone/assets/{asset:code}', [AssetUiController::class, 'update'])->name('assets.update');
            Route::delete('keystone/assets/{asset:code}', [AssetUiController::class, 'destroy'])->name('assets.destroy');
            Route::post('keystone/assets/{asset:code}/links', [AssetUiController::class, 'attach'])->name('assets.attach');
            Route::delete('keystone/assets/{asset:code}/links', [AssetUiController::class, 'detach'])->name('assets.detach');

            Route::get('keystone/categories', [CategoryUiController::class, 'index'])->name('categories.index');
            Route::post('keystone/categories', [CategoryUiController::class, 'store'])->name('categories.store');
            Route::get('keystone/categories/{category:code}', [CategoryUiController::class, 'show'])->name('categories.show');
            Route::patch('keystone/categories/{category:code}', [CategoryUiController::class, 'update'])->name('categories.update');
            Route::delete('keystone/categories/{category:code}', [CategoryUiController::class, 'destroy'])->name('categories.destroy');

            Route::get('keystone/owner-types', [OwnerTypeUiController::class, 'index'])->name('owner-types.index');
            Route::post('keystone/owner-types', [OwnerTypeUiController::class, 'store'])->name('owner-types.store');
            Route::get('keystone/owner-types/{ownerType:code}', [OwnerTypeUiController::class, 'show'])->name('owner-types.show');
            Route::patch('keystone/owner-types/{ownerType:code}', [OwnerTypeUiController::class, 'update'])->name('owner-types.update');
            Route::delete('keystone/owner-types/{ownerType:code}', [OwnerTypeUiController::class, 'destroy'])->name('owner-types.destroy');

            Route::get('keystone/owners', [OwnerUiController::class, 'index'])->name('owners.index');
            Route::post('keystone/owners', [OwnerUiController::class, 'store'])->name('owners.store');
            Route::get('keystone/owners/{owner:code}', [OwnerUiController::class, 'show'])->name('owners.show');
            Route::patch('keystone/owners/{owner:code}', [OwnerUiController::class, 'update'])->name('owners.update');
            Route::delete('keystone/owners/{owner:code}', [OwnerUiController::class, 'destroy'])->name('owners.destroy');

            Route::post('keystone/family-variants', [FamilyVariantUiController::class, 'store'])->name('family-variants.store');
            Route::get('keystone/family-variants/{familyVariant:code}', [FamilyVariantUiController::class, 'show'])->name('family-variants.show');
            Route::delete('keystone/family-variants/{familyVariant:code}', [FamilyVariantUiController::class, 'destroy'])->name('family-variants.destroy');

            Route::get('keystone/families', [FamilyUiController::class, 'index'])->name('families.index');
            Route::post('keystone/families', [FamilyUiController::class, 'store'])->name('families.store');
            Route::get('keystone/families/{family:code}', [FamilyUiController::class, 'show'])->name('families.show');
            Route::patch('keystone/families/{family:code}', [FamilyUiController::class, 'update'])->name('families.update');
            Route::delete('keystone/families/{family:code}', [FamilyUiController::class, 'destroy'])->name('families.destroy');

            Route::get('keystone/attribute-groups', [AttributeGroupUiController::class, 'index'])->name('attribute-groups.index');
            Route::post('keystone/attribute-groups', [AttributeGroupUiController::class, 'store'])->name('attribute-groups.store');
            Route::get('keystone/attribute-groups/{group:code}', [AttributeGroupUiController::class, 'show'])->name('attribute-groups.show');
            Route::patch('keystone/attribute-groups/{group:code}', [AttributeGroupUiController::class, 'update'])->name('attribute-groups.update');
            Route::delete('keystone/attribute-groups/{group:code}', [AttributeGroupUiController::class, 'destroy'])->name('attribute-groups.destroy');

            Route::get('keystone/attributes', [AttributeUiController::class, 'index'])->name('attributes.index');
            Route::get('keystone/attributes/create', [AttributeUiController::class, 'create'])->name('attributes.create');
            Route::post('keystone/attributes', [AttributeUiController::class, 'store'])->name('attributes.store');
            Route::get('keystone/attributes/{attribute:code}', [AttributeUiController::class, 'show'])->name('attributes.show');
            Route::patch('keystone/attributes/{attribute:code}', [AttributeUiController::class, 'update'])->name('attributes.update');
            Route::delete('keystone/attributes/{attribute:code}', [AttributeUiController::class, 'destroy'])->name('attributes.destroy');

            Route::scopeBindings()->group(function (): void {
                Route::post('keystone/attributes/{attribute:code}/options', [AttributeUiController::class, 'storeOption'])->name('attributes.options.store');
                Route::delete('keystone/attributes/{attribute:code}/options/{option:code}', [AttributeUiController::class, 'destroyOption'])->name('attributes.options.destroy');
            });
        });
    }

    /**
     * Widget types Keystone makes available.
     *
     * Returning a definition offers the widget in the picker; it does not
     * place it on anyone's dashboard.
     */
    public function widgets(): array
    {
        return [
            WidgetDefinition::make('keystone.product-status')
                ->label(__('keystone::keystone.widget_product_status'))
                ->description(__('keystone::keystone.widget_product_status_description'))
                ->defaultSize(6, 1)
                ->view('keystone::ui.widgets.product-status')
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModel::class, $request))
                ->resolve(fn (): array => [
                    'counts' => collect(ProductStatus::cases())
                        ->mapWithKeys(fn (ProductStatus $status): array => [
                            $status->value => ProductModel::query()->where('status', $status)->count(),
                        ])
                        ->all(),
                    'published' => ProductModel::query()->whereNotNull('published_version')->count(),
                ]),

            WidgetDefinition::make('keystone.completeness')
                ->label(__('keystone::keystone.widget_completeness'))
                ->description(__('keystone::keystone.widget_completeness_description'))
                ->defaultSize(6, 2)
                ->view('keystone::ui.widgets.completeness')
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModel::class, $request))
                ->resolve(fn (): array => [
                    'rows' => CompletenessModel::query()
                        ->join('keystone_channels', 'keystone_channels.id', '=', 'keystone_product_completeness.channel_id')
                        ->join('keystone_locales', 'keystone_locales.id', '=', 'keystone_product_completeness.locale_id')
                        ->groupBy('keystone_channels.code', 'keystone_locales.code')
                        ->orderBy('keystone_channels.code')
                        ->orderBy('keystone_locales.code')
                        ->selectRaw('keystone_channels.code as scope, keystone_locales.code as locale, count(*) as products, sum(case when ratio = 100 then 1 else 0 end) as complete, avg(ratio) as average')
                        ->toBase()
                        ->get()
                        ->map(fn (object $row): array => [
                            'scope' => (string) data_get($row, 'scope'),
                            'locale' => (string) data_get($row, 'locale'),
                            'products' => (int) data_get($row, 'products'),
                            'complete' => (int) data_get($row, 'complete'),
                            'average' => (int) round((float) data_get($row, 'average')),
                        ])
                        ->all(),
                ]),

            WidgetDefinition::make('keystone.review-queue')
                ->label(__('keystone::keystone.widget_review_queue'))
                ->description(__('keystone::keystone.widget_review_queue_description'))
                ->defaultSize(6, 2)
                ->view('keystone::ui.widgets.review-queue')
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModel::class, $request))
                ->resolve(fn (): array => [
                    'products' => ProductModel::query()
                        ->where('status', ProductStatus::InReview)
                        ->oldest('updated_at')
                        ->limit(5)
                        ->get(),
                    'waiting' => ProductModel::query()->where('status', ProductStatus::InReview)->count(),
                ]),

            WidgetDefinition::make('keystone.recent-changes')
                ->label(__('keystone::keystone.widget_recent_changes'))
                ->description(__('keystone::keystone.widget_recent_changes_description'))
                ->defaultSize(6, 2)
                ->view('keystone::ui.widgets.recent-changes')
                ->authorize(fn (Request $request): bool => ScreenAccess::allows('viewAny', ProductModel::class, $request))
                ->resolve(fn (): array => [
                    'versions' => VersionModel::query()
                        ->with(['versionable', 'author'])
                        ->latest('created_at')
                        ->limit(8)
                        ->get(),
                ]),
        ];
    }

    public function settings(): ?SettingsPanel
    {
        return SettingsPanel::make('keystone')
            ->label(__('keystone::keystone.settings_label'))
            ->description(__('keystone::keystone.settings_description'))
            ->view('keystone::ui.settings')
            ->resolve(fn (): array => [
                'authorization' => config('keystone.authorization') === true,
                'routes' => (array) config('keystone.routes', []),
                'mcp' => (array) config('keystone.mcp', []),
                'pagination' => (array) config('keystone.pagination', []),
            ]);
    }

    public function search(): ?SearchSource
    {
        return SearchSource::make('keystone')
            ->label(__('keystone::keystone.label'))
            // Each kind of record is searched only for those who may list it.
            ->authorize(fn (Request $request): bool => self::searchable($request))
            ->using(fn (string $query): array => [
                ...(self::mayFind(ProductModel::class)
                    ? ProductModel::query()
                        ->where('identifier', 'like', $query.'%')
                        ->orderBy('identifier')
                        ->limit(5)
                        ->get()
                        ->map(fn (ProductModel $product): SearchResult => SearchResult::make(
                            $product->identifier,
                            route('atrium.keystone.products.show', $product),
                        )->group(__('keystone::keystone.products')))
                        ->all()
                    : []),
                ...(self::mayFind(AssetModel::class)
                    ? AssetModel::query()
                        ->where(fn (Builder $builder): Builder => $builder->search($query)->orWhere('filename', 'like', '%'.$query.'%'))
                        ->orderByDesc('created_at')
                        ->limit(5)
                        ->get()
                        ->map(fn (AssetModel $asset): SearchResult => SearchResult::make(
                            $asset->label(),
                            route('atrium.keystone.assets.show', $asset),
                        )->subtitle($asset->filename)->group(__('keystone::keystone.assets')))
                        ->all()
                    : []),
                ...(self::mayFind(CategoryModel::class)
                    ? CategoryModel::query()
                        ->search($query)
                        ->orderBy('code')
                        ->limit(5)
                        ->get()
                        ->map(fn (CategoryModel $category): SearchResult => SearchResult::make(
                            $category->label(),
                            route('atrium.keystone.categories.show', $category),
                        )->subtitle($category->code)->group(__('keystone::keystone.categories')))
                        ->all()
                    : []),
                ...(self::mayFind(OwnerModel::class)
                    ? OwnerModel::query()
                        ->with('type')
                        ->search($query)
                        ->orderBy('code')
                        ->limit(5)
                        ->get()
                        ->map(fn (OwnerModel $owner): SearchResult => SearchResult::make(
                            $owner->label(),
                            route('atrium.keystone.owners.show', $owner),
                        )->subtitle($owner->code.' · '.$owner->type->code)->group(__('keystone::keystone.owners')))
                        ->all()
                    : []),
                ...(self::mayFind(FamilyModel::class)
                    ? FamilyModel::query()
                        ->search($query)
                        ->orderBy('code')
                        ->limit(5)
                        ->get()
                        ->map(fn (FamilyModel $family): SearchResult => SearchResult::make(
                            $family->label(),
                            route('atrium.keystone.families.show', $family),
                        )->subtitle($family->code)->group(__('keystone::keystone.families')))
                        ->all()
                    : []),
                ...(self::mayFind(AttributeModel::class)
                    ? AttributeModel::query()
                        ->search($query)
                        ->orderBy('code')
                        ->limit(5)
                        ->get()
                        ->map(fn (AttributeModel $attribute): SearchResult => SearchResult::make(
                            $attribute->label(),
                            route('atrium.keystone.attributes.show', $attribute),
                        )->subtitle($attribute->code.' · '.$attribute->type->value)->group(__('keystone::keystone.attributes')))
                        ->all()
                    : []),
                ...(self::mayFind(AttributeGroupModel::class)
                    ? AttributeGroupModel::query()
                        ->search($query)
                        ->orderBy('code')
                        ->limit(5)
                        ->get()
                        ->map(fn (AttributeGroupModel $group): SearchResult => SearchResult::make(
                            $group->label(),
                            route('atrium.keystone.attribute-groups.show', $group),
                        )->subtitle($group->code)->group(__('keystone::keystone.attribute_groups')))
                        ->all()
                    : []),
            ]);
    }

    /**
     * Whether the user may list any of the records search looks through.
     */
    private static function searchable(Request $request): bool
    {
        foreach (self::SEARCHED as $model) {
            if (ScreenAccess::allows('viewAny', $model, $request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the user searching may list a kind of record. Search may run in
     * another process, where Atrium signs the searcher in but no request
     * carries them, so the user comes from the guard.
     *
     * @param  class-string<Model>  $model
     */
    private static function mayFind(string $model): bool
    {
        $user = auth()->user();

        return Authorizer::for(app(PackageRegistry::class)->get('keystone'))->can($user, 'viewAny', $model);
    }
}
