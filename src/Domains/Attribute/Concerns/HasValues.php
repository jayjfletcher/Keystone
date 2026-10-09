<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Concerns;

use RefactorCircus\Keystone\Domains\Attribute\Data\ValueFilter;
use RefactorCircus\Keystone\Domains\Attribute\Services\Values;

/**
 * Attribute values stored as JSON, plus those inherited from parent models.
 *
 * @property array<string, array<string, array<string, mixed>>>|null $values
 */
trait HasValues
{
    /**
     * Set by a read that asked for one channel or some locales.
     */
    private ?ValueFilter $valueFilter = null;

    /**
     * This record's own values, in storage shape.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function ownValues(): array
    {
        return $this->values ?? [];
    }

    /**
     * Own values over everything inherited from parent models. Levels hold
     * disjoint attributes, so a plain merge by attribute is exact.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function allValues(): array
    {
        return array_merge($this->inheritedValues(), $this->ownValues());
    }

    /**
     * All values, narrowed by the filter a read asked for, if any.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function presentedValues(): array
    {
        $filter = $this->valueFilter ?? null;

        return $filter instanceof ValueFilter ? $filter->apply($this->allValues()) : $this->allValues();
    }

    /**
     * Narrow what presentedValues() returns, for this instance only.
     */
    public function useValueFilter(ValueFilter $filter): static
    {
        $this->valueFilter = $filter;

        return $this;
    }

    /**
     * The data in one slot of all values, or null.
     */
    public function value(string $code, ?string $scope = null, ?string $locale = null): mixed
    {
        return Values::get($this->allValues(), $code, $scope, $locale);
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    abstract public function inheritedValues(): array;
}
