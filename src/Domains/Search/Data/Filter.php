<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Search\Data;

/**
 * One condition on an attribute value.
 */
final readonly class Filter
{
    public const array OPERATORS = ['=', '!=', 'in', 'not_in', '>', '>=', '<', '<=', 'empty', 'not_empty'];

    public function __construct(
        public string $attribute,
        public string $operator,
        public mixed $value = null,
        public ?string $locale = null,
        public ?string $scope = null,
    ) {}

    /**
     * @param  array<string, mixed>  $filter
     */
    public static function fromArray(array $filter): self
    {
        return new self(
            attribute: is_string($filter['attribute'] ?? null) ? $filter['attribute'] : '',
            operator: is_string($filter['operator'] ?? null) ? $filter['operator'] : '=',
            value: $filter['value'] ?? null,
            locale: is_string($filter['locale'] ?? null) ? $filter['locale'] : null,
            scope: is_string($filter['scope'] ?? null) ? $filter['scope'] : null,
        );
    }

    /**
     * The value as a list, for `in` and `not_in`.
     *
     * @return array<int, mixed>
     */
    public function values(): array
    {
        return is_array($this->value) ? array_values($this->value) : [$this->value];
    }
}
