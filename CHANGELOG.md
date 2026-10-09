# Release Notes

## [Unreleased](https://github.com/Refactor-Circus/Keystone/commits/main)

### Breaking

- Moved to the Refactor Circus organisation: the package is now `refactor-circus/keystone` with the PHP namespace `RefactorCircus\Keystone` (it was `jayi/keystone` and `JayI\Keystone`). Update `composer.json` requirements and `use` statements. Old class names are not kept as aliases, so stored values written under them - polymorphic `*_type` columns, audit subjects, Pennant feature names - need updating to the new names.

### Added

- **Product webhooks** (`keystone.impex.webhooks`, off by default): published products as an Impex stream (`keystone.products`) that vendors subscribe to by category, owner, family, model or SKU, and by topic — `content`, `pricing`, `assets`, `resources`, `catalog` — in `thin`, `slice` or `full` format, narrowed to a channel and locales. Changes are pushed signed and batched, or pulled from a feed; unpublished, deleted and out-of-scope products reach vendors as removed; full exports start a new vendor off. Product changes are reported to Impex from `ProductIndex::queue()` (which now fires `RefactorCircus\Keystone\Domains\Search\Events\ProductsQueuedForSync`), deletions and asset links.
- Feeds can deliver through any Impex outbound channel with `deliver_through`, taking the channel's transport, signing and headers.
- `updated_since` on `GET /keystone/products`, `list-products-tool` and `ListProductsAction`, on every search engine. It filters on a new `changed_at` column (in the product resource too), set by `SyncProductIndex` whenever anything a product shows changes — inherited changes, refilings, associations and assets included — where `updated_at` moves only with the product's own row.
- `GET /keystone/products/{identifier}` sends an `ETag` and answers `304` to a matching `If-None-Match`.
- The package's section in Atrium's sidebar rail has its own icon (`cube`) and a fixed place in the rail.
- An **Audit log** link in the package's sidebar group, opening its own audit log in Atrium (`/atrium/history/keystone`), shown while an audit log (refactor-circus/keen) is installed and to those who may read the package's history.

### Breaking

- The Elasticsearch engine and its `refactor-circus/stretch` dependency are removed, with the `keystone.search.elasticsearch` config. `keystone.search.engine` now defaults to `null`: Scout when `laravel/scout` is installed, the database otherwise. An application still naming `elasticsearch` gets an error pointing at a Scout driver or its own `SearchEngine`. Facets remain in the search contract for custom engines; the bundled engines return none.
- Keystone ships no stylesheet: `resources/css/atrium.css` and its registration with Atrium's style hook are removed. The screens use only Atrium's components and safelisted utilities (bare form controls in table cells, `description-list`, `progress`, `flash`), and `ui/partials/status.blade.php` is replaced by `<x-atrium::flash />`, which also shows the first validation error. Requires a refactor-circus/atrium with those components. Published views that include `keystone::ui.partials.status` must switch to `<x-atrium::flash />`.
- `RefactorCircus\Keystone\Atrium\Http\Controllers\Concerns\AuthorizesScreens` is removed; the screen controllers use Atrium's `RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens`. `ScreenAccess::allows()` now delegates to `RefactorCircus\Atrium\Support\ScreenAccess`, and `KeystonePlugin` uses the base plugin's `featuresFromConfig()`, `key()` and `label()`.

- Keystone now stands on [refactor-circus/foundation](https://github.com/jayjfletcher/Foundation), the runtime the Refactor Circus packages share, and its local copies are removed in favour of Foundation's classes:
  - `RefactorCircus\Keystone\Contracts\{ActionStartingEvent,ActionFinishedEvent,ModelLifecycleEvent}` → `RefactorCircus\Foundation\Contracts\*`. Listening to a Foundation contract now hears every package of the suite.
  - `RefactorCircus\Keystone\Support\Models\Concerns\DispatchesModelEvents` → `RefactorCircus\Foundation\Models\Concerns\DispatchesModelEvents`. A subclass of a Keystone model now fires the Keystone model's events.
  - `RefactorCircus\Keystone\Support\ServiceProvider` → `RefactorCircus\Foundation\Support\ServiceProvider`; `RefactorCircus\Keystone\Support\Authorizer` → `RefactorCircus\Foundation\Auth\Authorizer::for($package)`.
  - `RefactorCircus\Keystone\Http\Request` → `RefactorCircus\Foundation\Http\Requests\Request`; `RefactorCircus\Keystone\Mcp\Request` → `RefactorCircus\Foundation\Mcp\Requests\Request`, whose calls run inside the `mcp` surface; `RefactorCircus\Keystone\Mcp\Tool` → `RefactorCircus\Foundation\Mcp\Tool`.
  - `RefactorCircus\Keystone\Cortex\CortexIntegration` → `RefactorCircus\Foundation\Cortex\CortexIntegration::for($package)`, which also marks agent tool calls with the `cortex` surface.
  - `KeystoneServiceProvider` extends `PackageServiceProvider`, and `KeystoneServer` extends `RefactorCircus\Foundation\Mcp\Server`. `KeystoneException` is now an abstract `RefactorCircus\Foundation\Exceptions\PackageException` that still answers 409, and the bundled policy base extends Foundation's `Policy`. Config keys, route names and tool names are unchanged.
- The source is reorganised into domain modules under `src/Domains/{Domain}` (`RefactorCircus\Keystone\Domains\{Domain}`), each with its own service provider registered by `RefactorCircus\Keystone\Domains\DomainServiceProvider`: **Product**, **ProductModel**, **Attribute** (attributes, groups, options, values), **Family** (families and family variants), **Owner** (owners and owner types), **Category**, **Channel** (channels and locales), **Association**, **Asset**, **Workflow** (versions, completeness, transitions, revert), **Search** and **Transfer** (starting Impex imports and exports). Cross-domain code moved to `RefactorCircus\Keystone\Support`, and the Atrium screens to `RefactorCircus\Keystone\Atrium`. `KeystoneServiceProvider` keeps its name. Config keys, route names, MCP tool names, views, translations, publish tags and `@keystoneCan` are unchanged. There are no aliases for the old class names.
- The Eloquent models are renamed to end in `Model`. Their old class names are kept as morph aliases, so any `*_type` value written under them still resolves; products, product models and owners keep writing their `keystone_product`, `keystone_product_model` and `keystone_owner` aliases, and the other models keep writing their old class names. Point published `keystone.policies` keys at the new names:
  - `RefactorCircus\Keystone\Models\Asset` → `RefactorCircus\Keystone\Domains\Asset\Models\AssetModel`
  - `RefactorCircus\Keystone\Models\Association` → `RefactorCircus\Keystone\Domains\Association\Models\AssociationModel`
  - `RefactorCircus\Keystone\Models\AssociationType` → `RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel`
  - `RefactorCircus\Keystone\Models\Attribute` → `RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel`
  - `RefactorCircus\Keystone\Models\AttributeGroup` → `RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel`
  - `RefactorCircus\Keystone\Models\AttributeOption` → `RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel`
  - `RefactorCircus\Keystone\Models\Category` → `RefactorCircus\Keystone\Domains\Category\Models\CategoryModel`
  - `RefactorCircus\Keystone\Models\Channel` → `RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel`
  - `RefactorCircus\Keystone\Models\Completeness` → `RefactorCircus\Keystone\Domains\Workflow\Models\CompletenessModel`
  - `RefactorCircus\Keystone\Models\Family` → `RefactorCircus\Keystone\Domains\Family\Models\FamilyModel`
  - `RefactorCircus\Keystone\Models\FamilyVariant` → `RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel`
  - `RefactorCircus\Keystone\Models\Locale` → `RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel`
  - `RefactorCircus\Keystone\Models\Owner` → `RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel`
  - `RefactorCircus\Keystone\Models\OwnerType` → `RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel`
  - `RefactorCircus\Keystone\Models\Product` → `RefactorCircus\Keystone\Domains\Product\Models\ProductModel`
  - `RefactorCircus\Keystone\Models\ProductModel` → `RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel`
  - `RefactorCircus\Keystone\Models\Version` → `RefactorCircus\Keystone\Domains\Workflow\Models\VersionModel`
- Relations now name their foreign and pivot keys, which Eloquent used to guess from the old class names; factories' `for()` needs the relationship name where it guessed it from a class (`AttributeOptionModel::factory()->for($attribute, 'attribute')`).
- `KeystoneSupportFeature` moved to `RefactorCircus\Keystone\Atrium\Features` and keeps its stored Pennant name, `RefactorCircus\Keystone\Features\KeystoneSupportFeature`, through Pennant's `#[Name]` attribute.
- `routes/keystone.php` is replaced by a `routes.php` per domain, loaded inside the same `keystone.` group; the routes, their names and middleware are unchanged.
- Model events (formerly `Events\Model`) and action events (formerly `Events\Action`) now live in their domain's `Events` namespace, keeping their class names. Model events are found from the model's domain and its name less the `Model` suffix.
- Unchanged, because Impex runs and queued payloads store their class names: `RefactorCircus\Keystone\Impex\*` (flows, sources, actions) and `RefactorCircus\Keystone\Jobs\*`.
- Actions, HTTP requests, MCP tools and MCP requests keep their class names and move to their domain:
  - `RefactorCircus\Keystone\Domains\Asset\Actions`: every Action for the domain (7 classes)
  - `RefactorCircus\Keystone\Domains\Association\Actions`: every Action for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Attribute\Actions`: every Action for the domain (14 classes)
  - `RefactorCircus\Keystone\Domains\Category\Actions`: every Action for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Channel\Actions`: every Action for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\Family\Actions`: every Action for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\Owner\Actions`: every Action for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\Product\Actions`: every Action for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\ProductModel\Actions`: every Action for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Workflow\Actions`: every Action for the domain (4 classes)
  - `RefactorCircus\Keystone\Domains\Transfer\Actions`: every Action for the domain (2 classes)
  - `RefactorCircus\Keystone\Domains\Asset\Http\Requests`: every HTTP request for the domain (8 classes)
  - `RefactorCircus\Keystone\Domains\Association\Http\Requests`: every HTTP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Attribute\Http\Requests`: every HTTP request for the domain (17 classes)
  - `RefactorCircus\Keystone\Domains\Category\Http\Requests`: every HTTP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Channel\Http\Requests`: every HTTP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\Family\Http\Requests`: every HTTP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\Owner\Http\Requests`: every HTTP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\ProductModel\Http\Requests`: every HTTP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Product\Http\Requests`: every HTTP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Workflow\Http\Requests`: every HTTP request for the domain (4 classes)
  - `RefactorCircus\Keystone\Domains\Transfer\Http\Requests`: every HTTP request for the domain (2 classes)
  - `RefactorCircus\Keystone\Domains\Asset\Mcp\Requests`: every MCP request for the domain (8 classes)
  - `RefactorCircus\Keystone\Domains\Association\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests`: every MCP request for the domain (17 classes)
  - `RefactorCircus\Keystone\Domains\Category\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Channel\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\Family\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\Owner\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `RefactorCircus\Keystone\Domains\Product\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\ProductModel\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `RefactorCircus\Keystone\Domains\Workflow\Mcp\Requests`: every MCP request for the domain (4 classes)
  - `RefactorCircus\Keystone\Domains\Transfer\Mcp\Requests`: every MCP request for the domain (2 classes)
  - `RefactorCircus\Keystone\Domains\Asset\Mcp\Tools`: every MCP tool for the domain (7 classes)
  - `RefactorCircus\Keystone\Domains\Association\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Attribute\Mcp\Tools`: every MCP tool for the domain (14 classes)
  - `RefactorCircus\Keystone\Domains\Category\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Channel\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\Family\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\Owner\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `RefactorCircus\Keystone\Domains\ProductModel\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Product\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `RefactorCircus\Keystone\Domains\Workflow\Mcp\Tools`: every MCP tool for the domain (4 classes)
  - `RefactorCircus\Keystone\Domains\Transfer\Mcp\Tools`: every MCP tool for the domain (2 classes)
  - `RefactorCircus\Keystone\Domains\Asset\Events`: every model and action event for the domain (24 classes)
  - `RefactorCircus\Keystone\Domains\Association\Events`: every model and action event for the domain (30 classes)
  - `RefactorCircus\Keystone\Domains\Attribute\Events`: every model and action event for the domain (58 classes)
  - `RefactorCircus\Keystone\Domains\Category\Events`: every model and action event for the domain (20 classes)
  - `RefactorCircus\Keystone\Domains\Channel\Events`: every model and action event for the domain (40 classes)
  - `RefactorCircus\Keystone\Domains\Transfer\Events`: every model and action event for the domain (4 classes)
  - `RefactorCircus\Keystone\Domains\Family\Events`: every model and action event for the domain (40 classes)
  - `RefactorCircus\Keystone\Domains\Owner\Events`: every model and action event for the domain (40 classes)
  - `RefactorCircus\Keystone\Domains\Product\Events`: every model and action event for the domain (20 classes)
  - `RefactorCircus\Keystone\Domains\ProductModel\Events`: every model and action event for the domain (20 classes)
  - `RefactorCircus\Keystone\Domains\Workflow\Events`: every model and action event for the domain (28 classes)
- Every other moved class, by its new namespace (old names relative to `RefactorCircus\Keystone`):
  - `RefactorCircus\Keystone\Support`: `Access\Authorizer`
  - `RefactorCircus\Keystone\Domains\Category\Concerns`: `Actions\Concerns\AssignsCategories`
  - `RefactorCircus\Keystone\Domains\Owner\Concerns`: `Actions\Concerns\AssignsOwners`, `Actions\Concerns\PlacesOwners`, `Actions\Concerns\WritesOwnerTypes`
  - `RefactorCircus\Keystone\Support\Concerns`: `Actions\Concerns\MovesInTree`
  - `RefactorCircus\Keystone\Domains\Asset\Concerns`: `Actions\Concerns\StoresAssetFiles`, `Models\Concerns\HasAssets`
  - `RefactorCircus\Keystone\Domains\Channel\Concerns`: `Actions\Concerns\WritesChannels`
  - `RefactorCircus\Keystone\Domains\Family\Concerns`: `Actions\Concerns\WritesFamilyAttributes`, `Actions\Concerns\WritesFamilyVariantLevels`
  - `RefactorCircus\Keystone\Domains\Product\Concerns`: `Actions\Concerns\WritesProducts`
  - `RefactorCircus\Keystone\Domains\Attribute\Concerns`: `Actions\Concerns\WritesValues`, `Models\Concerns\HasValues`
  - `RefactorCircus\Keystone\Domains\Association\Services`: `Associations\Associations`
  - `RefactorCircus\Keystone\Domains\Search\Console\Commands`: `Console\Commands\ReindexProductsCommand`
  - `RefactorCircus\Keystone\Domains\Search\Contracts`: `Contracts\SearchEngine`
  - `RefactorCircus\Keystone\Domains\Attribute\Enums`: `Enums\AttributeType`
  - `RefactorCircus\Keystone\Domains\Product\Enums`: `Enums\ProductStatus`
  - `RefactorCircus\Keystone\Domains\Workflow\Enums`: `Enums\Transition`
  - `RefactorCircus\Keystone\Domains\Attribute\Exceptions`: `Exceptions\AttributeGroupNotEmptyException`, `Exceptions\AttributeHasNoOptionsException`, `Exceptions\AttributeLabelsFamiliesException`
  - `RefactorCircus\Keystone\Domains\Transfer\Exceptions`: `Exceptions\ImpexMissingException`
  - `RefactorCircus\Keystone\Domains\Workflow\Exceptions`: `Exceptions\InvalidTransitionException`
  - `RefactorCircus\Keystone\Domains\Search\Exceptions`: `Exceptions\UnsupportedSearchException`
  - `RefactorCircus\Keystone\Atrium\Features`: `Features\KeystoneSupportFeature`
  - `RefactorCircus\Keystone\Domains\Asset\Http\Controllers`: `Http\Controllers\AssetController`
  - `RefactorCircus\Keystone\Domains\Association\Http\Controllers`: `Http\Controllers\AssociationTypeController`
  - `RefactorCircus\Keystone\Domains\Attribute\Http\Controllers`: `Http\Controllers\AttributeController`, `Http\Controllers\AttributeGroupController`, `Http\Controllers\AttributeOptionController`
  - `RefactorCircus\Keystone\Domains\Category\Http\Controllers`: `Http\Controllers\CategoryController`
  - `RefactorCircus\Keystone\Domains\Channel\Http\Controllers`: `Http\Controllers\ChannelController`, `Http\Controllers\LocaleController`
  - `RefactorCircus\Keystone\Domains\Family\Http\Controllers`: `Http\Controllers\FamilyController`, `Http\Controllers\FamilyVariantController`
  - `RefactorCircus\Keystone\Domains\Transfer\Http\Controllers`: `Http\Controllers\ImpexController`
  - `RefactorCircus\Keystone\Domains\Owner\Http\Controllers`: `Http\Controllers\OwnerController`, `Http\Controllers\OwnerTypeController`
  - `RefactorCircus\Keystone\Domains\Product\Http\Controllers`: `Http\Controllers\ProductController`
  - `RefactorCircus\Keystone\Domains\ProductModel\Http\Controllers`: `Http\Controllers\ProductModelController`
  - `RefactorCircus\Keystone\Domains\Workflow\Http\Controllers`: `Http\Controllers\ProductWorkflowController`
  - `RefactorCircus\Keystone\Domains\Asset\Resources`: `Http\Resources\AssetResource`, `Http\Resources\LinkedAssets`
  - `RefactorCircus\Keystone\Domains\Association\Resources`: `Http\Resources\AssociationTypeResource`
  - `RefactorCircus\Keystone\Domains\Attribute\Resources`: `Http\Resources\AttributeGroupResource`, `Http\Resources\AttributeOptionResource`, `Http\Resources\AttributeResource`
  - `RefactorCircus\Keystone\Domains\Category\Resources`: `Http\Resources\CategoryResource`
  - `RefactorCircus\Keystone\Domains\Channel\Resources`: `Http\Resources\ChannelResource`, `Http\Resources\LocaleResource`
  - `RefactorCircus\Keystone\Domains\Family\Resources`: `Http\Resources\FamilyAttributeResource`, `Http\Resources\FamilyResource`, `Http\Resources\FamilyVariantResource`
  - `RefactorCircus\Keystone\Domains\Owner\Resources`: `Http\Resources\OwnerResource`, `Http\Resources\OwnerTypeResource`
  - `RefactorCircus\Keystone\Domains\ProductModel\Resources`: `Http\Resources\ProductModelResource`
  - `RefactorCircus\Keystone\Domains\Product\Resources`: `Http\Resources\ProductResource`
  - `RefactorCircus\Keystone\Domains\Workflow\Resources`: `Http\Resources\VersionResource`, `Http\Resources\VersionSummaryResource`
  - `RefactorCircus\Keystone\Atrium\Http\Controllers`: `Http\Ui\AssetUiController`, `Http\Ui\AssociationUiController`, `Http\Ui\AttributeGroupUiController`, `Http\Ui\AttributeUiController`, `Http\Ui\CategoryUiController`, `Http\Ui\ChannelUiController`, `Http\Ui\FamilyUiController`, `Http\Ui\FamilyVariantUiController`, `Http\Ui\OwnerTypeUiController`, `Http\Ui\OwnerUiController`, `Http\Ui\ProductModelUiController`, `Http\Ui\ProductUiController`, `Http\Ui\TransferUiController`
  - `RefactorCircus\Keystone\Atrium\Support`: `Http\Ui\CategoryCodes`, `Http\Ui\EditingSlot`, `Http\Ui\Labels`, `Http\Ui\ValueForm`
  - `RefactorCircus\Keystone\Atrium\Http\Controllers\Concerns`: `Http\Ui\Concerns\AuthorizesScreens`
  - `RefactorCircus\Keystone\Atrium`: `Http\Ui\ScreenAccess`
  - `RefactorCircus\Keystone\Domains\Asset\Services`: `Media\AssetLinks`, `Media\AssetStorage`
  - `RefactorCircus\Keystone\Domains\Asset\Data`: `Media\StoredFile`
  - `RefactorCircus\Keystone\Support\Models\Concerns`: `Models\Concerns\DispatchesModelEvents`, `Models\Concerns\HasLabels`, `Models\Concerns\HasPath`
  - `RefactorCircus\Keystone\Domains\Association\Concerns`: `Models\Concerns\HasAssociations`
  - `RefactorCircus\Keystone\Domains\Asset\Policies`: `Policies\AssetPolicy`
  - `RefactorCircus\Keystone\Domains\Association\Policies`: `Policies\AssociationTypePolicy`
  - `RefactorCircus\Keystone\Domains\Attribute\Policies`: `Policies\AttributeGroupPolicy`, `Policies\AttributeOptionPolicy`, `Policies\AttributePolicy`
  - `RefactorCircus\Keystone\Domains\Category\Policies`: `Policies\CategoryPolicy`
  - `RefactorCircus\Keystone\Domains\Channel\Policies`: `Policies\ChannelPolicy`, `Policies\LocalePolicy`
  - `RefactorCircus\Keystone\Domains\Family\Policies`: `Policies\FamilyPolicy`, `Policies\FamilyVariantPolicy`
  - `RefactorCircus\Keystone\Domains\Owner\Policies`: `Policies\OwnerPolicy`, `Policies\OwnerTypePolicy`
  - `RefactorCircus\Keystone\Support\Policies`: `Policies\Policy`
  - `RefactorCircus\Keystone\Domains\ProductModel\Policies`: `Policies\ProductModelPolicy`
  - `RefactorCircus\Keystone\Domains\Product\Policies`: `Policies\ProductPolicy`
  - `RefactorCircus\Keystone\Domains\Search\Data`: `Search\Complete`, `Search\Filter`, `Search\ProductQuery`, `Search\SearchResults`
  - `RefactorCircus\Keystone\Domains\Search\Services`: `Search\Engines\DatabaseEngine`, `Search\Engines\ElasticsearchEngine`, `Search\Engines\ScoutEngine`, `Search\ProductDocument`, `Search\ProductIndex`
  - `RefactorCircus\Keystone\Domains\Search\Support`: `Search\Engines\SqlFragment`, `Search\ProductPage`
  - `RefactorCircus\Keystone\Domains\Search\Models`: `Search\Scout\SearchableProduct` (as `SearchableProductModel`)
  - `RefactorCircus\Keystone\Domains\Attribute\Services`: `Values\UniqueValues`, `Values\ValueValidator`, `Values\Values`
  - `RefactorCircus\Keystone\Domains\Attribute\Data`: `Values\ValueFilter`
  - `RefactorCircus\Keystone\Domains\Workflow\Services`: `Workflow\CompletenessCalculator`, `Workflow\Versions`

### Fixed

- Every class the Atrium screens use exists in Atrium's stylesheet, so all of them take effect.

### Added

- With refactor-circus/keen, the product, product model, family, attribute, category, owner, asset and channel pages show the record's audit history, and the product list shows Keystone's (`<x-atrium::audit-trail>`). Product versions and revert stay.
- Audit labels (`RefactorCircus\Foundation\Audit\AuditHooks`): records with localized labels are named by the current locale's label (else the code), associations by their type and both ends, and versions by what they version and their number.
- `GET /keystone/history` (`keystone.history.index`) and the `list-keystone-history-tool` MCP tool list Keystone's audit history, newest first, when [refactor-circus/keen](https://github.com/jayjfletcher/Keen) is installed. Without it, both answer that no audit log is installed (`404` over HTTP).
- Catalog foundation: Actions shared by the HTTP API, MCP server and Atrium dashboard, with starting/finished action events and per-hook model events.
- Attribute groups, typed attributes (text, textarea, number, decimal, boolean, date, select, multiselect, price, metric) and attribute options.
- JSON API under `/keystone`, MCP tools behind ToolSearch, optional Cortex registration, and Atrium catalog pages, settings and search.
- Config-driven authorization and swappable policies.
- Families (attribute sets): attribute membership with required flags and order, and a text label attribute. Deleting an attribute that labels a family is refused.
- Products with typed, localizable and scopable values stored as JSON, validated per attribute type and settings; unique attributes enforced by a database index.
- Family variants (one or two levels of axes), product models and variant products with value inheritance and distinct axis combinations.
- Pluggable product search: database (default), Elasticsearch via `refactor-circus/stretch`, or any Laravel Scout engine; queued index syncing and `keystone:search:reindex`.
- Deleting an attribute or option purges its stored values in a queued job.
- Atrium dashboard widgets: products by status, completeness per channel and locale, review queue and recent changes.
- Import, export and feeds with `refactor-circus/impex` (optional): `keystone:import-products` (CSV/JSONL, create/update/upsert, per-row failures), `keystone:upsert-products` for ERP pushes through inbound channels, resumable `keystone:export-products` of any search (working or published versions), per-channel `keystone:feed:{name}` flows with ledgered HTTP delivery; start endpoints, MCP tools and an Atrium page.
- Completeness per channel and locale (with per-channel requirements), stored and searchable; workflow statuses and transitions with snapshot publishing; versioning with authors, changes, history and revert.
- Associations: runtime association types (plain, two-way with automatic mirroring, quantified for bundles and kits); associations on products and product models, inherited by variants; cleanup on delete.
- Channels and locales: locales and channels (locales, currencies, category tree); localizable and scopable values enforced against them, including a channel's own locales and currencies; `scope` and `locales` on product reads; purging a deleted locale's or channel's values; dashboard editing of every value slot.
- Media: assets on any filesystem disk (streamed; S3/Vapor-ready), added by upload, disk path or URL with size and type limits; linked to products, product models and owners under roles, inherited by variants; temporary URLs for private disks.
- Taxonomy: independent category trees with moves, products and product models filed in categories (inherited by variants), product search by category branch.
- Dynamic hierarchical ownership: runtime owner types with parent, root and product rules; owner chains of any depth with moves; products and root product models assigned to owners; product search by owner subtree.

### Changed

- Atrium screens follow Atrium's screen conventions: every action is an icon button (tabs and back links too), product, enabled, live and import/export statuses are status dots coloured by `RefactorCircus\Keystone\Atrium\Badges` (`info` only for in review and pending or waiting runs), and every navigation item has an icon. `ProductStatus::badge()` now answers from `Badges`: in review is `info` and draft `primary`.
- Atrium screens now authorize every page and action against `keystone.policies`, asked exactly as the JSON API and MCP tools ask, and show each navigation item, button, form and card only when its action would be allowed (`ScreenAccess`, `@keystoneCan`). Widgets and search are gated the same way. With `keystone.authorization` on (the default) a guest sees nothing; turn it off for an open operator dashboard.
- Requires refactor-circus/atrium with icon buttons and status dots (f5eb488 or later).

### Added

- `KeystoneSupportFeature` (with refactor-circus/pennantplus): switches Keystone in Atrium on and off as a whole through Pennant; `keystone.atrium.features` names the features checked, skipping classes that are not installed.

### Removed

- Skeleton placeholder config, route, migration, view, command, translation and public assets tag.


## [v0.1.0](https://github.com/jayi/keystone/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
