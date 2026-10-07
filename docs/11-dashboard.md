# Dashboard

Keystone registers itself with [Atrium](https://github.com/jayjfletcher/Atrium) — discovered from `composer.json` and switched off with `keystone.ui.enabled`. Atrium owns the dashboard's path (`/atrium` by default), middleware and `viewAtrium` gate.

- **Catalog → Products:** search through the configured engine, filter by family and status, create products, edit values (one input per attribute, typed), enable or disable, see inherited values.
- **Catalog → Product models:** create root models, edit their values, add sub-models and variant products by picking their axis values.
- **Products — workflow:** each product page shows its status and live version with buttons for the transitions open to it (and a comment), completeness per channel and locale with what is missing, and the latest history with revert. The product list shows and filters status. Family pages narrow each requirement to channels.
- **Audit history (with [jayi/keen](https://github.com/jayjfletcher/Keen)):** product, product model, family, attribute, category, owner, asset and channel pages show that record's audit entries, and the product list shows Keystone's latest. Without Keen nothing renders. Product versions and revert stay as they are.
- **Catalog → Association types:** create plain, two-way and quantified types. Product and product model pages list associations (inherited ones marked) and add or remove one at a time.
- **Catalog → Channels:** add and remove locales; create channels with their locales, currencies and category tree.
- **Values:** product and product model pages pick a locale and a channel, so every localizable and scopable value can be edited.
- **Catalog → Assets:** upload, browse as a thumbnail grid, filter by type or product, edit labels, replace files, link and unlink. Product, product model and owner pages show their assets (a variant's inherited ones too) and upload-and-link a file in one step.
- **Catalog → Categories:** create trees, browse a whole branch nested, add subcategories, rename, reorder or move a branch, jump to its products. Products and product models take their categories as a comma-separated list of codes and show those inherited from models.
- **Catalog → Owners:** browse and create owners, see each owner's chain, add children, move an owner under another, jump to its products; manage owner types and their chain rules.
- **Catalog → Families:** create families; add, remove, reorder and require attributes; pick the label attribute; create family variants and open them.
- **Catalog → Attributes:** filter by search, type and group; create an attribute; edit its group, label, flags and settings; add and delete options on select and multiselect attributes.
- **Catalog → Attribute groups:** create, rename, reorder and delete groups, and see the attributes in each.
- **Catalog → Import & export:** upload a file to import, start an export, follow the latest runs (with jayi/impex). See [Import, export and feeds](12-impex.md).
- **Widgets:** offered in Atrium's widget picker, placed on no one's dashboard until they add them:
  - *Products by status* — counts per review state and live, each linking to the filtered product list;
  - *Completeness* — average score and fully complete products per channel and locale;
  - *Review queue* — products waiting for review, oldest first;
  - *Recent changes* — the latest versions with product, action and author.
- **Settings:** how the catalog is exposed — authorization, API prefix, MCP transports, page sizes.
- **Search:** products by identifier; assets, categories, owners, families, attributes and attribute groups by code or label.

The pages call the same Actions as the API, so they validate the same way. The dashboard edits the label for the current locale only and keeps every other locale's label. Attribute settings are edited as JSON. 

Publish the views with `--tag="keystone-views"` to change them.

## Look

The screens follow Atrium's screen conventions. Every action - create, save, delete, filter, upload, link, a workflow transition, a back link - is an `<x-atrium::icon-button>`: an icon whose label is its tooltip and accessible name. Statuses are `<x-atrium::status-dot>`s, coloured in one place, `JayI\Keystone\Atrium\Badges`:

| Status | Colour |
|---|---|
| Product in review; import or export pending or waiting | `info` (kept for awaiting a decision or a turn) |
| Product approved, enabled or live; run completed | `success` |
| Product draft; run running or rolling back | `primary` |
| Run failed | `danger` |
| Product archived or disabled; run cancelled | `neutral` |

Each dot carries `data-status` with the raw value. Labels such as an attribute's type or flags stay badges. Every navigation item has a Heroicons icon.

## Who sees what

With `keystone.authorization` on, each screen asks the policies in `keystone.policies` exactly as the JSON API and MCP tools ask - the same ability on the same model or model class - through `JayI\Keystone\Atrium\ScreenAccess`. Controllers refuse with it (403) and views hide controls with it, so a control is shown exactly when its action is allowed:

| Shown / allowed | Asks |
|---|---|
| Each navigation item and list page | `viewAny` on its model |
| Import & export | `create` (import) or `viewAny` (export) on `Product` |
| A record's page | `view` on the record |
| New / create forms and cards, add-child forms | `create` on the model created |
| Save forms, transitions, revert, link and unlink an asset, add and remove associations | `update` on the record |
| Delete buttons | `delete` on the record |
| Add an option | `update` on the attribute and `create` on `AttributeOption` |
| Upload and link a file | `create` on `Asset`, then `update` on the new asset |
| Widgets | `viewAny` on `Product` |
| Search | `viewAny` on each kind of record searched |

Forms a user may view but not save (product, product model, attribute and family) are shown read-only, without the save button. In your own views, use the same check:

```blade
@keystoneCan('update', $product)
    ...
@endkeystoneCan
```

With `keystone.authorization` off, everything is shown and allowed, and Atrium's `viewAtrium` gate is the only check.

## Switching Keystone off

With [jayi/pennantplus](https://github.com/jayjfletcher/PennantPlus) installed, `JayI\Keystone\Atrium\Features\KeystoneSupportFeature` switches Keystone in Atrium on and off as a whole: navigation, widgets, settings, search and pages (which answer 404). It is on until its global value is set, and only the global value counts - per-user access stays with the policies:

```php
Feature::for(null)->deactivate(KeystoneSupportFeature::class);
```

`keystone.atrium.features` lists the features that must all be on (default `[KeystoneSupportFeature::class]`): point it at a subclass to change the default, or at your own features. Feature classes that cannot be loaded - KeystoneSupportFeature without jayi/pennantplus - are skipped, so nothing is checked until Pennant is installed. The JSON API and MCP tools are not affected.
