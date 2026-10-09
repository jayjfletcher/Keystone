---
name: showroom-development
description: >
  Install and use the Showroom PIM package (refactor-circus/showroom) in Laravel applications: define attribute groups, typed
  attributes, options, families, family variants, product models, products, owners, categories, assets, locales, channels and associations through Actions; run the review and publishing workflow and read version history, import, export and syndicate products through Impex flows, the JSON API under /showroom, or MCP tools; configure authorization,
  policies, MCP transports, Cortex and the Atrium dashboard (pages, widgets, search).
license: MIT
metadata:
  author: Jay Fletcher
---

# Showroom

Use this skill when a Laravel application needs product information management (PIM) with Showroom: managing the attributes products are described with, over PHP, HTTP, MCP or the Atrium dashboard.

## Primary Goal

- apply `refactor-circus/showroom`'s public API — Actions, HTTP routes, MCP tools, config and events — in the smallest correct way

## Workflow

### 1. Install and migrate

```bash
composer require refactor-circus/showroom
php artisan vendor:publish --tag="showroom-migrations"
php artisan migrate
php artisan vendor:publish --tag="showroom-config"   # when changing config
```

### 2. Secure the surfaces

- `showroom.routes.middleware` defaults to `['api']`: add authentication (for example `auth:sanctum`).
- `showroom.authorization` is `true` by default: API and MCP calls need an authenticated user and pass the policies in `showroom.policies`. The bundled policies allow any authenticated user; map a model to an app policy to restrict it.
- MCP transports ship disabled (`showroom.mcp.web.enabled`, `showroom.mcp.local.enabled`); add auth middleware before enabling the web transport.
- The Atrium screens ask the same policies the same way (`RefactorCircus\Showroom\Atrium\ScreenAccess`): navigation items, pages and every button, form and card appear only when the action is allowed, and are refused (403) otherwise. In published or custom views use `@showroomCan('update', $product) ... @endshowroomCan`. With authorization on, a guest sees no Showroom screens.
- With refactor-circus/pennantplus, `Feature::for(null)->deactivate(RefactorCircus\Showroom\Atrium\Features\ShowroomSupportFeature::class)` hides Showroom in Atrium (pages 404); only its global value counts. `showroom.atrium.features` names the features checked; classes that are not installed are skipped.
- Showroom ships no stylesheet: screens use `x-atrium::*` components and Atrium's safelisted utilities only (checked with `RefactorCircus\Atrium\Testing\AtriumStyles`). In published views, keep to the same: bare form controls in table cells, `<x-atrium::flash />` for status and errors, `x-atrium::description-list` for details.
- With refactor-circus/keen, show pages render the record's history (`<x-atrium::audit-trail source="showroom" :subject="$product" />`) and the product list Showroom's; product versions and revert stay.
- Screens follow Atrium's conventions: actions are `<x-atrium::icon-button icon="…" :label="…">`, statuses `<x-atrium::status-dot>` coloured by `RefactorCircus\Showroom\Atrium\Badges` (`info` only for in review / pending runs).

### 3. Manage the catalog through Actions

Call Actions from each domain's `Actions` namespace (`RefactorCircus\Showroom\Domains\{Domain}\Actions`, such as `RefactorCircus\Showroom\Domains\Attribute\Actions\CreateAttributeAction`) with `app(...)->execute(...)`. Each has a static `rules()` that is its input contract; validate user input against it.

- Groups: `List/Show/Create/Update/DeleteAttributeGroupAction`
- Attributes: `List/Show/Create/Update/DeleteAttributeAction`
- Options: `ListAttributeOptionsAction`, `Create/Update/DeleteAttributeOptionAction`
- Families: `ListFamiliesAction`, `ShowFamilyAction`, `Create/Update/DeleteFamilyAction`
- Variants: `List/Show/Create/Update/DeleteFamilyVariantAction`, `List/Show/Create/Update/DeleteProductModelAction`
- Products: `ListProductsAction` (search), `Show/Create/Update/DeleteProductAction`
- Ownership: `List/Show/Create/Update/DeleteOwnerTypeAction`, `List/Show/Create/Update/DeleteOwnerAction`
- Taxonomy: `List/Show/Create/Update/DeleteCategoryAction`
- Media: `List/Show/Create/Update/DeleteAssetAction`, `AttachAssetAction`, `DetachAssetAction`
- Channels: `List/Show/Create/Update/DeleteLocaleAction`, `List/Show/Create/Update/DeleteChannelAction`
- Associations: `List/Show/Create/Update/DeleteAssociationTypeAction`; associations go through the product and product model Actions
- Workflow: `TransitionProductAction`, `ListProductVersionsAction`, `ShowProductVersionAction`, `RevertProductAction`
- Import/export (needs `refactor-circus/impex`): `StartImportAction`, `StartExportAction` — both return the Impex `Run`

### 4. Or use the HTTP API / MCP tools

- HTTP: `/showroom/attribute-groups`, `/showroom/attributes`, `/showroom/attributes/{code}/options`, `/showroom/families`, `/showroom/family-variants`, `/showroom/product-models`, `/showroom/products`, `/showroom/owner-types`, `/showroom/owners`, `/showroom/categories`, `/showroom/assets`, `/showroom/locales`, `/showroom/channels`, `/showroom/association-types`, `POST /showroom/imports`, `POST /showroom/exports` — records addressed by code, cursor pagination (`cursor`, `per_page`).
- MCP: tools such as `create-attribute-tool` behind `search_tools` / `execute_tools`; catalog in `RefactorCircus\Showroom\Mcp\ShowroomServer::TOOLS`.
- History (needs `refactor-circus/keen`): `GET /showroom/history` (route `showroom.history.index`) and `list-showroom-history-tool`; both answer "not installed" without it.

### 5. Choose a search engine

- `showroom.search.engine`: `null` (default: `scout` when `laravel/scout` is installed, `database` otherwise), `database`, `scout`, or a class implementing `RefactorCircus\Showroom\Domains\Search\Contracts\SearchEngine`.
- Run a queue worker: index syncs (`SyncProductIndex`) and value purges are queued. Rebuild with `php artisan showroom:search:reindex`.

### 6. React to changes

Listen to action events (`RefactorCircus\Showroom\Domains\Attribute\Events\AttributeCreatedActionEvent`, or the `RefactorCircus\Foundation\Contracts\ActionFinishedEvent` family) or model events (`RefactorCircus\Foundation\Contracts\ModelLifecycleEvent`).

To follow every product whose presentation may have changed (own edits, transitions, and changes inherited from models, families, categories, owners or channels), listen to `RefactorCircus\Showroom\Domains\Search\Events\ProductsQueuedForSync` (`$event->ids`, product ids). It fires inside the write's transaction, before the index sync is queued; product webhooks are built on it.

## Rules, References, and Templates

- Codes (`^[a-z][a-z0-9_]*$`, options may start with a digit) and attribute types never change after creation; updates that send them fail validation.
- Types: `text`, `textarea`, `number`, `decimal`, `boolean`, `date`, `select`, `multiselect`, `price`, `metric`. Settings are type-specific; `metric` requires `metric_family` and `default_unit`.
- Only `select` and `multiselect` take options. `is_unique` only for text, number, decimal, date.
- A group holding attributes cannot be deleted (`AttributeGroupNotEmptyException`, HTTP 409).
- A family's `attributes` is its whole membership (`[['attribute' => 'size', 'is_required' => true]]`), replaced on update; `label_attribute` must be a text attribute in the family, and such an attribute cannot be deleted.
- Values are lists of slots per attribute: `['name' => [['locale' => null, 'scope' => null, 'data' => 'Tee']]]`. Updates patch only the slots sent; `data => null` clears one. Decimal and amounts come back as strings.
- Product `identifier`s never change. Unique attributes are not localizable or scopable.
- Variants: a family variant has 1–2 levels of axes (select, boolean, metric); root models hold common values, variant products (`parent` = last-level model) hold last-level values and must fill every axis with a distinct combination.
- Owners: owner types set `parent_types` (null = any), `can_be_root`, `owns_products`; owner codes are globally unique; `parent` on update moves a subtree. Assign `owner` to simple products and root product models only; filter products with `owner` to include the whole subtree.
- Categories: a root category is a tree; `categories` on products/models is the full list of codes (replaced on update); variants inherit models' categories; filter products with `category` to include the whole branch; only leaf categories can be deleted.
- Assets: set `showroom.media.disk` (S3 on Vapor); create with exactly one of `file`, `path` (already on the disk, e.g. presigned upload) or `url`; link with `type` (`product`, `product_model`, `owner`), `target` and `role`; variants inherit model assets.
- Create locales and channels before writing localizable/scopable values: localizable values need an existing `locale`, scopable an existing channel as `scope`, both together one of that channel's locales; scoped prices use the channel's currencies. Read one channel with `scope` + `locales[]`.
- Associations: `associations` for plain types (`{type: {products: [...], product_models: [...]}}`), `quantified_associations` for quantified ones (`{identifier, quantity}` items); each type sent replaces its targets; two-way types mirror themselves.
- Workflow: transitions `submit`, `approve`, `reject`, `publish` (needs `approved` unless `showroom.workflow.require_approval` is off), `unpublish`, `archive`, `restore`; storefronts read `/versions/published`; editing an approved product returns it to draft. Completeness comes from family requirements (`required_channels` narrows them); search with `complete[scope]`/`complete[min]`.
- Import/export (`refactor-circus/impex`, optional; without it the start endpoints answer 501): flows `showroom:import-products` (a `.csv`/`.jsonl` given as `file`, `url` or `asset`; `mode` `upsert`/`create`/`update`), `showroom:upsert-products` (bind an Impex inbound channel's `flow` to it for ERP pushes), `showroom:export-products` (any search filters, `format`, `published`; result names the file asset), `showroom:feed:{name}` per `showroom.impex.feeds` entry (a channel's published products, optional `url` delivery; schedule via `impex.schedule`). CSV uses Akeneo columns: `code`, `code-locale`, `code-scope`, `code-locale-scope`, `price-USD`, `weight` + `weight-unit`. Rows may all fail by default; set `showroom.impex.allow_failures` (a share) to fail the run. A feed can deliver through an Impex outbound channel with `deliver_through`.
- Product webhooks (`showroom.impex.webhooks.enabled`, off by default, needs `refactor-circus/impex`): published products are the Impex stream `showroom.products`; vendors subscribe on Impex's subscriber API (`POST /impex/subscriber/subscriptions` with `stream`, `topics` from `content`/`pricing`/`assets`/`resources`/`catalog`, `filter` of `categories`/`owners`/`families`/`models` codes or `subjects` SKUs, `format` `thin`/`slice`/`full`, `options.channel`/`options.locales`, and `endpoint.url` or none for the feed). Never send product data to vendors any other way. Topics are append-only.
- Polling helpers: `updated_since` on the product list and `list-products-tool` (filters on `changed_at`, which every change a product shows moves, inherited ones included); ETag/`If-None-Match` → `304` on product show. Vendor subscriptions are managed with Impex's MCP tools (`list-streams-tool`, `create-subscription-tool`, …).
- Labels are a locale map: `['en' => 'Color']`; `$model->label()` resolves the current locale.
- Config keys: `authorization`, `policies`, `routes.*`, `mcp.*`, `cortex.*`, `ui.enabled`, `atrium.features`, `workflow.*`, `impex.*`, `media.*`, `search.*`, `pagination.*`.

## Examples

```php
use RefactorCircus\Showroom\Domains\Attribute\Actions\CreateAttributeAction;
use RefactorCircus\Showroom\Domains\Attribute\Actions\CreateAttributeGroupAction;
use RefactorCircus\Showroom\Domains\Attribute\Actions\CreateAttributeOptionAction;

app(CreateAttributeGroupAction::class)->execute(['code' => 'marketing', 'labels' => ['en' => 'Marketing']]);

$color = app(CreateAttributeAction::class)->execute([
    'code' => 'color',
    'type' => 'select',
    'group' => 'marketing',
    'labels' => ['en' => 'Color'],
]);

app(CreateAttributeOptionAction::class)->execute($color, ['code' => 'red', 'labels' => ['en' => 'Red']]);

app(\RefactorCircus\Showroom\Domains\Family\Actions\CreateFamilyAction::class)->execute([
    'code' => 'shirts',
    'attributes' => [['attribute' => 'color', 'is_required' => true]],
]);

app(\RefactorCircus\Showroom\Domains\Product\Actions\CreateProductAction::class)->execute([
    'identifier' => 'SHIRT-RED',
    'family' => 'shirts',
    'values' => ['color' => [['locale' => null, 'scope' => null, 'data' => 'red']]],
]);

$page = app(\RefactorCircus\Showroom\Domains\Product\Actions\ListProductsAction::class)->execute([
    'filters' => [['attribute' => 'color', 'operator' => '=', 'value' => 'red']],
]);
```

```php
// Validate a request with the Action's own rules, then execute it.
$data = $request->validate(CreateAttributeAction::rules());
app(CreateAttributeAction::class)->execute($data);
```

## Anti-patterns

- creating or updating the models (`RefactorCircus\Showroom\Domains\*\Models\*Model`, such as `ProductModel` for products and `ProductModelModel` for product models) directly instead of through Actions, which skips validation, catalog rules and events
- renaming a code or changing a type — create a new attribute instead
- sending a partial `attributes` list to `UpdateFamilyAction` expecting it to append; it replaces the membership
- querying product `values` JSON directly for listings instead of `ListProductsAction`, which goes through the configured search engine
- writing to the `values` column directly, which skips validation, uniqueness and index syncing
- adding per-product-type migrations or columns for attributes; attributes are defined at runtime
- exposing the API or the MCP web transport without authentication middleware
