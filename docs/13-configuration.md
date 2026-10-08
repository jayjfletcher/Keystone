# Configuration

Publish with `php artisan vendor:publish --tag="keystone-config"`.

| Key | Default | Meaning |
|---|---|---|
| `authorization` | `true` | Require an authenticated user on the API, MCP and Atrium screens, and check every call against `policies`; the screens show only what the user may use |
| `policies` | bundled policies | Model ⇒ policy class used by the Gate |
| `routes.enabled` | `true` | Register the HTTP API |
| `routes.prefix` | `keystone` | API path prefix |
| `routes.middleware` | `['api']` | API middleware. Add authentication. |
| `mcp.web.enabled` | `false` | Serve MCP over HTTP |
| `mcp.web.route` | `mcp/keystone` | MCP web route |
| `mcp.web.middleware` | `[]` | MCP web middleware. Add authentication. |
| `mcp.local.enabled` | `false` | Register the local (stdio) MCP server |
| `mcp.local.handle` | `keystone` | Local server handle |
| `cortex.enabled` | `true` | Register with Cortex when it is installed |
| `cortex.server` | `keystone` | Server name in Cortex |
| `cortex.tools` | `null` | Tool names to offer Cortex, or `null` for all |
| `ui.enabled` | `true` | Register the Atrium plugin |
| `atrium.features` | `[KeystoneSupportFeature::class]` | Features that must all be on for Keystone to appear in Atrium; classes that cannot be loaded (without jayi/pennantplus) are skipped |
| `workflow.require_approval` | `true` | Publish only approved products |
| `workflow.require_complete` | `false` | Submit only products 100% complete on every channel and locale |
| `impex.enabled` | `true` | Register the Impex flows when jayi/impex is installed |
| `impex.chunk` | `500` | Import rows per batch chunk |
| `impex.allow_failures` | `1.0` | Share of import rows that may fail before the run fails |
| `impex.tries` | `1` | Attempts per import row |
| `impex.export_page_size` | `500` | Products per export page |
| `impex.export_path` | `keystone/exports` | Folder export files are written under, on the media disk |
| `impex.feeds` | `[]` | Syndication feeds: `channel`, `format`, `url`, `ledger_channel`, `deliver_through` (an Impex outbound channel to send the file through instead of `POST`ing it to `url`) |
| `impex.webhooks.enabled` | `false` | Offer published products to Impex subscribers as a stream. Needs jayi/impex with `impex.enabled` on; once on, every product write is compared with what subscribers last saw |
| `impex.webhooks.stream` | `keystone.products` | The stream's key |
| `impex.webhooks.stream_class` | `ProductStream::class` | The stream class registered with Impex; extend `JayI\Keystone\Impex\Webhooks\ProductStream` to change it |
| `impex.webhooks.topics` | `content`, `pricing`, `assets`, `resources`, `catalog` | Topics subscribers choose from, in a fixed order: append, never reorder or remove. Each claims attribute values by `types` or attribute `groups`, product `fields`, or linked `assets` (`true`); the `default` topic takes the rest |
| `impex.webhooks.formatters` | `[]` | Extra payload formats, name ⇒ Impex `Formatter` class, beside `thin`, `slice` and `full` |
| `media.disk` | `null` | Disk for asset files (null: the app default) |
| `media.path` | `keystone/assets` | Folder assets are written under |
| `media.max_kilobytes` | `51200` | Largest file accepted |
| `media.mime_types` | `null` | Allowed MIME types, or null for any |
| `media.temporary_urls` | `null` | Minutes a signed URL lasts; null for plain URLs |
| `media.delete_files` | `true` | Delete the file when its asset is deleted |
| `media.download_timeout` | `30` | Seconds allowed to fetch an asset from a URL |
| `search.engine` | `database` | `database`, `elasticsearch`, `scout`, or a `SearchEngine` class |
| `search.queue.connection` | `null` | Queue connection for index syncs (null: default) |
| `search.queue.queue` | `null` | Queue name for index syncs |
| `search.elasticsearch.index` | `keystone_products` | Elasticsearch index name |
| `search.elasticsearch.connection` | `null` | Connection from `config/stretch.php` |
| `search.scout.index` | `keystone_products` | Scout index name |
| `pagination.per_page` | `25` | Default page size for every listing |
| `pagination.max_per_page` | `100` | Largest `per_page` a caller may ask for |
