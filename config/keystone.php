<?php

declare(strict_types=1);

use JayI\Keystone\Atrium\Features\KeystoneSupportFeature;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Policies\AssetPolicy;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Policies\AssociationTypePolicy;
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

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | With authorization on, the JSON API, the MCP tools and the Atrium
    | screens require an authenticated user and check every call against the
    | policies below; the screens show only the navigation and controls the
    | user may use. Turn it off only for a trusted operator surface, where
    | the route middleware (and Atrium's own gate) is the only check.
    |
    */

    'authorization' => true,

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | The policy the Gate uses for each model. The bundled policies let any
    | authenticated user manage the catalog; point a model at your own class
    | to restrict who may read or change it.
    |
    */

    'policies' => [
        AttributeGroupModel::class => AttributeGroupPolicy::class,
        AttributeModel::class => AttributePolicy::class,
        AttributeOptionModel::class => AttributeOptionPolicy::class,
        FamilyModel::class => FamilyPolicy::class,
        FamilyVariantModel::class => FamilyVariantPolicy::class,
        ProductModelModel::class => ProductModelPolicy::class,
        ProductModel::class => ProductPolicy::class,
        OwnerTypeModel::class => OwnerTypePolicy::class,
        OwnerModel::class => OwnerPolicy::class,
        CategoryModel::class => CategoryPolicy::class,
        AssetModel::class => AssetPolicy::class,
        LocaleModel::class => LocalePolicy::class,
        ChannelModel::class => ChannelPolicy::class,
        AssociationTypeModel::class => AssociationTypePolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP API Routes
    |--------------------------------------------------------------------------
    |
    | The prefix and middleware applied to the Keystone API routes. Add
    | authentication middleware before exposing these in production — they
    | change the catalog every product depends on.
    |
    */

    'routes' => [
        'enabled' => true,
        'prefix' => 'keystone',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Server
    |--------------------------------------------------------------------------
    |
    | Keystone exposes the same operations over MCP as over HTTP: both
    | surfaces call one Action, so they cannot drift. Both transports ship
    | disabled. When enabling the web transport, add auth middleware.
    |
    */

    'mcp' => [
        'web' => [
            'enabled' => false,
            'route' => 'mcp/keystone',
            'middleware' => [],
        ],
        'local' => [
            'enabled' => false,
            'handle' => 'keystone',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cortex
    |--------------------------------------------------------------------------
    |
    | When jayi/cortex is installed, the MCP server is registered with it, so
    | its instructions can be overridden, and the tools join its registry,
    | so Cortex agents can manage the catalog. Set `tools` to a list of tool
    | names, such as ['list-attributes-tool', 'show-attribute-tool'], to
    | offer only some of them.
    |
    */

    'cortex' => [
        'enabled' => true,
        'server' => 'keystone',
        'tools' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard UI
    |--------------------------------------------------------------------------
    |
    | Keystone renders its dashboard through Atrium, which owns the path,
    | middleware and authorization gate.
    |
    */

    'ui' => [

        /*
        | Whether Keystone registers itself with the Atrium dashboard. The JSON
        | API is unaffected by this switch.
        */

        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Atrium
    |--------------------------------------------------------------------------
    |
    | features: Features that must all be on for Keystone to appear in
    |           Atrium at all - its navigation, widgets, settings, search and
    |           pages (which answer 404 otherwise). Atrium asks its feature
    |           resolver, so Pennant (through jayi/pennantplus) or any other
    |           flag system decides.
    |
    |           KeystoneSupportFeature is on until its global value is set,
    |           and only its global value counts. Swap in a subclass to change
    |           that, or your own feature names. Feature classes that do not
    |           exist (without jayi/pennantplus) are skipped, so nothing is
    |           checked until Pennant is installed. Empty always shows
    |           Keystone.
    |
    | Individual pages and controls are still shown per Keystone policy.
    |
    */

    'atrium' => [
        'features' => [
            KeystoneSupportFeature::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    |
    | Products move draft → in review → approved, and `publish` makes the
    | current version the one storefronts read.
    |
    | - `require_approval`: publish only approved products. Off, any product
    |   but an archived one can be published directly.
    | - `require_complete`: submit for review only products 100% complete on
    |   every channel and locale.
    |
    */

    'workflow' => [
        'require_approval' => true,
        'require_complete' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Import, Export and Feeds (Impex)
    |--------------------------------------------------------------------------
    |
    | With jayi/impex installed, Keystone registers its flows:
    | `keystone:import-products`, `keystone:upsert-products`,
    | `keystone:export-products`, and `keystone:feed:{name}` for each feed.
    |
    | - `chunk`, `allow_failures` (a share, 0.02 = 2%), `tries`: how imports
    |   batch, how many rows may fail before the run fails, retries per row.
    |   By default every row may fail: the run completes and reports how many
    |   did, with each failed row's reason kept by Impex.
    | - `export_page_size`, `export_path`: how exports are paged and where
    |   their files go (on the media disk).
    | - `feeds`: syndication feeds, each a channel's published products:
    |   'google' => ['channel' => 'ecommerce', 'format' => 'jsonl',
    |                'url' => 'https://…', 'ledger_channel' => 'google-feed'],
    |   Schedule one with Impex: 'schedule' => ['keystone:feed:google' => '0 3 * * *'].
    |
    */

    'impex' => [
        'enabled' => true,
        'chunk' => 500,
        'allow_failures' => 1.0,
        'tries' => 1,
        'export_page_size' => 500,
        'export_path' => 'keystone/exports',
        'feeds' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Where asset files live. Any Laravel filesystem disk works; use S3 (or
    | another object store) on Vapor or Lambda, where the local disk does not
    | outlive the request. Files are streamed, never held whole in memory.
    |
    | - `disk`: a disk from config/filesystems.php; null for the default.
    | - `path`: the folder assets are written under.
    | - `max_kilobytes`: the largest file accepted.
    | - `mime_types`: allowed MIME types, or null for any.
    | - `temporary_urls`: minutes a signed URL lasts, for private buckets;
    |   null serves plain URLs.
    | - `delete_files`: whether deleting an asset deletes its file.
    | - `download_timeout`: seconds allowed to fetch an asset from a URL.
    |
    */

    'media' => [
        'disk' => null,
        'path' => 'keystone/assets',
        'max_kilobytes' => 51200,
        'mime_types' => null,
        'temporary_urls' => null,
        'delete_files' => true,
        'download_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    |
    | Where products are searched and listed.
    |
    | - "database": no extra service; reads the products table. Fine for
    |   thousands of products.
    | - "elasticsearch": native, through jayi/stretch, which holds the
    |   connection settings (config/stretch.php). Filters, ranges and facets.
    | - "scout": whichever Laravel Scout engine `scout.driver` names. Only
    |   equality filters.
    | - or the class name of your own JayI\Keystone\Domains\Search\Contracts\SearchEngine.
    |
    | Writes are synced to the index by a queued job after each commit.
    | Rebuild it with `php artisan keystone:search:reindex`.
    |
    */

    'search' => [
        'engine' => 'database',

        'queue' => [
            'connection' => null,
            'queue' => null,
        ],

        'elasticsearch' => [
            'index' => 'keystone_products',
            // A connection name from config/stretch.php, or null for its default.
            'connection' => null,
        ],

        'scout' => [
            'index' => 'keystone_products',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Page sizes for every listing, over HTTP, MCP and the dashboard. Callers
    | may ask for `per_page` up to the maximum.
    |
    */

    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100,
    ],

];
