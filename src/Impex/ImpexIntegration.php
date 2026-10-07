<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use JayI\Impex\Domains\Flow\Services\FlowRegistry;
use JayI\Impex\Domains\Flow\Support\Flow;
use JayI\Impex\Domains\Run\Enums\RunTrigger;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Impex\Impex;
use JayI\Impex\ImpexServiceProvider;
use JayI\Keystone\Domains\Transfer\Exceptions\ImpexMissingException;
use JayI\Keystone\Impex\Flows\ExportProductsFlow;
use JayI\Keystone\Impex\Flows\FeedFlow;
use JayI\Keystone\Impex\Flows\ImportProductsFlow;
use JayI\Keystone\Impex\Flows\UpsertProductsFlow;

/**
 * Registers Keystone's flows with Impex, when Impex is installed.
 *
 * Impex is optional. Nothing here runs unless its service provider is loaded
 * and `keystone.impex.enabled` is true; every Impex class is referenced only
 * behind that check.
 */
final class ImpexIntegration
{
    public const string IMPORT = 'keystone:import-products';

    public const string UPSERT = 'keystone:upsert-products';

    public const string EXPORT = 'keystone:export-products';

    public function __construct(
        private readonly Application $app,
        private readonly Config $config,
    ) {}

    public function active(): bool
    {
        return $this->config->get('keystone.impex.enabled', true) === true
            && class_exists(ImpexServiceProvider::class)
            && $this->app->getProvider(ImpexServiceProvider::class) !== null;
    }

    /**
     * Register the flows as the registry is first built, so an application
     * that never runs one pays nothing — and its own `impex.flows` config
     * still wins for any slug.
     */
    public function register(): void
    {
        if (! $this->active()) {
            return;
        }

        $this->app->afterResolving(FlowRegistry::class, function (FlowRegistry $flows): void {
            $flows->registerMany($this->flows());
        });
    }

    /**
     * @return array<string, class-string<Flow>>
     */
    public function flows(): array
    {
        $flows = [
            self::IMPORT => ImportProductsFlow::class,
            self::UPSERT => UpsertProductsFlow::class,
            self::EXPORT => ExportProductsFlow::class,
        ];

        /** @var array<string, mixed> $feeds */
        $feeds = (array) $this->config->get('keystone.impex.feeds', []);

        foreach (array_keys($feeds) as $name) {
            $flows[FeedFlow::PREFIX.$name] = FeedFlow::class;
        }

        return $flows;
    }

    /**
     * Start a run of one of Keystone's flows.
     *
     * @param  array<int|string, mixed>  $arguments
     *
     * @throws ImpexMissingException
     */
    public function start(string $slug, array $arguments, RunTrigger $trigger = RunTrigger::Api): RunModel
    {
        if (! $this->active()) {
            throw ImpexMissingException::make();
        }

        return $this->app->make(Impex::class)->run($slug, $arguments, $trigger, tags: ['keystone' => 'true']);
    }
}
