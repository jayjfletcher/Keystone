# Import, export and feeds

Bulk imports, exports and syndication feeds run as [Impex](https://github.com/jayi/impex) flows. Impex is optional:

```bash
composer require jayi/impex
php artisan migrate
```

With it installed (and `keystone.impex.enabled` on), Keystone registers these flows:

| Flow | Does |
|---|---|
| `keystone:import-products` | A `.csv` or `.jsonl` file → products |
| `keystone:upsert-products` | Records passed inline (an ERP push) → products |
| `keystone:export-products` | Products matching a search → a `.jsonl` or `.csv` file |
| `keystone:feed:{name}` | One per configured feed: a channel's published products → a file, optionally pushed to a URL |

Every flow is an ordinary Impex run: its status, steps, failures and result are on Impex's API, MCP tools and dashboard, and Impex can pause, retry, resume and schedule it. Without Impex, the start endpoints and tools answer `501`.

## Importing

Rows go through `CreateProductAction` and `UpdateProductAction`, so validation, uniqueness, versions, completeness and indexing apply exactly as over the API.

```http
POST /keystone/imports
Content-Type: multipart/form-data

file=@products.csv
mode=upsert
```

Give the file as exactly one of:

- `file` — an upload,
- `url` — fetched and stored as an asset,
- `asset` — the code of an existing asset (for example one uploaded to S3 with a presigned URL).

The format comes from the extension, or send `format` (`csv` or `jsonl`). `mode` is one of:

| Mode | Existing identifier | New identifier |
|---|---|---|
| `upsert` (default) | updated | created |
| `create` | skipped | created |
| `update` | updated | skipped |

The answer is Impex's run, `202 Accepted`. When it completes, its result counts the rows:

```json
{"total": 1200, "succeeded": 1188, "failed": 12}
```

Each failed row keeps its reason on the run's batch items. By default any number of rows may fail and the run still completes; set `keystone.impex.allow_failures` to a share (`0.02` = 2%) to fail the run past it.

Rows are processed in chunks (`keystone.impex.chunk`) and remembered by position, so a retried or resumed run never imports a row twice.

### JSONL

One product per line, in the API's shape:

```json
{"identifier": "TEE-001", "family": "shirts", "categories": ["tops"], "values": {"name": [{"locale": "en", "scope": null, "data": "Tee"}]}}
```

### CSV

A header row, then one product per row. Columns follow Akeneo's conventions:

| Column | Holds |
|---|---|
| `identifier` | Required |
| `family`, `parent`, `owner` | Codes |
| `enabled` | `1` or `0` |
| `categories` | Comma-separated codes |
| `name` | A value that is neither localizable nor scopable |
| `name-en` | Localizable, in `en` |
| `description-ecommerce` | Scopable, on `ecommerce` |
| `description-en-ecommerce` | Both |
| `price-USD` | A price in one currency (`price-en-ecommerce-USD` for a scoped price) |
| `weight`, `weight-unit` | A metric's amount and unit |

Multiselects are comma-separated option codes; booleans `1` or `0`. An empty cell leaves the value as it is.

## Exporting

```http
POST /keystone/exports
{"family": "shirts", "scope": "ecommerce", "locales": ["en"], "format": "csv", "published": true}
```

- Every [search](05-search.md) filter applies: `search`, `family`, `category`, `owner`, `filters`, `complete`, … plus `scope` and `locales` to keep one channel's values.
- `format`: `jsonl` (default) or `csv`, in the import format — an export imports back unchanged.
- `published`: export the live versions instead of the working copies; unpublished products are left out.
- `code`: the asset code of the file (default `export-{run id}`).

The export pages through the search (`keystone.impex.export_page_size`) as a resumable step, so a large catalog outlives a Lambda time limit. The result names the asset holding the file, saved under `keystone.impex.export_path` on the media disk:

```json
{"asset": "export-01jq…", "count": 5400, "format": "csv"}
```

## Feeds

A feed is a named export of one channel's published products, with the channel's locales, filed in its category tree:

```php
// config/keystone.php
'impex' => [
    'feeds' => [
        'google' => [
            'channel' => 'ecommerce',
            'format' => 'jsonl',
            'url' => 'https://feeds.example.com/ingest',   // optional
            'ledger_channel' => 'google-feed',            // optional, default: the feed name
        ],
    ],
],
```

Each feed is the flow `keystone:feed:google`. With a `url`, the file is `POST`ed to it through `Impex::http()`, so the delivery is recorded in Impex's ledger, and retried up to three times. Schedule it with Impex:

```php
// config/impex.php
'schedule' => [
    'keystone:feed:google' => '0 3 * * *',
],
```

Or run it now: `POST /impex/runs {"flow": "keystone:feed:google"}`.

## ERP and storefront connectors

An ERP pushes products by posting to an Impex inbound channel bound to `keystone:upsert-products`:

```php
// config/impex.php
'channels' => [
    'erp' => [
        'direction' => 'inbound',
        'signing_secret' => env('ERP_SECRET'),
        'signature_header' => 'X-Signature',
        'flow' => 'keystone:upsert-products',
        'idempotency_header' => 'X-Request-Id',
    ],
],
```

`POST /impex/channels/erp` with a list of products, or `{"products": [...]}`, in the JSONL shape. The request lands in the ledger, the signature is checked, and a run upserts the records. Storefronts read the live versions directly (`GET /keystone/products/{identifier}/versions/published`) or take a feed.

## From PHP

```php
use JayI\Keystone\Domains\Transfer\Actions\StartExportAction;
use JayI\Keystone\Domains\Transfer\Actions\StartImportAction;

$run = app(StartImportAction::class)->execute(['asset' => 'supplier-2026-09', 'mode' => 'update']);

$run = app(StartExportAction::class)->execute(['family' => 'shirts', 'format' => 'csv']);
```

Both return the Impex `Run`. MCP agents use `start-import-tool` and `start-export-tool`, then Impex's tools to follow the run.

## Dashboard

**Catalog → Import & export** uploads a file to import, starts an export, and lists the latest Keystone runs with their results, linked to the Impex run page and the exported asset.
