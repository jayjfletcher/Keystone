# Channels and locales

## Locales

A locale is a language the catalog holds content in: `en`, `en_US`, `zh-Hant`. Localizable values can only be written in a locale that exists.

```http
POST /keystone/locales  {"code": "fr_FR", "labels": {"en_US": "French (France)"}}
```

A locale a channel publishes in cannot be deleted (`409`). Deleting any other locale purges the values written in it, in a queued job.

## Channels

A channel is somewhere products are published — a storefront, a print catalog, a marketplace:

```json
{
  "code": "ecommerce",
  "locales": ["en_US", "fr_FR"],
  "currencies": ["USD", "EUR"],
  "category_tree": "web_navigation"
}
```

- `locales`: at least one; the languages the channel publishes in.
- `currencies`: ISO 4217 codes it sells in.
- `category_tree`: the root category of the branch it sells, or null. That root cannot be deleted while a channel uses it.

Deleting a channel purges the values scoped to it, in a queued job. Values already written in a locale a channel later drops stay stored.

## What is enforced

| Attribute | `locale` | `scope` |
|---|---|---|
| localizable | an existing locale | — |
| scopable | — | an existing channel |
| both | one of **that channel's** locales | an existing channel |

A price in a scopable attribute may only use the channel's currencies. Errors come back at the path sent, such as `values.copy.0.locale`.

Label maps (`labels`) are not held to existing locales, so translations can be prepared before a locale is added.

## Reading one channel

`GET /keystone/products/{identifier}`, `GET /keystone/product-models/{code}` and `GET /keystone/products` accept `scope` and `locales[]`, returning only that channel's and those locales' values, plus the values that depend on neither — what an export to one storefront needs:

```http
GET /keystone/products/TEE-001?scope=print&locales[]=en_US
```

## Actions

`ListLocalesAction`, `ShowLocaleAction`, `CreateLocaleAction`, `UpdateLocaleAction`, `DeleteLocaleAction`, `ListChannelsAction`, `ShowChannelAction`, `CreateChannelAction`, `UpdateChannelAction`, `DeleteChannelAction`.
