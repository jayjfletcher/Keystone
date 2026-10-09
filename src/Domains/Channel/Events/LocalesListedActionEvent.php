<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

/**
 * Locales were listed.
 */
final class LocalesListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, LocaleModel>  $locales
     */
    public function __construct(
        public CursorPaginator $locales,
    ) {}
}
