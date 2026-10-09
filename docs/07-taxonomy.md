# Taxonomy

Categories form independent trees. A category without a parent is the root of a tree, so a catalog can keep several side by side — "Master catalog", "Web navigation", "Print catalog".

```http
POST /showroom/categories  {"code": "master"}
POST /showroom/categories  {"code": "clothing", "parent": "master"}
POST /showroom/categories  {"code": "shirts", "parent": "clothing", "sort_order": 1}
```

- `code` is unique across all trees and never changes. Codes are lowercase letters, digits and underscores.
- `sort_order` orders siblings.
- `GET /showroom/categories/shirts` returns its `chain` from the tree root: `["master", "clothing", "shirts"]`.
- List the trees with `roots=1`, a category's children with `parent`, or a whole branch with `under`.

## Moving

Sending `parent` on update moves the category with its whole branch — within its tree, into another tree, or to the root as a tree of its own (`"parent": null`). Moving a category under itself or its own descendant is refused. Products beneath are re-indexed.

## Deleting

A category with children cannot be deleted (`409`); trees are pruned leaf first. Deleting a leaf removes it from the products and product models filed in it.

## Filing products

`categories` on a product or product model is the whole list of category codes, from any trees; on update it replaces the list.

```json
{"identifier": "TEE-001", "categories": ["shirts", "sale"]}
```

Variant products inherit every category of their product models, on top of their own; the API returns the combined list.

## Searching by category

`GET /showroom/products?category=clothing` finds products filed in Clothing **or any category beneath it**, on every search engine. The indexed document carries `categories` (the assigned codes, inherited ones included) and `category_tree` (those plus all their ancestors).

## Actions

`ListCategoriesAction`, `ShowCategoryAction`, `CreateCategoryAction`, `UpdateCategoryAction`, `DeleteCategoryAction`.
