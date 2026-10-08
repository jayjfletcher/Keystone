# Completeness, workflow and versioning

## Completeness

A family says which attributes are required, and optionally on which channels:

```json
{"attributes": [
  {"attribute": "name", "is_required": true},
  {"attribute": "marketing_copy", "is_required": true, "required_channels": ["ecommerce"]}
]}
```

Without `required_channels`, a required attribute is required on every channel. For every channel and each locale it publishes in, a product scores:

```json
"completeness": [
  {"scope": "ecommerce", "locale": "en", "required": 2, "missing": 1, "ratio": 50, "missing_attributes": ["marketing_copy"]}
]
```

- A required slot is filled when it holds a non-empty value — the product's own or inherited from its product models — in the channel (for scopable attributes) and the locale (for localizable ones).
- Nothing required counts as 100%. Products without a family have no completeness.
- Scores are stored and refreshed by the queued sync job whenever the product, its models, its family's requirements or the channels change. `php artisan keystone:search:reindex` recomputes them all.

Search by completeness:

```http
GET /keystone/products?complete[scope]=ecommerce&complete[locale]=en&complete[min]=100
```

Without `locale`, every locale of the channel must reach `min` (default 100).

## Workflow

| Status | |
|---|---|
| `draft` | Being enriched |
| `in_review` | Submitted for review |
| `approved` | Reviewed; ready to publish |
| `archived` | Retired |

`POST /keystone/products/{identifier}/transitions` with `transition` and an optional `comment`:

| Transition | From | To |
|---|---|---|
| `submit` | draft | in_review |
| `approve` | in_review | approved |
| `reject` | in_review | draft |
| `publish` | approved | — (sets the live version) |
| `unpublish` | any but archived | — (clears the live version) |
| `archive` | any but archived | archived (and unpublished) |
| `restore` | archived | draft |

A transition from the wrong status answers `409` with what it needs.

### Publishing

`publish` points `published_version` at the product's current version. The working copy stays editable: storefronts read `GET /keystone/products/{identifier}/versions/published`, which does not move until the next publish. Editing an approved product sends it back to `draft`, so changes are reviewed before they go live.

The same holds for vendors subscribed to [product webhooks](12-impex.md#product-webhooks): values, family and associations reach them from the published version, so a draft edit is sent only once it is published. Categories, owner and linked assets are not versioned and follow the live product, so a published product's change there is sent straight away. Unpublishing or archiving a product reaches its subscribers as `removed`.

```php
'workflow' => [
    'require_approval' => true,  // off: publish from any status but archived
    'require_complete' => false, // on: submit only at 100% on every channel and locale
],
```

## Versioning and audit

Every write to a product records a version:

```json
{
  "version": 4,
  "action": "updated",
  "comment": null,
  "author": {"type": "users", "id": "7"},
  "changes": {
    "values.name": {"old": [{"locale": null, "scope": null, "data": "Tee"}], "new": [{"locale": null, "scope": null, "data": "Classic tee"}]},
    "categories": {"old": [], "new": ["shirts"]}
  },
  "created_at": "2026-09-29T10:00:00+00:00"
}
```

- Actions: `created`, `updated`, `submitted`, `approved`, `rejected`, `published`, `unpublished`, `archived`, `restored`, `reverted`.
- The author is the authenticated user, when there is one.
- A write that changes nothing records nothing; workflow steps always record.
- Each version's `snapshot` holds the product's own values, the values it inherited at that moment, family, parent, owner, categories, associations, enabled flag and status.

| | |
|---|---|
| `GET /keystone/products/{identifier}/versions` | History, newest first (filter by `action`) |
| `GET /keystone/products/{identifier}/versions/{n\|latest\|published}` | One version with its snapshot |
| `POST /keystone/products/{identifier}/revert` | `{"version": 2}` — restore its values, categories, associations, family, owner and enabled flag as a new `reverted` version |

Revert leaves the workflow status and the live version alone. Versions are deleted with their product.

## Actions

`TransitionProductAction`, `ListProductVersionsAction`, `ShowProductVersionAction`, `RevertProductAction`.
