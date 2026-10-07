<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;

/**
 * A product's versions were listed.
 */
final class ProductVersionsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, VersionModel>  $versions
     */
    public function __construct(
        public ProductModel $product,
        public CursorPaginator $versions,
    ) {}
}
