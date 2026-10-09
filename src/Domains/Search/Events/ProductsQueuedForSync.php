<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Search\Events;

/**
 * Products whose presentation may have changed, queued for the index: their
 * own edits, a transition, or a change they inherit from a model, family,
 * category, owner or channel.
 *
 * Fired inside the write's transaction, so a listener's own writes commit or
 * roll back with it. Carries ids only.
 */
final readonly class ProductsQueuedForSync
{
    /**
     * @param  array<int, string>  $ids
     */
    public function __construct(public array $ids) {}
}
