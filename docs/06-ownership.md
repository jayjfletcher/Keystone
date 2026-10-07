# Ownership

Products belong to owners, and owners form chains of any shape and depth:

```
vendor → series → product
vendor → product
vendor → vendor → series → product
manufacturer → vendor → product
```

## Owner types

Owner types are defined at runtime, each with the rules for where its owners may sit:

```json
{"code": "series", "parent_types": ["vendor"], "can_be_root": false, "owns_products": true}
```

| Field | Meaning |
|---|---|
| `parent_types` | Owner types allowed as parent. `null` allows any type; `[]` allows none. |
| `can_be_root` | Whether an owner of this type may have no parent. Default `true`. |
| `owns_products` | Whether products and product models may be assigned to it. Default `true`. |

Rules are checked when owners are created or moved; changing them later does not rewrite owners already placed. An owner type with owners cannot be deleted.

## Owners

```json
{"code": "classic", "type": "series", "parent": "acme"}
```

- `code` is unique across all owners and never changes; neither does `type`.
- Sending `parent` on update **moves** the owner with everything beneath it. Moving an owner under itself or its own descendant is refused.
- `GET /keystone/owners/classic` returns its `chain`, root first: `["globex", "acme", "classic"]`.
- List by `type`, direct children of a `parent`, everything `under` an owner at any depth, or `roots`.
- An owner with child owners, products or product models cannot be deleted.

Each owner stores a materialized path, so "everything beneath Acme" is a single prefix query however deep the chain.

## Assigning products

Set `owner` on a simple product or a root product model, naming the deepest owner in the chain — the series, not the vendor. Sub-models and variant products take their root model's owner and cannot set their own; the API returns it as their `owner`.

## Searching by owner

`GET /keystone/products?owner=acme` finds every product owned by Acme **or anything beneath it**, on every search engine. The indexed document carries `owner` and `owners` (the whole chain), and moving an owner re-indexes the products beneath it.

## Actions

`ListOwnerTypesAction`, `ShowOwnerTypeAction`, `CreateOwnerTypeAction`, `UpdateOwnerTypeAction`, `DeleteOwnerTypeAction`, `ListOwnersAction`, `ShowOwnerAction`, `CreateOwnerAction`, `UpdateOwnerAction`, `DeleteOwnerAction`.
