<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * The Locale `deleting` Eloquent event.
 */
final class LocaleDeletingEvent implements ModelLifecycleEvent
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
        return 'deleting';
    }
}
