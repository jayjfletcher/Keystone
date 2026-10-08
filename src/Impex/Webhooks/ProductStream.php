<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex\Webhooks;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use JayI\Impex\Domains\Subscription\Contracts\Formatter;
use JayI\Impex\Domains\Subscription\Contracts\SubscriptionMatcher;
use JayI\Impex\Domains\Subscription\Enums\Selection;
use JayI\Impex\Domains\Subscription\Models\SubscriptionModel;
use JayI\Impex\Domains\Subscription\Support\AbstractStream;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * Keystone's published products, as a stream vendors subscribe to.
 *
 * The subject key is the product identifier — the SKU vendors already know,
 * and the one they list when subscribing to particular products. A product
 * counts as present once published; unpublishing, archiving or deleting it
 * reaches its subscribers as removed.
 */
class ProductStream extends AbstractStream
{
    public function __construct(
        protected readonly ProductSnapshots $products,
        protected readonly TopicMap $topicMap,
        protected readonly ProductScopeMatcher $scopes,
        protected readonly Config $config,
        protected readonly Container $container,
        protected readonly ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        $key = $this->config->get('keystone.impex.webhooks.stream', 'keystone.products');

        return is_string($key) ? $key : 'keystone.products';
    }

    public function topics(): array
    {
        return $this->topicMap->topics();
    }

    public function snapshots(array $keys): array
    {
        return $this->products->load($keys);
    }

    public function slice(array $snapshot): array
    {
        return $this->topicMap->slice($snapshot);
    }

    public function matcher(): SubscriptionMatcher
    {
        return $this->scopes;
    }

    public function filterRules(): array
    {
        return [
            'filter.categories' => ['sometimes', 'array', 'max:500'],
            'filter.categories.*' => ['string', 'exists:keystone_categories,code'],
            'filter.owners' => ['sometimes', 'array', 'max:500'],
            'filter.owners.*' => ['string', 'exists:keystone_owners,code'],
            'filter.families' => ['sometimes', 'array', 'max:500'],
            'filter.families.*' => ['string', 'exists:keystone_families,code'],
            'filter.models' => ['sometimes', 'array', 'max:500'],
            'filter.models.*' => ['string', 'exists:keystone_product_models,code'],
        ];
    }

    public function formats(): array
    {
        $formats = [
            'thin' => new ProductFormatter('thin', $this->topicMap),
            'slice' => new ProductFormatter('slice', $this->topicMap),
            'full' => new ProductFormatter('full', $this->topicMap),
        ];

        /** @var array<string, class-string<Formatter>> $custom */
        $custom = $this->config->get('keystone.impex.webhooks.formatters', []);

        foreach ($custom as $name => $class) {
            $formatter = $this->container->make($class);

            if ($formatter instanceof Formatter) {
                $formats[$name] = $formatter;
            }
        }

        return $formats;
    }

    /**
     * Every published product the subscription covers, one per line, a
     * thousand at a time.
     */
    public function export(SubscriptionModel $subscription, mixed $handle): void
    {
        $formatter = new ProductFormatter('full', $this->topicMap);

        ProductModel::query()
            ->whereNotNull('published_version')
            ->select(['id', 'identifier'])
            ->chunkById(1000, function (Collection $products) use ($subscription, $handle, $formatter): void {
                /** @var list<string> $identifiers */
                $identifiers = array_values($products->map(fn (ProductModel $product): string => $product->identifier)->all());

                $snapshots = array_filter($this->products->load($identifiers));

                foreach ($this->covered($subscription, $snapshots) as $identifier => $snapshot) {
                    fwrite($handle, (string) json_encode([
                        'identifier' => $identifier,
                        'data' => $formatter->present($snapshot, $subscription),
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
                }
            });
    }

    /**
     * @param  array<string, array<string, mixed>>  $snapshots
     * @return array<string, array<string, mixed>>
     */
    private function covered(SubscriptionModel $subscription, array $snapshots): array
    {
        return match ($subscription->selection) {
            Selection::All => $snapshots,
            Selection::Filter => array_intersect_key($snapshots, array_flip($this->scopes->match($snapshots, [$subscription])[$subscription->id] ?? [])),
            Selection::List => array_intersect_key($snapshots, array_flip(
                $this->db->table('impex_subscription_subjects')
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('subject_key', array_keys($snapshots))
                    ->pluck('subject_key')
                    ->map(fn (mixed $key): string => (string) $key)
                    ->all(),
            )),
        };
    }
}
