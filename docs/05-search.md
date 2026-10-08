# Search

Products are listed and searched through a search engine you choose with `keystone.search.engine`. Every surface — `ListProductsAction`, `GET /keystone/products`, `list-products-tool`, the dashboard — goes through it.

| Engine | Needs | Filters | Facets | Scale |
|---|---|---|---|---|
| `database` (default) | nothing | all operators on every type but price | — | thousands of products |
| `elasticsearch` | `jayi/stretch` | all operators on every type but price | ✅ | millions |
| `scout` | `laravel/scout` + a Scout engine | `=`, `!=`, ranges, `in`, `not_in` — as far as the Scout engine supports them | — | engine-dependent |

You can also bind your own class implementing `JayI\Keystone\Domains\Search\Contracts\SearchEngine`, and name it in the config.

## Querying

```http
GET /keystone/products?search=tee&family=shirts&enabled=1
    &filters[0][attribute]=color&filters[0][operator]=in&filters[0][value][]=red&filters[0][value][]=blue
    &filters[1][attribute]=weight&filters[1][operator]=>=&filters[1][value]=100
    &facets[]=color&sort=-updated_at&page=1&per_page=25
```

- `search`: full text over the identifier and text, textarea and select values (inherited ones included).
- `owner`: products owned by this owner code or anything beneath it.
- `category`: products filed in this category code or any category beneath it.
- `status`: `draft`, `in_review`, `approved` or `archived`; `published`: with or without a live version.
- `complete`: `{scope, locale?, min}` — see [Workflow](11-workflow.md).
- `updated_since`: products whose own record changed at or after this moment (ISO 8601, or any date Laravel's `date` rule accepts) — see [Changed since](#changed-since).
- `filters`: all must hold. Operators: `=`, `!=`, `in`, `not_in`, `>`, `>=`, `<`, `<=`, `empty`, `not_empty`. Add `locale` / `scope` for localizable / scopable attributes. Multiselect `=` means "contains". Metric filters compare the amount.
- `facets`: attribute codes to count values of across every match (Elasticsearch only). Returned as `facets: {"color": {"red": 12, "blue": 3}}`.
- `sort`: `identifier`, `created_at` or `updated_at`, `-` for descending.
- Pagination is page-numbered, with `meta.total` and `meta.last_page`.

A filter the engine cannot run answers `422` with a message.

## Changed since

`updated_since` compares the product's own `updated_at`, on every engine: the database engine queries `keystone_products.updated_at`, Elasticsearch a range on the indexed `updated_at` date, and Scout passes `updated_at >= <ISO 8601 string>` to its engine (declare `updated_at` filterable, as for any Scout filter; the engine must support a range on it).

Only a write to the product's own row moves that timestamp: its values, family, owner or `enabled` flag, and workflow transitions. An update that only refiles categories or changes associations need not touch the row, and a change the product inherits never does: an edit to its product model, a family, a category or owner, or an asset linked to it or replaced. Combine `updated_since` with `sort=updated_at` to page through recent changes; to hear about inherited changes too, subscribe to the product stream through Impex ([Product webhooks](12-impex.md#product-webhooks)).

Indexed engines answer from the index, so a product changed moments ago appears once its `SyncProductIndex` job has run.

## Keeping the index in step

Engines with an index (`elasticsearch`, `scout`) are fed by a queued job, `SyncProductIndex`, dispatched after each write commits. It carries product ids only. Changing a product model re-indexes its variants. Before the jobs are dispatched, `ProductIndex::queue()` fires `JayI\Keystone\Domains\Search\Events\ProductsQueuedForSync` with the product ids, inside the write's transaction, so a listener can follow every product whose presentation may have changed — its own edits, a transition, or a change it inherits from a model, family, category, owner or channel. Product webhooks are built on it. Choose the queue with:

```php
'search' => [
    'queue' => ['connection' => 'redis', 'queue' => 'search'],
],
```

Rebuild the whole index at any time:

```bash
php artisan keystone:search:reindex
```

The indexed document holds the identifier, family, parent model, owner and owner chain, categories and their ancestors, status, published flag, completeness per channel and locale, enabled flag, dates, a `text` field for full text, and `values` in storage shape — `values.color.<all_channels>.<all_locales>` — with all inherited values merged.

## Elasticsearch

```bash
composer require jayi/stretch
php artisan vendor:publish --tag="stretch-config"
```

```env
ELASTICSEARCH_HOST=localhost:9200
```

```php
// config/keystone.php
'search' => [
    'engine' => 'elasticsearch',
    'elasticsearch' => [
        'index' => 'keystone_products',
        'connection' => null, // a connection from config/stretch.php
    ],
],
```

Then create the index with `php artisan keystone:search:reindex`. Keystone maps every string value as a `keyword` for exact filters, `text` for full text, and lets numbers, booleans and dates map dynamically.

## Scout

```bash
composer require laravel/scout meilisearch/meilisearch-php
```

```php
'search' => [
    'engine' => 'scout',
    'scout' => ['index' => 'keystone_products'],
],
```

Scout's own `scout.driver` picks the engine. Declare the fields you filter on as filterable in the engine's index settings (for Meilisearch, `family`, `enabled`, `parent` and each `values.<code>.<all_channels>.<all_locales>` you filter by), as Scout expects.
