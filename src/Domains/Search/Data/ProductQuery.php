<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Search\Data;

use Carbon\CarbonImmutable;

/**
 * A product search, independent of the engine that runs it.
 */
final readonly class ProductQuery
{
    public const array SORTABLE = ['identifier', 'created_at', 'updated_at'];

    /**
     * @param  array<int, Filter>  $filters
     * @param  'asc'|'desc'  $direction
     * @param  array<int, string>  $facets  Attribute codes to count values of.
     */
    public function __construct(
        public ?string $search = null,
        public ?string $family = null,
        public ?bool $enabled = null,
        public ?string $parent = null,
        public ?string $owner = null,
        public ?string $category = null,
        public ?string $status = null,
        public ?bool $published = null,
        public ?Complete $complete = null,
        public array $filters = [],
        public string $sort = 'identifier',
        public string $direction = 'asc',
        public int $page = 1,
        public int $perPage = 25,
        public array $facets = [],
        public ?CarbonImmutable $updatedSince = null,
    ) {}

    /**
     * @param  array<string, mixed>  $input  Validated against ListProductsAction::rules().
     */
    public static function fromArray(array $input): self
    {
        /** @var array<int, array<string, mixed>> $filters */
        $filters = is_array($input['filters'] ?? null) ? $input['filters'] : [];

        /** @var array<int, string> $facets */
        $facets = is_array($input['facets'] ?? null) ? array_values($input['facets']) : [];

        $sort = is_string($input['sort'] ?? null) ? $input['sort'] : 'identifier';
        $direction = 'asc';

        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
            $direction = 'desc';
        }

        return new self(
            search: is_string($input['search'] ?? null) && $input['search'] !== '' ? $input['search'] : null,
            family: is_string($input['family'] ?? null) ? $input['family'] : null,
            enabled: isset($input['enabled']) ? filter_var($input['enabled'], FILTER_VALIDATE_BOOLEAN) : null,
            parent: is_string($input['parent'] ?? null) ? $input['parent'] : null,
            owner: is_string($input['owner'] ?? null) && $input['owner'] !== '' ? $input['owner'] : null,
            category: is_string($input['category'] ?? null) && $input['category'] !== '' ? $input['category'] : null,
            status: is_string($input['status'] ?? null) && $input['status'] !== '' ? $input['status'] : null,
            published: isset($input['published']) ? filter_var($input['published'], FILTER_VALIDATE_BOOLEAN) : null,
            complete: is_array($input['complete'] ?? null) ? Complete::fromArray($input['complete']) : null,
            filters: array_map(fn (array $filter): Filter => Filter::fromArray($filter), $filters),
            sort: $sort,
            direction: $direction,
            page: isset($input['page']) ? max(1, (int) $input['page']) : 1,
            perPage: isset($input['per_page']) ? (int) $input['per_page'] : (int) config('keystone.pagination.per_page', 25),
            facets: $facets,
            updatedSince: is_string($input['updated_since'] ?? null) && $input['updated_since'] !== '' ? CarbonImmutable::parse($input['updated_since']) : null,
        );
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
