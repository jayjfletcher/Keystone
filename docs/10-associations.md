# Associations, bundles and kits

## Association types

```http
POST /showroom/association-types  {"code": "cross_sell"}
POST /showroom/association-types  {"code": "compatible", "is_two_way": true}
POST /showroom/association-types  {"code": "bundle", "is_quantified": true}
```

| Flag | Meaning |
|---|---|
| `is_two_way` | The association exists both ways, kept in sync automatically: link A to B and B shows A. For compatible parts and replacements. |
| `is_quantified` | Each associated item carries a quantity. For bundles and kits. |

Both flags are fixed once created, and a type cannot be both. A type in use cannot be deleted (`409`).

## Associating

Products and product models take associations on create and update, in two keys:

```json
{
  "associations": {
    "cross_sell": {"products": ["CAP", "SOCKS"], "product_models": ["classic-polo"]}
  },
  "quantified_associations": {
    "bundle": {"products": [{"identifier": "TEE", "quantity": 2}, {"identifier": "CAP", "quantity": 1}]}
  }
}
```

- Plain types go under `associations`, quantified ones under `quantified_associations`; the other way round is a validation error.
- Each type sent **replaces** that type's targets; types not sent are kept. Send an empty list to clear a type.
- Targets are products (by identifier) and product models (by code). A record cannot be associated with itself.
- Quantities are integers of at least 1.

Responses carry both keys. A variant product shows its product models' associations merged with its own; for quantified types, the variant's own quantity wins.

Deleting a product or product model removes every association from and to it.

## Bundles and kits

A bundle or kit is a product whose `quantified_associations` list its components. Showroom holds the composition; pricing and stock for the bundle are left to the commerce system that reads it.

## Actions

`ListAssociationTypesAction`, `ShowAssociationTypeAction`, `CreateAssociationTypeAction`, `UpdateAssociationTypeAction`, `DeleteAssociationTypeAction`. Associations themselves are written through the product and product model Actions.
