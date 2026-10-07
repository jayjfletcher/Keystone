<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Services;

use Illuminate\Contracts\Config\Repository as Config;
use JayI\Keystone\Domains\Attribute\Enums\AttributeType;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Services\Values;
use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Data\Filter;
use JayI\Keystone\Domains\Search\Data\ProductQuery;
use JayI\Keystone\Domains\Search\Data\SearchResults;
use JayI\Keystone\Domains\Search\Exceptions\UnsupportedSearchException;
use JayI\Stretch\Domains\Aggregation\Contracts\AggregationBuilderContract;
use JayI\Stretch\Domains\Query\Contracts\BoolQueryBuilderContract;
use JayI\Stretch\Domains\Query\Contracts\QueryBuilderContract;
use JayI\Stretch\Stretch;

/**
 * Searches an Elasticsearch index through jayi/stretch.
 *
 * Every string value is indexed as a keyword, so filters match exactly; the
 * `text` field holds the product's text for full-text search. Facets are
 * terms aggregations over the requested attributes.
 */
final class ElasticsearchEngine implements SearchEngine
{
    public function __construct(
        private readonly Stretch $stretch,
        private readonly Config $config,
    ) {}

    public function maintainsIndex(): bool
    {
        return true;
    }

    public function index(iterable $products): void
    {
        $operations = [];

        foreach ($products as $product) {
            $operations[] = ['index' => ['_index' => $this->indexName(), '_id' => $product->id]];
            $operations[] = ProductDocument::from($product);
        }

        if ($operations !== []) {
            $this->stretch()->bulk($operations);
        }
    }

    public function remove(array $ids): void
    {
        $operations = array_map(
            fn (string $id): array => ['delete' => ['_index' => $this->indexName(), '_id' => $id]],
            $ids,
        );

        if ($operations !== []) {
            $this->stretch()->bulk($operations);
        }
    }

    public function reset(): void
    {
        $stretch = $this->stretch();

        if ($stretch->indexExists($this->indexName())) {
            $stretch->deleteIndex($this->indexName());
        }

        $stretch->createIndex($this->indexName(), [
            'mappings' => [
                'dynamic_templates' => [
                    ['value_strings' => [
                        'path_match' => 'values.*',
                        'match_mapping_type' => 'string',
                        'mapping' => ['type' => 'keyword'],
                    ]],
                ],
                'properties' => [
                    'id' => ['type' => 'keyword'],
                    'identifier' => ['type' => 'keyword'],
                    'family' => ['type' => 'keyword'],
                    'parent' => ['type' => 'keyword'],
                    'owner' => ['type' => 'keyword'],
                    'owners' => ['type' => 'keyword'],
                    'categories' => ['type' => 'keyword'],
                    'category_tree' => ['type' => 'keyword'],
                    'status' => ['type' => 'keyword'],
                    'published' => ['type' => 'boolean'],
                    'enabled' => ['type' => 'boolean'],
                    'text' => ['type' => 'text'],
                    'created_at' => ['type' => 'date'],
                    'updated_at' => ['type' => 'date'],
                ],
            ],
        ]);
    }

    public function search(ProductQuery $query): SearchResults
    {
        $types = $this->types($query);

        $builder = $this->stretch()->index($this->indexName())
            ->source(false)
            ->trackTotalHits()
            ->from($query->offset())
            ->size($query->perPage)
            ->sort($query->sort, $query->direction)
            ->sort('id');

        if ($query->search !== null) {
            $builder->bool(function (BoolQueryBuilderContract $bool) use ($query): void {
                $bool->should(fn (QueryBuilderContract $q) => $q->match('text', $query->search, ['operator' => 'and']));
                $bool->should(fn (QueryBuilderContract $q) => $q->prefix('identifier', (string) $query->search));
                $bool->minimumShouldMatch(1);
            });
        }

        if ($query->family !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('family', $query->family));
        }

        if ($query->enabled !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('enabled', $query->enabled));
        }

        if ($query->parent !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('parent', $query->parent));
        }

        if ($query->status !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('status', $query->status));
        }

        if ($query->published !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('published', $query->published));
        }

        if ($query->complete !== null) {
            $complete = $query->complete;

            foreach ($complete->locales() ?: ['-'] as $locale) {
                $builder->filter(fn (QueryBuilderContract $q) => $q->range('completeness.'.$complete->scope.'.'.$locale)->gte($complete->min));
            }
        }

        if ($query->category !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('category_tree', $query->category));
        }

        if ($query->owner !== null) {
            $builder->filter(fn (QueryBuilderContract $q) => $q->term('owners', $query->owner));
        }

        foreach ($query->filters as $filter) {
            $this->applyFilter($builder, $filter, $types[$filter->attribute] ?? AttributeType::Text);
        }

        foreach ($query->facets as $code) {
            $field = $this->field($code, null, null, $types[$code] ?? AttributeType::Select);

            $builder->aggregation('facet_'.$code, fn (AggregationBuilderContract $aggregation) => $aggregation->terms($field)->size(100));
        }

        $response = $builder->execute();

        /** @var array<int, array{_id: string}> $hits */
        $hits = $response['hits']['hits'] ?? [];

        $total = $response['hits']['total'] ?? 0;

        return new SearchResults(
            ids: array_map(fn (array $hit): string => $hit['_id'], $hits),
            total: (int) (is_array($total) ? ($total['value'] ?? 0) : $total),
            facets: $this->facets($response, $query->facets),
        );
    }

    private function applyFilter(QueryBuilderContract $builder, Filter $filter, AttributeType $type): void
    {
        if ($type === AttributeType::Price) {
            throw UnsupportedSearchException::filter('elasticsearch', $filter->attribute, $filter->operator);
        }

        $field = $this->field($filter->attribute, $filter->scope, $filter->locale, $type);
        // Query strings carry numbers as text; ranges must compare numbers.
        $bound = is_string($filter->value) && is_numeric($filter->value) ? $filter->value + 0 : $filter->value;
        $value = is_bool($filter->value) || $type !== AttributeType::Boolean ? $filter->value : filter_var($filter->value, FILTER_VALIDATE_BOOLEAN);

        match ($filter->operator) {
            '=' => $builder->filter(fn (QueryBuilderContract $q) => $q->term($field, $value)),
            '!=' => $builder->filter(fn (QueryBuilderContract $q) => $q->bool(fn (BoolQueryBuilderContract $bool) => $bool->mustNot(fn (QueryBuilderContract $not) => $not->term($field, $value)))),
            'in' => $builder->filter(fn (QueryBuilderContract $q) => $q->terms($field, $filter->values())),
            'not_in' => $builder->filter(fn (QueryBuilderContract $q) => $q->bool(fn (BoolQueryBuilderContract $bool) => $bool->mustNot(fn (QueryBuilderContract $not) => $not->terms($field, $filter->values())))),
            '>' => $builder->filter(fn (QueryBuilderContract $q) => $q->range($field)->gt($bound)),
            '>=' => $builder->filter(fn (QueryBuilderContract $q) => $q->range($field)->gte($bound)),
            '<' => $builder->filter(fn (QueryBuilderContract $q) => $q->range($field)->lt($bound)),
            '<=' => $builder->filter(fn (QueryBuilderContract $q) => $q->range($field)->lte($bound)),
            'not_empty' => $builder->filter(fn (QueryBuilderContract $q) => $q->exists($field)),
            'empty' => $builder->filter(fn (QueryBuilderContract $q) => $q->bool(fn (BoolQueryBuilderContract $bool) => $bool->mustNot(fn (QueryBuilderContract $not) => $not->exists($field)))),
            default => throw UnsupportedSearchException::filter('elasticsearch', $filter->attribute, $filter->operator),
        };
    }

    private function field(string $code, ?string $scope, ?string $locale, AttributeType $type): string
    {
        $field = implode('.', ['values', $code, $scope ?? Values::ALL_CHANNELS, $locale ?? Values::ALL_LOCALES]);

        return $type === AttributeType::Metric ? $field.'.amount' : $field;
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  array<int, string>  $codes
     * @return array<string, array<string, int>>
     */
    private function facets(array $response, array $codes): array
    {
        $facets = [];

        foreach ($codes as $code) {
            /** @var array<int, array{key: string|int|bool, key_as_string?: string, doc_count: int}> $buckets */
            $buckets = $response['aggregations']['facet_'.$code]['buckets'] ?? [];

            foreach ($buckets as $bucket) {
                $facets[$code][(string) ($bucket['key_as_string'] ?? $bucket['key'])] = $bucket['doc_count'];
            }

            $facets[$code] ??= [];
        }

        return $facets;
    }

    /**
     * @return array<string, AttributeType>
     */
    private function types(ProductQuery $query): array
    {
        $codes = [...array_map(fn (Filter $filter): string => $filter->attribute, $query->filters), ...$query->facets];

        if ($codes === []) {
            return [];
        }

        return AttributeModel::query()
            ->whereIn('code', $codes)
            ->get(['code', 'type'])
            ->mapWithKeys(fn (AttributeModel $attribute): array => [$attribute->code => $attribute->type])
            ->all();
    }

    private function stretch(): Stretch
    {
        $connection = $this->config->get('keystone.search.elasticsearch.connection');

        return is_string($connection) ? $this->stretch->connection($connection) : $this->stretch;
    }

    private function indexName(): string
    {
        return $this->config->string('keystone.search.elasticsearch.index', 'keystone_products');
    }
}
