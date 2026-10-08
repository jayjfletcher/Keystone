# Release Notes

## [Unreleased](https://github.com/jayi/keystone/compare/v0.1.0...1.x)

### Added

- **Product webhooks** (`keystone.impex.webhooks`, off by default): published products as an Impex stream (`keystone.products`) that vendors subscribe to by category, owner, family, model or SKU, and by topic — `content`, `pricing`, `assets`, `resources`, `catalog` — in `thin`, `slice` or `full` format, narrowed to a channel and locales. Changes are pushed signed and batched, or pulled from a feed; unpublished, deleted and out-of-scope products reach vendors as removed; full exports start a new vendor off. Product changes are reported to Impex from `ProductIndex::queue()` (which now fires `JayI\Keystone\Domains\Search\Events\ProductsQueuedForSync`), deletions and asset links.
- Feeds can deliver through any Impex outbound channel with `deliver_through`, taking the channel's transport, signing and headers.
- `updated_since` on `GET /keystone/products` (and `ListProductsAction`), on every search engine.
- `GET /keystone/products/{identifier}` sends an `ETag` and answers `304` to a matching `If-None-Match`.
- The package's section in Atrium's sidebar rail has its own icon (`cube`) and a fixed place in the rail.
- An **Audit log** link in the package's sidebar group, opening its own audit log in Atrium (`/atrium/history/keystone`), shown while an audit log (jayi/keen) is installed and to those who may read the package's history.

### Breaking

- Keystone ships no stylesheet: `resources/css/atrium.css` and its registration with Atrium's style hook are removed. The screens use only Atrium's components and safelisted utilities (bare form controls in table cells, `description-list`, `progress`, `flash`), and `ui/partials/status.blade.php` is replaced by `<x-atrium::flash />`, which also shows the first validation error. Requires a jayi/atrium with those components. Published views that include `keystone::ui.partials.status` must switch to `<x-atrium::flash />`.
- `JayI\Keystone\Atrium\Http\Controllers\Concerns\AuthorizesScreens` is removed; the screen controllers use Atrium's `JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens`. `ScreenAccess::allows()` now delegates to `JayI\Atrium\Support\ScreenAccess`, and `KeystonePlugin` uses the base plugin's `featuresFromConfig()`, `key()` and `label()`.

- Keystone now stands on [jayi/foundation](https://github.com/jayjfletcher/Foundation), the runtime the jayi packages share, and its local copies are removed in favour of Foundation's classes:
  - `JayI\Keystone\Contracts\{ActionStartingEvent,ActionFinishedEvent,ModelLifecycleEvent}` → `JayI\Foundation\Contracts\*`. Listening to a Foundation contract now hears every package of the suite.
  - `JayI\Keystone\Support\Models\Concerns\DispatchesModelEvents` → `JayI\Foundation\Models\Concerns\DispatchesModelEvents`. A subclass of a Keystone model now fires the Keystone model's events.
  - `JayI\Keystone\Support\ServiceProvider` → `JayI\Foundation\Support\ServiceProvider`; `JayI\Keystone\Support\Authorizer` → `JayI\Foundation\Auth\Authorizer::for($package)`.
  - `JayI\Keystone\Http\Request` → `JayI\Foundation\Http\Requests\Request`; `JayI\Keystone\Mcp\Request` → `JayI\Foundation\Mcp\Requests\Request`, whose calls run inside the `mcp` surface; `JayI\Keystone\Mcp\Tool` → `JayI\Foundation\Mcp\Tool`.
  - `JayI\Keystone\Cortex\CortexIntegration` → `JayI\Foundation\Cortex\CortexIntegration::for($package)`, which also marks agent tool calls with the `cortex` surface.
  - `KeystoneServiceProvider` extends `PackageServiceProvider`, and `KeystoneServer` extends `JayI\Foundation\Mcp\Server`. `KeystoneException` is now an abstract `JayI\Foundation\Exceptions\PackageException` that still answers 409, and the bundled policy base extends Foundation's `Policy`. Config keys, route names and tool names are unchanged.
- The source is reorganised into domain modules under `src/Domains/{Domain}` (`JayI\Keystone\Domains\{Domain}`), each with its own service provider registered by `JayI\Keystone\Domains\DomainServiceProvider`: **Product**, **ProductModel**, **Attribute** (attributes, groups, options, values), **Family** (families and family variants), **Owner** (owners and owner types), **Category**, **Channel** (channels and locales), **Association**, **Asset**, **Workflow** (versions, completeness, transitions, revert), **Search** and **Transfer** (starting Impex imports and exports). Cross-domain code moved to `JayI\Keystone\Support`, and the Atrium screens to `JayI\Keystone\Atrium`. `KeystoneServiceProvider` keeps its name. Config keys, route names, MCP tool names, views, translations, publish tags and `@keystoneCan` are unchanged. There are no aliases for the old class names.
- The Eloquent models are renamed to end in `Model`. Their old class names are kept as morph aliases, so any `*_type` value written under them still resolves; products, product models and owners keep writing their `keystone_product`, `keystone_product_model` and `keystone_owner` aliases, and the other models keep writing their old class names. Point published `keystone.policies` keys at the new names:
  - `JayI\Keystone\Models\Asset` → `JayI\Keystone\Domains\Asset\Models\AssetModel`
  - `JayI\Keystone\Models\Association` → `JayI\Keystone\Domains\Association\Models\AssociationModel`
  - `JayI\Keystone\Models\AssociationType` → `JayI\Keystone\Domains\Association\Models\AssociationTypeModel`
  - `JayI\Keystone\Models\Attribute` → `JayI\Keystone\Domains\Attribute\Models\AttributeModel`
  - `JayI\Keystone\Models\AttributeGroup` → `JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel`
  - `JayI\Keystone\Models\AttributeOption` → `JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel`
  - `JayI\Keystone\Models\Category` → `JayI\Keystone\Domains\Category\Models\CategoryModel`
  - `JayI\Keystone\Models\Channel` → `JayI\Keystone\Domains\Channel\Models\ChannelModel`
  - `JayI\Keystone\Models\Completeness` → `JayI\Keystone\Domains\Workflow\Models\CompletenessModel`
  - `JayI\Keystone\Models\Family` → `JayI\Keystone\Domains\Family\Models\FamilyModel`
  - `JayI\Keystone\Models\FamilyVariant` → `JayI\Keystone\Domains\Family\Models\FamilyVariantModel`
  - `JayI\Keystone\Models\Locale` → `JayI\Keystone\Domains\Channel\Models\LocaleModel`
  - `JayI\Keystone\Models\Owner` → `JayI\Keystone\Domains\Owner\Models\OwnerModel`
  - `JayI\Keystone\Models\OwnerType` → `JayI\Keystone\Domains\Owner\Models\OwnerTypeModel`
  - `JayI\Keystone\Models\Product` → `JayI\Keystone\Domains\Product\Models\ProductModel`
  - `JayI\Keystone\Models\ProductModel` → `JayI\Keystone\Domains\ProductModel\Models\ProductModelModel`
  - `JayI\Keystone\Models\Version` → `JayI\Keystone\Domains\Workflow\Models\VersionModel`
- Relations now name their foreign and pivot keys, which Eloquent used to guess from the old class names; factories' `for()` needs the relationship name where it guessed it from a class (`AttributeOptionModel::factory()->for($attribute, 'attribute')`).
- `KeystoneSupportFeature` moved to `JayI\Keystone\Atrium\Features` and keeps its stored Pennant name, `JayI\Keystone\Features\KeystoneSupportFeature`, through Pennant's `#[Name]` attribute.
- `routes/keystone.php` is replaced by a `routes.php` per domain, loaded inside the same `keystone.` group; the routes, their names and middleware are unchanged.
- Model events (formerly `Events\Model`) and action events (formerly `Events\Action`) now live in their domain's `Events` namespace, keeping their class names. Model events are found from the model's domain and its name less the `Model` suffix.
- Unchanged, because Impex runs and queued payloads store their class names: `JayI\Keystone\Impex\*` (flows, sources, actions) and `JayI\Keystone\Jobs\*`.
- Actions, HTTP requests, MCP tools and MCP requests keep their class names and move to their domain:
  - `JayI\Keystone\Domains\Asset\Actions`: every Action for the domain (7 classes)
  - `JayI\Keystone\Domains\Association\Actions`: every Action for the domain (5 classes)
  - `JayI\Keystone\Domains\Attribute\Actions`: every Action for the domain (14 classes)
  - `JayI\Keystone\Domains\Category\Actions`: every Action for the domain (5 classes)
  - `JayI\Keystone\Domains\Channel\Actions`: every Action for the domain (10 classes)
  - `JayI\Keystone\Domains\Family\Actions`: every Action for the domain (10 classes)
  - `JayI\Keystone\Domains\Owner\Actions`: every Action for the domain (10 classes)
  - `JayI\Keystone\Domains\Product\Actions`: every Action for the domain (5 classes)
  - `JayI\Keystone\Domains\ProductModel\Actions`: every Action for the domain (5 classes)
  - `JayI\Keystone\Domains\Workflow\Actions`: every Action for the domain (4 classes)
  - `JayI\Keystone\Domains\Transfer\Actions`: every Action for the domain (2 classes)
  - `JayI\Keystone\Domains\Asset\Http\Requests`: every HTTP request for the domain (8 classes)
  - `JayI\Keystone\Domains\Association\Http\Requests`: every HTTP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Attribute\Http\Requests`: every HTTP request for the domain (17 classes)
  - `JayI\Keystone\Domains\Category\Http\Requests`: every HTTP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Channel\Http\Requests`: every HTTP request for the domain (12 classes)
  - `JayI\Keystone\Domains\Family\Http\Requests`: every HTTP request for the domain (12 classes)
  - `JayI\Keystone\Domains\Owner\Http\Requests`: every HTTP request for the domain (12 classes)
  - `JayI\Keystone\Domains\ProductModel\Http\Requests`: every HTTP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Product\Http\Requests`: every HTTP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Workflow\Http\Requests`: every HTTP request for the domain (4 classes)
  - `JayI\Keystone\Domains\Transfer\Http\Requests`: every HTTP request for the domain (2 classes)
  - `JayI\Keystone\Domains\Asset\Mcp\Requests`: every MCP request for the domain (8 classes)
  - `JayI\Keystone\Domains\Association\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Attribute\Mcp\Requests`: every MCP request for the domain (17 classes)
  - `JayI\Keystone\Domains\Category\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Channel\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `JayI\Keystone\Domains\Family\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `JayI\Keystone\Domains\Owner\Mcp\Requests`: every MCP request for the domain (12 classes)
  - `JayI\Keystone\Domains\Product\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `JayI\Keystone\Domains\ProductModel\Mcp\Requests`: every MCP request for the domain (6 classes)
  - `JayI\Keystone\Domains\Workflow\Mcp\Requests`: every MCP request for the domain (4 classes)
  - `JayI\Keystone\Domains\Transfer\Mcp\Requests`: every MCP request for the domain (2 classes)
  - `JayI\Keystone\Domains\Asset\Mcp\Tools`: every MCP tool for the domain (7 classes)
  - `JayI\Keystone\Domains\Association\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `JayI\Keystone\Domains\Attribute\Mcp\Tools`: every MCP tool for the domain (14 classes)
  - `JayI\Keystone\Domains\Category\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `JayI\Keystone\Domains\Channel\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `JayI\Keystone\Domains\Family\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `JayI\Keystone\Domains\Owner\Mcp\Tools`: every MCP tool for the domain (10 classes)
  - `JayI\Keystone\Domains\ProductModel\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `JayI\Keystone\Domains\Product\Mcp\Tools`: every MCP tool for the domain (5 classes)
  - `JayI\Keystone\Domains\Workflow\Mcp\Tools`: every MCP tool for the domain (4 classes)
  - `JayI\Keystone\Domains\Transfer\Mcp\Tools`: every MCP tool for the domain (2 classes)
  - `JayI\Keystone\Domains\Asset\Events`: every model and action event for the domain (24 classes)
  - `JayI\Keystone\Domains\Association\Events`: every model and action event for the domain (30 classes)
  - `JayI\Keystone\Domains\Attribute\Events`: every model and action event for the domain (58 classes)
  - `JayI\Keystone\Domains\Category\Events`: every model and action event for the domain (20 classes)
  - `JayI\Keystone\Domains\Channel\Events`: every model and action event for the domain (40 classes)
  - `JayI\Keystone\Domains\Transfer\Events`: every model and action event for the domain (4 classes)
  - `JayI\Keystone\Domains\Family\Events`: every model and action event for the domain (40 classes)
  - `JayI\Keystone\Domains\Owner\Events`: every model and action event for the domain (40 classes)
  - `JayI\Keystone\Domains\Product\Events`: every model and action event for the domain (20 classes)
  - `JayI\Keystone\Domains\ProductModel\Events`: every model and action event for the domain (20 classes)
  - `JayI\Keystone\Domains\Workflow\Events`: every model and action event for the domain (28 classes)
- Every other moved class, by its new namespace (old names relative to `JayI\Keystone`):
  - `JayI\Keystone\Support`: `Access\Authorizer`
  - `JayI\Keystone\Domains\Category\Concerns`: `Actions\Concerns\AssignsCategories`
  - `JayI\Keystone\Domains\Owner\Concerns`: `Actions\Concerns\AssignsOwners`, `Actions\Concerns\PlacesOwners`, `Actions\Concerns\WritesOwnerTypes`
  - `JayI\Keystone\Support\Concerns`: `Actions\Concerns\MovesInTree`
  - `JayI\Keystone\Domains\Asset\Concerns`: `Actions\Concerns\StoresAssetFiles`, `Models\Concerns\HasAssets`
  - `JayI\Keystone\Domains\Channel\Concerns`: `Actions\Concerns\WritesChannels`
  - `JayI\Keystone\Domains\Family\Concerns`: `Actions\Concerns\WritesFamilyAttributes`, `Actions\Concerns\WritesFamilyVariantLevels`
  - `JayI\Keystone\Domains\Product\Concerns`: `Actions\Concerns\WritesProducts`
  - `JayI\Keystone\Domains\Attribute\Concerns`: `Actions\Concerns\WritesValues`, `Models\Concerns\HasValues`
  - `JayI\Keystone\Domains\Association\Services`: `Associations\Associations`
  - `JayI\Keystone\Domains\Search\Console\Commands`: `Console\Commands\ReindexProductsCommand`
  - `JayI\Keystone\Domains\Search\Contracts`: `Contracts\SearchEngine`
  - `JayI\Keystone\Domains\Attribute\Enums`: `Enums\AttributeType`
  - `JayI\Keystone\Domains\Product\Enums`: `Enums\ProductStatus`
  - `JayI\Keystone\Domains\Workflow\Enums`: `Enums\Transition`
  - `JayI\Keystone\Domains\Attribute\Exceptions`: `Exceptions\AttributeGroupNotEmptyException`, `Exceptions\AttributeHasNoOptionsException`, `Exceptions\AttributeLabelsFamiliesException`
  - `JayI\Keystone\Domains\Transfer\Exceptions`: `Exceptions\ImpexMissingException`
  - `JayI\Keystone\Domains\Workflow\Exceptions`: `Exceptions\InvalidTransitionException`
  - `JayI\Keystone\Domains\Search\Exceptions`: `Exceptions\UnsupportedSearchException`
  - `JayI\Keystone\Atrium\Features`: `Features\KeystoneSupportFeature`
  - `JayI\Keystone\Domains\Asset\Http\Controllers`: `Http\Controllers\AssetController`
  - `JayI\Keystone\Domains\Association\Http\Controllers`: `Http\Controllers\AssociationTypeController`
  - `JayI\Keystone\Domains\Attribute\Http\Controllers`: `Http\Controllers\AttributeController`, `Http\Controllers\AttributeGroupController`, `Http\Controllers\AttributeOptionController`
  - `JayI\Keystone\Domains\Category\Http\Controllers`: `Http\Controllers\CategoryController`
  - `JayI\Keystone\Domains\Channel\Http\Controllers`: `Http\Controllers\ChannelController`, `Http\Controllers\LocaleController`
  - `JayI\Keystone\Domains\Family\Http\Controllers`: `Http\Controllers\FamilyController`, `Http\Controllers\FamilyVariantController`
  - `JayI\Keystone\Domains\Transfer\Http\Controllers`: `Http\Controllers\ImpexController`
  - `JayI\Keystone\Domains\Owner\Http\Controllers`: `Http\Controllers\OwnerController`, `Http\Controllers\OwnerTypeController`
  - `JayI\Keystone\Domains\Product\Http\Controllers`: `Http\Controllers\ProductController`
  - `JayI\Keystone\Domains\ProductModel\Http\Controllers`: `Http\Controllers\ProductModelController`
  - `JayI\Keystone\Domains\Workflow\Http\Controllers`: `Http\Controllers\ProductWorkflowController`
  - `JayI\Keystone\Domains\Asset\Resources`: `Http\Resources\AssetResource`, `Http\Resources\LinkedAssets`
  - `JayI\Keystone\Domains\Association\Resources`: `Http\Resources\AssociationTypeResource`
  - `JayI\Keystone\Domains\Attribute\Resources`: `Http\Resources\AttributeGroupResource`, `Http\Resources\AttributeOptionResource`, `Http\Resources\AttributeResource`
  - `JayI\Keystone\Domains\Category\Resources`: `Http\Resources\CategoryResource`
  - `JayI\Keystone\Domains\Channel\Resources`: `Http\Resources\ChannelResource`, `Http\Resources\LocaleResource`
  - `JayI\Keystone\Domains\Family\Resources`: `Http\Resources\FamilyAttributeResource`, `Http\Resources\FamilyResource`, `Http\Resources\FamilyVariantResource`
  - `JayI\Keystone\Domains\Owner\Resources`: `Http\Resources\OwnerResource`, `Http\Resources\OwnerTypeResource`
  - `JayI\Keystone\Domains\ProductModel\Resources`: `Http\Resources\ProductModelResource`
  - `JayI\Keystone\Domains\Product\Resources`: `Http\Resources\ProductResource`
  - `JayI\Keystone\Domains\Workflow\Resources`: `Http\Resources\VersionResource`, `Http\Resources\VersionSummaryResource`
  - `JayI\Keystone\Atrium\Http\Controllers`: `Http\Ui\AssetUiController`, `Http\Ui\AssociationUiController`, `Http\Ui\AttributeGroupUiController`, `Http\Ui\AttributeUiController`, `Http\Ui\CategoryUiController`, `Http\Ui\ChannelUiController`, `Http\Ui\FamilyUiController`, `Http\Ui\FamilyVariantUiController`, `Http\Ui\OwnerTypeUiController`, `Http\Ui\OwnerUiController`, `Http\Ui\ProductModelUiController`, `Http\Ui\ProductUiController`, `Http\Ui\TransferUiController`
  - `JayI\Keystone\Atrium\Support`: `Http\Ui\CategoryCodes`, `Http\Ui\EditingSlot`, `Http\Ui\Labels`, `Http\Ui\ValueForm`
  - `JayI\Keystone\Atrium\Http\Controllers\Concerns`: `Http\Ui\Concerns\AuthorizesScreens`
  - `JayI\Keystone\Atrium`: `Http\Ui\ScreenAccess`
  - `JayI\Keystone\Domains\Asset\Services`: `Media\AssetLinks`, `Media\AssetStorage`
  - `JayI\Keystone\Domains\Asset\Data`: `Media\StoredFile`
  - `JayI\Keystone\Support\Models\Concerns`: `Models\Concerns\DispatchesModelEvents`, `Models\Concerns\HasLabels`, `Models\Concerns\HasPath`
  - `JayI\Keystone\Domains\Association\Concerns`: `Models\Concerns\HasAssociations`
  - `JayI\Keystone\Domains\Asset\Policies`: `Policies\AssetPolicy`
  - `JayI\Keystone\Domains\Association\Policies`: `Policies\AssociationTypePolicy`
  - `JayI\Keystone\Domains\Attribute\Policies`: `Policies\AttributeGroupPolicy`, `Policies\AttributeOptionPolicy`, `Policies\AttributePolicy`
  - `JayI\Keystone\Domains\Category\Policies`: `Policies\CategoryPolicy`
  - `JayI\Keystone\Domains\Channel\Policies`: `Policies\ChannelPolicy`, `Policies\LocalePolicy`
  - `JayI\Keystone\Domains\Family\Policies`: `Policies\FamilyPolicy`, `Policies\FamilyVariantPolicy`
  - `JayI\Keystone\Domains\Owner\Policies`: `Policies\OwnerPolicy`, `Policies\OwnerTypePolicy`
  - `JayI\Keystone\Support\Policies`: `Policies\Policy`
  - `JayI\Keystone\Domains\ProductModel\Policies`: `Policies\ProductModelPolicy`
  - `JayI\Keystone\Domains\Product\Policies`: `Policies\ProductPolicy`
  - `JayI\Keystone\Domains\Search\Data`: `Search\Complete`, `Search\Filter`, `Search\ProductQuery`, `Search\SearchResults`
  - `JayI\Keystone\Domains\Search\Services`: `Search\Engines\DatabaseEngine`, `Search\Engines\ElasticsearchEngine`, `Search\Engines\ScoutEngine`, `Search\ProductDocument`, `Search\ProductIndex`
  - `JayI\Keystone\Domains\Search\Support`: `Search\Engines\SqlFragment`, `Search\ProductPage`
  - `JayI\Keystone\Domains\Search\Models`: `Search\Scout\SearchableProduct` (as `SearchableProductModel`)
  - `JayI\Keystone\Domains\Attribute\Services`: `Values\UniqueValues`, `Values\ValueValidator`, `Values\Values`
  - `JayI\Keystone\Domains\Attribute\Data`: `Values\ValueFilter`
  - `JayI\Keystone\Domains\Workflow\Services`: `Workflow\CompletenessCalculator`, `Workflow\Versions`

### Fixed

- Every class the Atrium screens use exists in Atrium's stylesheet, so all of them take effect.

### Added

- With jayi/keen, the product, product model, family, attribute, category, owner, asset and channel pages show the record's audit history, and the product list shows Keystone's (`<x-atrium::audit-trail>`). Product versions and revert stay.
- Audit labels (`JayI\Foundation\Audit\AuditHooks`): records with localized labels are named by the current locale's label (else the code), associations by their type and both ends, and versions by what they version and their number.
- `GET /keystone/history` (`keystone.history.index`) and the `list-keystone-history-tool` MCP tool list Keystone's audit history, newest first, when [jayi/keen](https://github.com/jayjfletcher/Keen) is installed. Without it, both answer that no audit log is installed (`404` over HTTP).
- Catalog foundation: Actions shared by the HTTP API, MCP server and Atrium dashboard, with starting/finished action events and per-hook model events.
- Attribute groups, typed attributes (text, textarea, number, decimal, boolean, date, select, multiselect, price, metric) and attribute options.
- JSON API under `/keystone`, MCP tools behind ToolSearch, optional Cortex registration, and Atrium catalog pages, settings and search.
- Config-driven authorization and swappable policies.
- Families (attribute sets): attribute membership with required flags and order, and a text label attribute. Deleting an attribute that labels a family is refused.
- Products with typed, localizable and scopable values stored as JSON, validated per attribute type and settings; unique attributes enforced by a database index.
- Family variants (one or two levels of axes), product models and variant products with value inheritance and distinct axis combinations.
- Pluggable product search: database (default), Elasticsearch via `jayi/stretch`, or any Laravel Scout engine; queued index syncing and `keystone:search:reindex`.
- Deleting an attribute or option purges its stored values in a queued job.
- Atrium dashboard widgets: products by status, completeness per channel and locale, review queue and recent changes.
- Import, export and feeds with `jayi/impex` (optional): `keystone:import-products` (CSV/JSONL, create/update/upsert, per-row failures), `keystone:upsert-products` for ERP pushes through inbound channels, resumable `keystone:export-products` of any search (working or published versions), per-channel `keystone:feed:{name}` flows with ledgered HTTP delivery; start endpoints, MCP tools and an Atrium page.
- Completeness per channel and locale (with per-channel requirements), stored and searchable; workflow statuses and transitions with snapshot publishing; versioning with authors, changes, history and revert.
- Associations: runtime association types (plain, two-way with automatic mirroring, quantified for bundles and kits); associations on products and product models, inherited by variants; cleanup on delete.
- Channels and locales: locales and channels (locales, currencies, category tree); localizable and scopable values enforced against them, including a channel's own locales and currencies; `scope` and `locales` on product reads; purging a deleted locale's or channel's values; dashboard editing of every value slot.
- Media: assets on any filesystem disk (streamed; S3/Vapor-ready), added by upload, disk path or URL with size and type limits; linked to products, product models and owners under roles, inherited by variants; temporary URLs for private disks.
- Taxonomy: independent category trees with moves, products and product models filed in categories (inherited by variants), product search by category branch.
- Dynamic hierarchical ownership: runtime owner types with parent, root and product rules; owner chains of any depth with moves; products and root product models assigned to owners; product search by owner subtree.

### Changed

- Atrium screens follow Atrium's screen conventions: every action is an icon button (tabs and back links too), product, enabled, live and import/export statuses are status dots coloured by `JayI\Keystone\Atrium\Badges` (`info` only for in review and pending or waiting runs), and every navigation item has an icon. `ProductStatus::badge()` now answers from `Badges`: in review is `info` and draft `primary`.
- Atrium screens now authorize every page and action against `keystone.policies`, asked exactly as the JSON API and MCP tools ask, and show each navigation item, button, form and card only when its action would be allowed (`ScreenAccess`, `@keystoneCan`). Widgets and search are gated the same way. With `keystone.authorization` on (the default) a guest sees nothing; turn it off for an open operator dashboard.
- Requires jayi/atrium with icon buttons and status dots (f5eb488 or later).

### Added

- `KeystoneSupportFeature` (with jayi/pennantplus): switches Keystone in Atrium on and off as a whole through Pennant; `keystone.atrium.features` names the features checked, skipping classes that are not installed.

### Removed

- Skeleton placeholder config, route, migration, view, command, translation and public assets tag.


## [v0.1.0](https://github.com/jayi/keystone/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
