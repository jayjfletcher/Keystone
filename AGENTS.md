# Showroom

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `refactor-circus/showroom`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Architecture Conventions

- Code is organised into domain modules under `src/Domains/{Domain}` (namespace `RefactorCircus\Showroom\Domains\{Domain}`), mirroring the mono application's domain-module standard. Each domain has a `{Domain}ServiceProvider` (extending `RefactorCircus\Keystone\Support\ServiceProvider`), listed in `src/Domains/DomainServiceProvider.php`, its own API `routes.php` loaded through `loadApiRoutesFrom()`, and only the subdirectories it uses (`Models/ Policies/ Resources/ Enums/ Data/ Actions/ Events/ Http/{Controllers,Requests}/ Mcp/{Tools,Requests}/ Concerns/ Services/ Support/ Contracts/ Exceptions/ Console/`).
- Showroom stands on `refactor-circus/keystone`. `ShowroomServiceProvider` extends `RefactorCircus\Keystone\Support\PackageServiceProvider`: `definition()` describes the package (`Package::make('showroom', 'RefactorCircus\Showroom')`), `register()` calls `registerPackage()` right after merging config and before the domain providers, and `boot()` uses `registerPolicies()`, `registerMcpServer()`, `registerCortex()`, `registerAtriumPlugin()` and `loadHistoryRoutes()`. It keeps the rest of the cross-cutting wiring: views, translations, publish tags and the Impex integration.
- Use Keystone's classes rather than local copies: event contracts `RefactorCircus\Keystone\Contracts\{ActionStartingEvent,ActionFinishedEvent,ModelLifecycleEvent}`, the `RefactorCircus\Keystone\Models\Concerns\DispatchesModelEvents` trait, request bases `RefactorCircus\Keystone\Http\Requests\Request` and `RefactorCircus\Keystone\Mcp\Requests\Request`, `RefactorCircus\Keystone\Mcp\Tool`, `RefactorCircus\Keystone\Mcp\Server` (the base of `ShowroomServer`), `RefactorCircus\Keystone\Auth\Authorizer::for(...)` and `RefactorCircus\Keystone\Cortex\CortexIntegration::for(...)`. Catalog exceptions extend `RefactorCircus\Showroom\Exceptions\ShowroomException`, an abstract `PackageException` answering 409; the bundled policies extend `RefactorCircus\Showroom\Support\Policies\Policy`, which extends Keystone's `Policy`.
- History: `GET {showroom.routes.prefix}/history` (`showroom.history.index`) and `ListShowroomHistoryTool` (`list-showroom-history-tool`, in `ShowroomServer::TOOLS`) come from Keystone and answer "not installed" until refactor-circus/keen is installed.
- Eloquent models are `{Entity}Model` (`ProductModel` is a product, `ProductModelModel` a product model). Relations name their keys explicitly, because Eloquent guesses keys from class names.
- Models moved from `RefactorCircus\Showroom\Models`: each domain provider keeps the old class names (and the `showroom_*` aliases for products, product models and owners, listed first) as morph aliases through `keepMorphAliases()`. Model events map to `{Domain}\Events\{Entity}{Hook}Event`.
- Classes whose names are stored stay put: `src/Impex` (Impex stores flow, source and action classes) and `src/Jobs` (queued payloads). `ShowroomSupportFeature` keeps its Pennant name with `#[Name]`.
- Everything Showroom sends out goes through Impex so it is recorded: feeds through `Impex::http()` or an Impex channel (`deliver_through`), vendor pushes through the product stream. The stream adapter lives in `src/Impex/Webhooks` (`ProductStream`, `ProductSnapshots`, `TopicMap`, `ProductScopeMatcher`, `ProductFormatter`, `CaptureProductChanges`) and is registered by `ImpexIntegration` only when `showroom.impex.webhooks.enabled` is true. `ProductSnapshots` loads a chunk of products in a constant number of queries; keep it that way. The topics in `showroom.impex.webhooks.topics` are stored as bits: append, never reorder.
- Cross-domain code lives in `src/Support`; the Atrium screens live in `src/Atrium`.
- Atrium owns every component and style. Showroom ships no `resources/css`, no stylesheet registration and no component namespace; views use only `x-atrium::*` components (bare form controls for table cells and inline rows, `description-list`, `progress`, `flash`, `audit-trail`) and Atrium's safelisted utilities, never `<style>` or `style=`. `tests/Feature/Ui/StylesTest.php` asserts `AtriumStyles::missingClasses()` and `AtriumStyles::inlineStyles()` are empty. A missing class or component is added to Atrium, not here.
- Screen controllers use Atrium's `RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens`; `RefactorCircus\Showroom\Atrium\ScreenAccess::allows()` delegates to `RefactorCircus\Atrium\Support\ScreenAccess::allows('showroom', ...)` and keeps Showroom's helpers (`transfers()`, `@showroomCan`). `ShowroomPlugin::features()` uses `featuresFromConfig('showroom.atrium.features')`.
- Show screens end with `<x-atrium::audit-trail source="showroom" :subject="$record" />` and the product list with `<x-atrium::audit-trail source="showroom" />`. Domain providers register `AuditHooks::label()` only where the audit log's default naming falls short (localized labels, associations, versions).
- One config file, `config/showroom.php`; its keys do not change.

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
