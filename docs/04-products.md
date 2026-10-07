# Products and variants

## Values

Attribute values are sent and returned in one shape everywhere — a list of slots per attribute:

```json
{
  "name": [{"locale": null, "scope": null, "data": "Classic tee"}],
  "description": [
    {"locale": "en", "scope": null, "data": "Soft cotton"},
    {"locale": "fr", "scope": null, "data": "Coton doux"}
  ]
}
```

- `locale` is required for localizable attributes and must be an existing locale; `scope` is required for scopable attributes and must be an existing channel; both are null otherwise. See [Channels and locales](09-channels.md).
- On update, only the slots you send change. `"data": null` clears a slot.
- `data` by type:

| Type | `data` |
|---|---|
| `text`, `textarea` | string |
| `number` | integer |
| `decimal` | number or numeric string — returned as a string, so no precision is lost |
| `boolean` | `true` / `false` |
| `date` | `"2026-03-01"` |
| `select` | an option code |
| `multiselect` | a list of option codes |
| `price` | `[{"amount": "19.99", "currency": "USD"}]` |
| `metric` | `{"amount": "180", "unit": "gram"}` |

Each value is checked against its attribute's settings (length, bounds, decimals, currencies, options) and errors come back at the path you sent, such as `values.weight.0.data.unit`.

Values are stored as JSON on the product row, keyed by attribute, channel and locale.

## Simple products

A product has an `identifier` (its SKU: letters, digits, dots, dashes and underscores, never changed), an optional `family`, an optional `owner` (see [Ownership](06-ownership.md)), `categories` (see [Taxonomy](07-taxonomy.md)), `associations` and `quantified_associations` (see [Associations](10-associations.md)), an `enabled` flag and its values.

- With a family, only the family's attributes can be set.
- Without one, any attribute can.
- Changing the family keeps stored values, as in any PIM; values outside the new family stay hidden in the data until the product moves back.
- Completeness (required attributes) is not enforced on save.

## Unique attributes

A value of an `is_unique` attribute can be held by one product only. The check is enforced by a unique index on `keystone_product_unique_values`, so it holds under concurrent writes. Unique attributes are neither localizable nor scopable, and in a family variant they sit on the last level.

## Variants

A **family variant** describes how a family's products vary:

```json
{
  "code": "shirts_by_color_size",
  "family": "shirts",
  "levels": [
    {"axes": ["color"], "attributes": ["price"]},
    {"axes": ["size"], "attributes": ["ean", "weight"]}
  ]
}
```

- One or two levels, each with 1–5 **axes**: select, boolean or metric attributes of the family that are neither localizable nor scopable.
- Each level lists the attributes set there. Family attributes on no level are **common**, set once on the root product model.
- Levels can change only while no product model uses the variant.

Then build the tree:

| Record | Holds | Created with |
|---|---|---|
| Root product model | common values | `family_variant` |
| Sub-model (two-level variants only) | level-1 axes and attributes | `parent` = root model |
| Variant product | last-level axes and attributes | `parent` = the model at the last model level |

Every axis of a level must be filled, and siblings must differ on their axes. A variant product takes its family from the family variant, and its API `values` include everything inherited from its models. Changing a model re-indexes every variant beneath it.

## Deleting

- Deleting a product model deletes its sub-models and variant products.
- A family with products or product models, a family variant with product models, and an attribute placed in a family variant cannot be deleted (`409`).
- Deleting an attribute or an option purges its stored values in a queued job.

## Actions

| | |
|---|---|
| Family variants | `ListFamilyVariantsAction`, `ShowFamilyVariantAction`, `CreateFamilyVariantAction`, `UpdateFamilyVariantAction`, `DeleteFamilyVariantAction` |
| Product models | `ListProductModelsAction`, `ShowProductModelAction`, `CreateProductModelAction`, `UpdateProductModelAction`, `DeleteProductModelAction` |
| Products | `ListProductsAction` (search), `ShowProductAction`, `CreateProductAction`, `UpdateProductAction`, `DeleteProductAction` |
