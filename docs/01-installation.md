# Installation

```bash
composer require refactor-circus/keystone

php artisan vendor:publish --tag="keystone-migrations"
php artisan migrate
```

Keystone requires PHP 8.4 and Laravel 13. It depends on `refactor-circus/atrium` for the dashboard and `laravel/mcp` for the MCP server. For product search beyond the database, add `laravel/scout` with a Scout engine (picked automatically once installed) — see [Search](05-search.md). For bulk imports, exports and feeds, add `refactor-circus/impex` — see [Import, export and feeds](12-impex.md).

Asset files go to `keystone.media.disk` — use S3 or another object store on Vapor and Lambda. See [Media](08-media.md).

Run a queue worker: completeness scores and the search index are refreshed by queued jobs after each write, as are value purges after an attribute, option, locale or channel is deleted.

## Publishing

| Tag | Publishes |
|---|---|
| `keystone` | Everything below |
| `keystone-config` | `config/keystone.php` |
| `keystone-migrations` | The catalog migrations |
| `keystone-views` | Dashboard views, to `resources/views/vendor/keystone` |
| `keystone-lang` | Translations, to `lang/vendor/keystone` |

## Securing the surfaces

- **HTTP API:** add authentication middleware to `keystone.routes.middleware` (for example `['api', 'auth:sanctum']`). With `keystone.authorization` on — the default — every call also needs an authenticated user and passes the policies in `keystone.policies`.
- **MCP:** both transports ship disabled. Add auth middleware to `keystone.mcp.web.middleware` before enabling the web transport.
- **Dashboard:** Atrium owns the path, middleware and `viewAtrium` gate.

## Tables

All tables are prefixed `keystone_`: `keystone_attribute_groups`, `keystone_attributes`, `keystone_attribute_options`, `keystone_families`, `keystone_family_attributes`, `keystone_family_variants`, `keystone_family_variant_attributes`, `keystone_product_models`, `keystone_products`, `keystone_product_unique_values`, `keystone_owner_types`, `keystone_owner_type_parents`, `keystone_owners`, `keystone_categories`, `keystone_category_product`, `keystone_category_product_model`, `keystone_assets`, `keystone_asset_links`, `keystone_locales`, `keystone_channels`, `keystone_channel_locale`, `keystone_association_types`, `keystone_associations`, `keystone_product_completeness`, `keystone_versions`. Keys are ULIDs.
