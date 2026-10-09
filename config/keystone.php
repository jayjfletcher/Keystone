<?php

declare(strict_types=1);

use RefactorCircus\Keystone\Atrium\Features\KeystoneSupportFeature;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Asset\Policies\AssetPolicy;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Association\Policies\AssociationTypePolicy;
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
use RefactorCircus\Keystone\Impex\Webhooks\ProductStream;

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
    | When refactor-circus/cortex is installed, the MCP server is registered with it, so
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
    |           resolver, so Pennant (through refactor-circus/pennantplus) or any other
    |           flag system decides.
    |
    |           KeystoneSupportFeature is on until its global value is set,
    |           and only its global value counts. Swap in a subclass to change
    |           that, or your own feature names. Feature classes that do not
    |           exist (without refactor-circus/pennantplus) are skipped, so nothing is
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
    | With refactor-circus/impex installed, Keystone registers its flows:
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
    |   Or name an outbound Impex channel with 'deliver_through' => 'google-sftp'
    |   to send the file the channel's way: its transport, signing and headers.
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

        /*
        | Product webhooks: published products as an Impex stream, so vendors
        | subscribe to the products and topics they want and are pushed
        | changes (or pull them from a feed) instead of polling the API.
        | Subscribers, endpoints and deliveries are managed through Impex.
        |
        | - `enabled`: off until someone subscribes; once on, every product
        |   write is compared with what subscribers last saw.
        | - `stream`: the stream's key. `stream_class` swaps the stream.
        | - `topics`: what a subscriber picks from, in a fixed order — append
        |   new ones, never reorder or remove one. A topic claims attribute
        |   values by `types` or attribute `groups`, product `fields`, and
        |   linked `assets`; the `default` topic takes the rest.
        | - `formatters`: extra payload formats, name => Formatter class,
        |   beside thin, slice and full.
        */

        'webhooks' => [
            'enabled' => false,
            'stream' => 'keystone.products',
            'stream_class' => ProductStream::class,
            'topics' => [
                'content' => ['default' => true],
                'pricing' => ['types' => ['price']],
                'assets' => ['assets' => true],
                'resources' => ['groups' => []],
                'catalog' => ['fields' => ['family', 'parent', 'owner', 'enabled', 'categories', 'associations', 'quantified_associations']],
            ],
            'formatters' => [],
        ],
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
    | - null: "scout" when laravel/scout is installed, "database" otherwise.
    | - "database": no extra service; reads the products table. Fine for
    |   thousands of products.
    | - "scout": whichever Laravel Scout engine `scout.driver` names —
    |   Meilisearch, Typesense, Algolia, or a community driver such as one
    |   for Elasticsearch.
    | - or the class name of your own RefactorCircus\Keystone\Domains\Search\Contracts\SearchEngine.
    |
    | Writes are synced to the index by a queued job after each commit.
    | Rebuild it with `php artisan keystone:search:reindex`.
    |
    */

    'search' => [
        'engine' => null,

        'queue' => [
            'connection' => null,
            'queue' => null,
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
