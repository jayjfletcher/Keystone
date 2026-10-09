# Keystone

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `refactor-circus/keystone`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Architecture Conventions

- Code is organised into domain modules under `src/Domains/{Domain}` (namespace `RefactorCircus\Keystone\Domains\{Domain}`), mirroring the mono application's domain-module standard. Each domain has a `{Domain}ServiceProvider` (extending `RefactorCircus\Foundation\Support\ServiceProvider`), listed in `src/Domains/DomainServiceProvider.php`, its own API `routes.php` loaded through `loadApiRoutesFrom()`, and only the subdirectories it uses (`Models/ Policies/ Resources/ Enums/ Data/ Actions/ Events/ Http/{Controllers,Requests}/ Mcp/{Tools,Requests}/ Concerns/ Services/ Support/ Contracts/ Exceptions/ Console/`).
- Keystone stands on `refactor-circus/foundation`. `KeystoneServiceProvider` extends `RefactorCircus\Foundation\Support\PackageServiceProvider`: `definition()` describes the package (`Package::make('keystone', 'RefactorCircus\Keystone')`), `register()` calls `registerPackage()` right after merging config and before the domain providers, and `boot()` uses `registerPolicies()`, `registerMcpServer()`, `registerCortex()`, `registerAtriumPlugin()` and `loadHistoryRoutes()`. It keeps the rest of the cross-cutting wiring: views, translations, publish tags and the Impex integration.
- Use Foundation's classes rather than local copies: event contracts `RefactorCircus\Foundation\Contracts\{ActionStartingEvent,ActionFinishedEvent,ModelLifecycleEvent}`, the `RefactorCircus\Foundation\Models\Concerns\DispatchesModelEvents` trait, request bases `RefactorCircus\Foundation\Http\Requests\Request` and `RefactorCircus\Foundation\Mcp\Requests\Request`, `RefactorCircus\Foundation\Mcp\Tool`, `RefactorCircus\Foundation\Mcp\Server` (the base of `KeystoneServer`), `RefactorCircus\Foundation\Auth\Authorizer::for(...)` and `RefactorCircus\Foundation\Cortex\CortexIntegration::for(...)`. Catalog exceptions extend `RefactorCircus\Keystone\Exceptions\KeystoneException`, an abstract `PackageException` answering 409; the bundled policies extend `RefactorCircus\Keystone\Support\Policies\Policy`, which extends Foundation's `Policy`.
- History: `GET {keystone.routes.prefix}/history` (`keystone.history.index`) and `ListKeystoneHistoryTool` (`list-keystone-history-tool`, in `KeystoneServer::TOOLS`) come from Foundation and answer "not installed" until refactor-circus/keen is installed.
- Eloquent models are `{Entity}Model` (`ProductModel` is a product, `ProductModelModel` a product model). Relations name their keys explicitly, because Eloquent guesses keys from class names.
- Models moved from `RefactorCircus\Keystone\Models`: each domain provider keeps the old class names (and the `keystone_*` aliases for products, product models and owners, listed first) as morph aliases through `keepMorphAliases()`. Model events map to `{Domain}\Events\{Entity}{Hook}Event`.
- Classes whose names are stored stay put: `src/Impex` (Impex stores flow, source and action classes) and `src/Jobs` (queued payloads). `KeystoneSupportFeature` keeps its Pennant name with `#[Name]`.
- Everything Keystone sends out goes through Impex so it is recorded: feeds through `Impex::http()` or an Impex channel (`deliver_through`), vendor pushes through the product stream. The stream adapter lives in `src/Impex/Webhooks` (`ProductStream`, `ProductSnapshots`, `TopicMap`, `ProductScopeMatcher`, `ProductFormatter`, `CaptureProductChanges`) and is registered by `ImpexIntegration` only when `keystone.impex.webhooks.enabled` is true. `ProductSnapshots` loads a chunk of products in a constant number of queries; keep it that way. The topics in `keystone.impex.webhooks.topics` are stored as bits: append, never reorder.
- Cross-domain code lives in `src/Support`; the Atrium screens live in `src/Atrium`.
- Atrium owns every component and style. Keystone ships no `resources/css`, no stylesheet registration and no component namespace; views use only `x-atrium::*` components (bare form controls for table cells and inline rows, `description-list`, `progress`, `flash`, `audit-trail`) and Atrium's safelisted utilities, never `<style>` or `style=`. `tests/Feature/Ui/StylesTest.php` asserts `AtriumStyles::missingClasses()` and `AtriumStyles::inlineStyles()` are empty. A missing class or component is added to Atrium, not here.
- Screen controllers use Atrium's `RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens`; `RefactorCircus\Keystone\Atrium\ScreenAccess::allows()` delegates to `RefactorCircus\Atrium\Support\ScreenAccess::allows('keystone', ...)` and keeps Keystone's helpers (`transfers()`, `@keystoneCan`). `KeystonePlugin::features()` uses `featuresFromConfig('keystone.atrium.features')`.
- Show screens end with `<x-atrium::audit-trail source="keystone" :subject="$record" />` and the product list with `<x-atrium::audit-trail source="keystone" />`. Domain providers register `AuditHooks::label()` only where the audit log's default naming falls short (localized labels, associations, versions).
- One config file, `config/keystone.php`; its keys do not change.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
