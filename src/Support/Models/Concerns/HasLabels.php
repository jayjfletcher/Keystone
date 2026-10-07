<?php

declare(strict_types=1);

namespace JayI\Keystone\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * A `labels` map of locale => label, falling back to the code.
 *
 * @property array<string, string>|null $labels
 * @property string $code
 */
trait HasLabels
{
    /**
     * The label in the given locale, else the app's locales, else the code.
     */
    public function label(?string $locale = null): string
    {
        $labels = $this->labels ?? [];

        foreach ([$locale ?? app()->getLocale(), app()->getFallbackLocale()] as $candidate) {
            if (isset($labels[$candidate]) && $labels[$candidate] !== '') {
                return $labels[$candidate];
            }
        }

        return $this->code;
    }

    /**
     * Match a term against the code or any label.
     *
     * @param  Builder<static>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        // PostgreSQL has no LIKE on json; every other driver compares the text.
        $labels = $this->getConnection()->getDriverName() === 'pgsql'
            ? $query->getQuery()->raw('CAST(labels AS TEXT)')
            : 'labels';

        $query->where(fn (Builder $builder): Builder => $builder
            ->where('code', 'like', '%'.$term.'%')
            ->orWhere($labels, 'like', '%'.$term.'%'));
    }
}
