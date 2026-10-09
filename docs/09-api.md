# HTTP API

Routes are registered under `keystone.routes.prefix` (default `keystone`) with `keystone.routes.middleware` (default `['api']`). Disable them with `keystone.routes.enabled`.

## Endpoints

| Method | Path | Action |
|---|---|---|
| `GET` | `/keystone/attribute-groups` | `ListAttributeGroupsAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/attribute-groups` | `CreateAttributeGroupAction` → `201` |
| `GET` | `/keystone/attribute-groups/{group}` | `ShowAttributeGroupAction`, with its attributes |
| `PATCH` | `/keystone/attribute-groups/{group}` | `UpdateAttributeGroupAction` |
| `DELETE` | `/keystone/attribute-groups/{group}` | `DeleteAttributeGroupAction` → `204` |
| `GET` | `/keystone/attributes` | `ListAttributesAction` — `type`, `group`, `search`, `cursor`, `per_page` |
| `POST` | `/keystone/attributes` | `CreateAttributeAction` → `201` |
| `GET` | `/keystone/attributes/{attribute}` | `ShowAttributeAction`, with options for select types |
| `PATCH` | `/keystone/attributes/{attribute}` | `UpdateAttributeAction` |
| `DELETE` | `/keystone/attributes/{attribute}` | `DeleteAttributeAction` → `204` |
| `GET` | `/keystone/attributes/{attribute}/options` | `ListAttributeOptionsAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/attributes/{attribute}/options` | `CreateAttributeOptionAction` → `201` |
| `PATCH` | `/keystone/attributes/{attribute}/options/{option}` | `UpdateAttributeOptionAction` |
| `DELETE` | `/keystone/attributes/{attribute}/options/{option}` | `DeleteAttributeOptionAction` → `204` |
| `GET` | `/keystone/families` | `ListFamiliesAction` — `search`, `attribute`, `cursor`, `per_page` |
| `POST` | `/keystone/families` | `CreateFamilyAction` → `201` |
| `GET` | `/keystone/families/{family}` | `ShowFamilyAction`, with its attributes |
| `PATCH` | `/keystone/families/{family}` | `UpdateFamilyAction` |
| `DELETE` | `/keystone/families/{family}` | `DeleteFamilyAction` → `204` |
| `GET` | `/keystone/family-variants` | `ListFamilyVariantsAction` — `family`, `search`, `cursor`, `per_page` |
| `POST` | `/keystone/family-variants` | `CreateFamilyVariantAction` → `201` |
| `GET` | `/keystone/family-variants/{familyVariant}` | `ShowFamilyVariantAction`, with levels and common attributes |
| `PATCH` | `/keystone/family-variants/{familyVariant}` | `UpdateFamilyVariantAction` |
| `DELETE` | `/keystone/family-variants/{familyVariant}` | `DeleteFamilyVariantAction` → `204` |
| `GET` | `/keystone/product-models` | `ListProductModelsAction` — `family_variant`, `parent`, `roots`, `search`, `cursor`, `per_page` |
| `POST` | `/keystone/product-models` | `CreateProductModelAction` → `201` |
| `GET` | `/keystone/product-models/{productModel}` | `ShowProductModelAction`, with sub-models and products |
| `PATCH` | `/keystone/product-models/{productModel}` | `UpdateProductModelAction` |
| `DELETE` | `/keystone/product-models/{productModel}` | `DeleteProductModelAction` → `204` |
| `GET` | `/keystone/assets` | `ListAssetsAction` — `search`, `type`, `product`, `product_model`, `owner`, `role`, `cursor`, `per_page` |
| `POST` | `/keystone/assets` | `CreateAssetAction` — `file`, `path` or `url` → `201` |
| `GET` | `/keystone/assets/{asset}` | `ShowAssetAction`, with its links |
| `PATCH` | `/keystone/assets/{asset}` | `UpdateAssetAction` — labels, or replace the file (multipart: POST with `_method=PATCH`) |
| `DELETE` | `/keystone/assets/{asset}` | `DeleteAssetAction` → `204` |
| `POST` | `/keystone/assets/{asset}/links` | `AttachAssetAction` — `type`, `target`, `role`, `sort_order` |
| `DELETE` | `/keystone/assets/{asset}/links` | `DetachAssetAction` — `type`, `target`, optional `role` |
| `GET` | `/keystone/association-types` | `ListAssociationTypesAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/association-types` | `CreateAssociationTypeAction` → `201` |
| `GET` / `PATCH` / `DELETE` | `/keystone/association-types/{associationType}` | Show / update labels / delete |
| `GET` | `/keystone/locales` | `ListLocalesAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/locales` | `CreateLocaleAction` → `201` |
| `GET` / `PATCH` / `DELETE` | `/keystone/locales/{locale}` | Show / update labels / delete |
| `GET` | `/keystone/channels` | `ListChannelsAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/channels` | `CreateChannelAction` → `201` |
| `GET` / `PATCH` / `DELETE` | `/keystone/channels/{channel}` | Show / update / delete |
| `GET` | `/keystone/categories` | `ListCategoriesAction` — `roots`, `parent`, `under`, `search`, `cursor`, `per_page` |
| `POST` | `/keystone/categories` | `CreateCategoryAction` → `201` |
| `GET` | `/keystone/categories/{category}` | `ShowCategoryAction`, with its chain and children |
| `PATCH` | `/keystone/categories/{category}` | `UpdateCategoryAction` — labels, order, or move with `parent` |
| `DELETE` | `/keystone/categories/{category}` | `DeleteCategoryAction` → `204` |
| `GET` | `/keystone/owner-types` | `ListOwnerTypesAction` — `search`, `cursor`, `per_page` |
| `POST` | `/keystone/owner-types` | `CreateOwnerTypeAction` → `201` |
| `GET` | `/keystone/owner-types/{ownerType}` | `ShowOwnerTypeAction` |
| `PATCH` | `/keystone/owner-types/{ownerType}` | `UpdateOwnerTypeAction` |
| `DELETE` | `/keystone/owner-types/{ownerType}` | `DeleteOwnerTypeAction` → `204` |
| `GET` | `/keystone/owners` | `ListOwnersAction` — `type`, `parent`, `under`, `roots`, `search`, `cursor`, `per_page` |
| `POST` | `/keystone/owners` | `CreateOwnerAction` → `201` |
| `GET` | `/keystone/owners/{owner}` | `ShowOwnerAction`, with its chain and children |
| `PATCH` | `/keystone/owners/{owner}` | `UpdateOwnerAction` — labels, or move with `parent` |
| `DELETE` | `/keystone/owners/{owner}` | `DeleteOwnerAction` → `204` |
| `GET` | `/keystone/products` | `ListProductsAction` — search, `updated_since`; see [Search](05-search.md) |
| `POST` | `/keystone/products` | `CreateProductAction` → `201` |
| `GET` | `/keystone/products/{product}` | `ShowProductAction`, with inherited values — `scope`, `locales[]`; `ETag`, `304` on `If-None-Match` |
| `PATCH` | `/keystone/products/{product}` | `UpdateProductAction` |
| `DELETE` | `/keystone/products/{product}` | `DeleteProductAction` → `204` |
| `POST` | `/keystone/products/{product}/transitions` | `TransitionProductAction` — `transition`, `comment` |
| `GET` | `/keystone/products/{product}/versions` | `ListProductVersionsAction` — `action`, `cursor`, `per_page` |
| `GET` | `/keystone/products/{product}/versions/{version}` | `ShowProductVersionAction` — a number, `latest` or `published` |
| `POST` | `/keystone/products/{product}/revert` | `RevertProductAction` — `version`, `comment` |
| `POST` | `/keystone/imports` | `StartImportAction` → `202` with the Impex run — `file`, `url` or `asset`, `format`, `mode`; see [Import, export and feeds](12-impex.md) |
| `POST` | `/keystone/exports` | `StartExportAction` → `202` with the Impex run — search filters, `format`, `code`, `published` |

Path parameters are codes — identifiers for products. An option is looked up within its attribute.

## Example

```http
POST /keystone/attributes
Content-Type: application/json

{"code": "color", "type": "select", "group": "marketing", "labels": {"en": "Color"}}
```

```json
{
  "data": {
    "id": "01J...",
    "code": "color",
    "type": "select",
    "group": "marketing",
    "labels": {"en": "Color"},
    "is_unique": false,
    "is_localizable": false,
    "is_scopable": false,
    "settings": [],
    "sort_order": 0,
    "options": [],
    "created_at": "2026-09-29T09:00:00+00:00",
    "updated_at": "2026-09-29T09:00:00+00:00",
    "changed_at": "2026-09-29T09:00:00+00:00"
  }
}
```

## Pagination

Product search is page-numbered (`page`, `per_page`, `meta.total`). Other listings are cursor paginated in display order (`sort_order`, then `code`). Pass `meta.next_cursor` back as `cursor`. `per_page` defaults to `keystone.pagination.per_page` and is capped at `keystone.pagination.max_per_page`.

## Polling products

Clients that poll the catalog have two helpers:

- `GET /keystone/products?updated_since=2026-10-01T00:00:00Z` lists products where anything they show changed at or after that moment — inherited changes, refilings, associations and assets included. It filters on each product's `changed_at`, which the API returns; see [Search](05-search.md#changed-since).
- `GET /keystone/products/{product}` carries an `ETag` of its body. Send it back as `If-None-Match` and an unchanged product answers `304 Not Modified` with no body. The tag covers the response as asked for, so the same product read with other `scope` or `locales[]` has another tag.

Vendors who need every change, inherited ones included, subscribe to product webhooks instead of polling; see [Import, export and feeds](12-impex.md#product-webhooks).

## Errors

| Status | When |
|---|---|
| `403` | Unauthenticated or denied by a policy, with authorization on |
| `404` | Unknown code |
| `409` | A catalog rule: deleting something still in use (a group with attributes, a family with products, a family variant with models, an attribute that labels a family or shapes a variant), options on a type that takes none. The body is `{"message": "..."}`. |
| `422` | Validation — including changing a code, type or identifier, invalid values, taken unique values, missing or duplicate axes — and search filters the engine cannot run |

## Authorization

With `keystone.authorization` on, every request needs an authenticated user and is checked against the model's policy from `keystone.policies`: `viewAny`, `view`, `create`, `update` and `delete`. Option endpoints also check `view` or `update` on the parent attribute. The bundled policies allow any authenticated user; replace one to restrict the catalog:

```php
'policies' => [
    \RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel::class => \App\Policies\CatalogAttributePolicy::class,
    // ...
],
```
