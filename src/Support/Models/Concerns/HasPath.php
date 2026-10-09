<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * A node of a tree stored with a materialized path — "/root/…/self/" — so
 * a whole subtree is one prefix match at any depth.
 *
 * @property string|null $parent_id
 * @property string $path
 * @property int $depth
 */
trait HasPath
{
    /**
     * The nodes from the root down to, and including, this one.
     *
     * @return Collection<int, self>
     */
    public function chain(): Collection
    {
        $ids = array_values(array_filter(explode('/', $this->path)));

        return self::query()->whereKey($ids)->orderBy('depth')->get();
    }

    /**
     * This node and everything beneath it.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeSubtreeOf(Builder $query, self $node): Builder
    {
        return $query->where('path', 'like', $node->path.'%');
    }

    /**
     * The path this node has under the given parent.
     */
    public function pathUnder(?self $parent): string
    {
        return ($parent->path ?? '/').$this->getKey().'/';
    }

    /**
     * Whether the given node is this one or sits beneath it.
     */
    public function contains(self $node): bool
    {
        return str_starts_with($node->path, $this->path);
    }
}
