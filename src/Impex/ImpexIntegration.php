<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use JayI\Impex\Domains\Flow\Services\FlowRegistry;
use JayI\Impex\Domains\Flow\Support\Flow;
use JayI\Impex\Domains\Run\Enums\RunTrigger;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Impex\Domains\Subscription\Services\StreamRegistry;
use JayI\Impex\Impex;
use JayI\Impex\ImpexServiceProvider;
use JayI\Keystone\Domains\Transfer\Exceptions\ImpexMissingException;
use JayI\Keystone\Impex\Flows\ExportProductsFlow;
use JayI\Keystone\Impex\Flows\FeedFlow;
use JayI\Keystone\Impex\Flows\ImportProductsFlow;
use JayI\Keystone\Impex\Flows\UpsertProductsFlow;
use JayI\Keystone\Impex\Webhooks\CaptureProductChanges;
use JayI\Keystone\Impex\Webhooks\ProductScopeMatcher;
use JayI\Keystone\Impex\Webhooks\ProductSnapshots;
use JayI\Keystone\Impex\Webhooks\ProductStream;
use JayI\Keystone\Impex\Webhooks\TopicMap;

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

        $this->registerWebhooks();
    }

    /**
     * Whether published products are offered to subscribers as a stream.
     * Off by default: once on, every product write is compared with what
     * subscribers last saw, which is work worth doing only when someone
     * subscribes.
     */
    public function webhooks(): bool
    {
        return $this->active()
            && $this->config->get('keystone.impex.webhooks.enabled', false) === true
            && class_exists(StreamRegistry::class);
    }

    /**
     * Register the product stream with Impex, and report product changes to
     * it. Every outgoing push, feed and webhook then goes through Impex and
     * lands in its ledger.
     */
    private function registerWebhooks(): void
    {
        if (! $this->webhooks()) {
            return;
        }

        $this->app->singleton(TopicMap::class);
        $this->app->singleton(ProductSnapshots::class);
        $this->app->singleton(ProductScopeMatcher::class);

        $this->app->afterResolving(StreamRegistry::class, function (StreamRegistry $streams): void {
            /** @var class-string<ProductStream> $stream */
            $stream = $this->config->get('keystone.impex.webhooks.stream_class', ProductStream::class);

            $streams->register($stream);
        });

        $this->app->make(Dispatcher::class)->subscribe(CaptureProductChanges::class);
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
