<?php

declare(strict_types=1);

namespace JayI\Keystone\Support\Concerns;

use Illuminate\Database\Eloquent\Model;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;

/**
 * Placing a node of a materialized-path tree, and carrying its subtree along
 * when it moves.
 */
trait MovesInTree
{
    /**
     * Set the node's parent, path and depth, and rewrite every descendant.
     *
     * @template TNode of OwnerModel|CategoryModel
     *
     * @param  TNode  $node
     * @param  TNode|null  $parent
     */
    private function place(Model $node, ?Model $parent): void
    {
        $oldPath = $node->path;
        $oldDepth = $node->depth;

        $node->parent()->associate($parent);
        $node->depth = $parent === null ? 0 : $parent->depth + 1;
        $node->path = $node->pathUnder($parent);
        $node->save();

        if ($oldPath === '' || $oldPath === $node->path) {
            return;
        }

        // Descendants keep their place relative to the node that moved.
        $node->newQuery()
            ->where('path', 'like', $oldPath.'%')
            ->whereKeyNot($node->getKey())
            ->lazyById()
            ->each(function (OwnerModel|CategoryModel $descendant) use ($oldPath, $oldDepth, $node): void {
                $descendant->path = $node->path.substr($descendant->path, strlen($oldPath));
                $descendant->depth = $descendant->depth - $oldDepth + $node->depth;
                $descendant->save();
            });
    }
}
