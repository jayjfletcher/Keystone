<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

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
