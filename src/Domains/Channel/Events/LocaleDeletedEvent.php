<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

/**
 * The Locale `deleted` Eloquent event.
 */
final class LocaleDeletedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public LocaleModel $locale) {}

    public function model(): Model
    {
        return $this->locale;
    }

    public function hook(): string
    {
        return 'deleted';
    }
}
