<?php

declare(strict_types=1);

namespace JayI\Keystone\Mcp;

use JayI\Foundation\Mcp\Server;
use JayI\Keystone\Domains\Asset\Mcp\Tools\AttachAssetTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\CreateAssetTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\DeleteAssetTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\DetachAssetTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\ListAssetsTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\ShowAssetTool;
use JayI\Keystone\Domains\Asset\Mcp\Tools\UpdateAssetTool;
use JayI\Keystone\Domains\Association\Mcp\Tools\CreateAssociationTypeTool;
use JayI\Keystone\Domains\Association\Mcp\Tools\DeleteAssociationTypeTool;
use JayI\Keystone\Domains\Association\Mcp\Tools\ListAssociationTypesTool;
use JayI\Keystone\Domains\Association\Mcp\Tools\ShowAssociationTypeTool;
use JayI\Keystone\Domains\Association\Mcp\Tools\UpdateAssociationTypeTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\CreateAttributeGroupTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\CreateAttributeOptionTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\CreateAttributeTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\DeleteAttributeGroupTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\DeleteAttributeOptionTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\DeleteAttributeTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ListAttributeGroupsTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ListAttributeOptionsTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ListAttributesTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ShowAttributeGroupTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ShowAttributeTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\UpdateAttributeGroupTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\UpdateAttributeOptionTool;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\UpdateAttributeTool;
use JayI\Keystone\Domains\Category\Mcp\Tools\CreateCategoryTool;
use JayI\Keystone\Domains\Category\Mcp\Tools\DeleteCategoryTool;
use JayI\Keystone\Domains\Category\Mcp\Tools\ListCategoriesTool;
use JayI\Keystone\Domains\Category\Mcp\Tools\ShowCategoryTool;
use JayI\Keystone\Domains\Category\Mcp\Tools\UpdateCategoryTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\CreateChannelTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\CreateLocaleTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\DeleteChannelTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\DeleteLocaleTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\ListChannelsTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\ListLocalesTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\ShowChannelTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\ShowLocaleTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\UpdateChannelTool;
use JayI\Keystone\Domains\Channel\Mcp\Tools\UpdateLocaleTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\CreateFamilyTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\CreateFamilyVariantTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\DeleteFamilyTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\DeleteFamilyVariantTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\ListFamiliesTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\ListFamilyVariantsTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\ShowFamilyTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\ShowFamilyVariantTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\UpdateFamilyTool;
use JayI\Keystone\Domains\Family\Mcp\Tools\UpdateFamilyVariantTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\CreateOwnerTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\CreateOwnerTypeTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\DeleteOwnerTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\DeleteOwnerTypeTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\ListOwnersTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\ListOwnerTypesTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\ShowOwnerTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\ShowOwnerTypeTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\UpdateOwnerTool;
use JayI\Keystone\Domains\Owner\Mcp\Tools\UpdateOwnerTypeTool;
use JayI\Keystone\Domains\Product\Mcp\Tools\CreateProductTool;
use JayI\Keystone\Domains\Product\Mcp\Tools\DeleteProductTool;
use JayI\Keystone\Domains\Product\Mcp\Tools\ListProductsTool;
use JayI\Keystone\Domains\Product\Mcp\Tools\ShowProductTool;
use JayI\Keystone\Domains\Product\Mcp\Tools\UpdateProductTool;
use JayI\Keystone\Domains\ProductModel\Mcp\Tools\CreateProductModelTool;
use JayI\Keystone\Domains\ProductModel\Mcp\Tools\DeleteProductModelTool;
use JayI\Keystone\Domains\ProductModel\Mcp\Tools\ListProductModelsTool;
use JayI\Keystone\Domains\ProductModel\Mcp\Tools\ShowProductModelTool;
use JayI\Keystone\Domains\ProductModel\Mcp\Tools\UpdateProductModelTool;
use JayI\Keystone\Domains\Transfer\Mcp\Tools\StartExportTool;
use JayI\Keystone\Domains\Transfer\Mcp\Tools\StartImportTool;
use JayI\Keystone\Domains\Workflow\Mcp\Tools\ListProductVersionsTool;
use JayI\Keystone\Domains\Workflow\Mcp\Tools\RevertProductTool;
use JayI\Keystone\Domains\Workflow\Mcp\Tools\ShowProductVersionTool;
use JayI\Keystone\Domains\Workflow\Mcp\Tools\TransitionProductTool;
use JayI\Keystone\Mcp\Tools\ListKeystoneHistoryTool;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolSearch;

#[Name('Keystone')]
#[Version('1.0.0')]
#[Instructions(
    'Manage the Keystone product catalog (PIM). Attributes are the typed characteristics products are described '.
    'with — color, weight, description — and are defined at runtime. Every record is addressed by its code, '.
    'which never changes once created; an attribute\'s type never changes either, so choose both carefully. '.
    'Attribute groups only organise attributes into sections; an attribute belongs to at most one. Select and '.
    'multiselect attributes take a list of options; no other type does. A family (attribute set) is a kind of '.
    'product: the attributes it has and which of them are required. Products are identified by identifier '.
    '(SKU) and hold values keyed by attribute code, as lists of {locale, scope, data} slots. A family variant '.
    'says how a family\'s products vary: one or two levels of axes. A root product model holds the common '.
    'values; in two-level variants, sub-models hold level-1 values; variant products hold the last level and '.
    'inherit the rest. Owners (vendors, series, ...) form chains of runtime-defined owner types; a product or '.
    'root product model belongs to the deepest owner. Categories form independent trees (a root category is a '.
    'tree); products and product models are filed in any number of them, and variants inherit their models\' '.
    'categories. Assets (images, documents) are added from a URL and linked to products, product models or '.
    'owners under a role; variants show their models\' assets too. Localizable values need an existing locale; '.
    'scopable values an existing channel, and a value that is both must use one of that channel\'s locales. '.
    'Reads accept scope and locales to return one channel\'s values. Associations relate products and models '.
    'by type (cross_sell, accessories, ...); two-way types mirror themselves; quantified types hold bundle and '.
    'kit components with quantities. Products carry completeness per channel and locale, move draft → in_review → '.
    'approved through transition-product-tool, and publish snapshots a version for storefronts; every write is '.
    'versioned and can be reverted. With Impex installed, start-import-tool and start-export-tool run bulk '.
    'imports and exports as Impex runs; with jayi/keen installed, list-keystone-history-tool lists who changed what. Labels are keyed by locale. Product search is page-numbered and returns a total; other '.
    'listings are cursor paginated: pass next_cursor back as cursor for the next page.',
)]
final class KeystoneServer extends Server
{
    /**
     * Every tool the server offers, behind ToolSearch. Also registered with
     * Cortex when it is installed.
     *
     * @var array<int, class-string<Tool>>
     */
    public const array TOOLS = [
        // Attribute groups
        ListAttributeGroupsTool::class,
        ShowAttributeGroupTool::class,
        CreateAttributeGroupTool::class,
        UpdateAttributeGroupTool::class,
        DeleteAttributeGroupTool::class,

        // Attributes
        ListAttributesTool::class,
        ShowAttributeTool::class,
        CreateAttributeTool::class,
        UpdateAttributeTool::class,
        DeleteAttributeTool::class,

        // Attribute options
        ListAttributeOptionsTool::class,
        CreateAttributeOptionTool::class,
        UpdateAttributeOptionTool::class,
        DeleteAttributeOptionTool::class,

        // Families
        ListFamiliesTool::class,
        ShowFamilyTool::class,
        CreateFamilyTool::class,
        UpdateFamilyTool::class,
        DeleteFamilyTool::class,

        // Family variants
        ListFamilyVariantsTool::class,
        ShowFamilyVariantTool::class,
        CreateFamilyVariantTool::class,
        UpdateFamilyVariantTool::class,
        DeleteFamilyVariantTool::class,

        // Product models
        ListProductModelsTool::class,
        ShowProductModelTool::class,
        CreateProductModelTool::class,
        UpdateProductModelTool::class,
        DeleteProductModelTool::class,

        // Products
        ListProductsTool::class,
        ShowProductTool::class,
        CreateProductTool::class,
        UpdateProductTool::class,
        DeleteProductTool::class,
        TransitionProductTool::class,
        ListProductVersionsTool::class,
        ShowProductVersionTool::class,
        RevertProductTool::class,

        // Ownership
        ListOwnerTypesTool::class,
        ShowOwnerTypeTool::class,
        CreateOwnerTypeTool::class,
        UpdateOwnerTypeTool::class,
        DeleteOwnerTypeTool::class,
        ListOwnersTool::class,
        ShowOwnerTool::class,
        CreateOwnerTool::class,
        UpdateOwnerTool::class,
        DeleteOwnerTool::class,

        // Taxonomy
        ListCategoriesTool::class,
        ShowCategoryTool::class,
        CreateCategoryTool::class,
        UpdateCategoryTool::class,
        DeleteCategoryTool::class,

        // Media
        ListAssetsTool::class,
        ShowAssetTool::class,
        CreateAssetTool::class,
        UpdateAssetTool::class,
        DeleteAssetTool::class,
        AttachAssetTool::class,
        DetachAssetTool::class,

        // Channels and locales
        ListLocalesTool::class,
        ShowLocaleTool::class,
        CreateLocaleTool::class,
        UpdateLocaleTool::class,
        DeleteLocaleTool::class,
        ListChannelsTool::class,
        ShowChannelTool::class,
        CreateChannelTool::class,
        UpdateChannelTool::class,
        DeleteChannelTool::class,

        // Associations
        ListAssociationTypesTool::class,
        ShowAssociationTypeTool::class,
        CreateAssociationTypeTool::class,
        UpdateAssociationTypeTool::class,
        DeleteAssociationTypeTool::class,

        // Import and export (with jayi/impex)
        StartImportTool::class,
        StartExportTool::class,

        // Audit history (with jayi/keen)
        ListKeystoneHistoryTool::class,
    ];

    /**
     * @var array<class-string<ToolSearch>, array<int, class-string<Tool>|Tool>>
     */
    protected array $tools = [
        ToolSearch::class => self::TOOLS,
    ];
}
