<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

/**
 * Assets were listed.
 */
final class AssetsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, AssetModel>  $assets
     */
    public function __construct(
        public CursorPaginator $assets,
    ) {}
}
