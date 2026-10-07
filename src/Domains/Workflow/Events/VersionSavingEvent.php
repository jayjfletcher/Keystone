<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;

/**
 * The Version `saving` Eloquent event.
 */
final class VersionSavingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public VersionModel $version) {}

    public function model(): Model
    {
        return $this->version;
    }

    public function hook(): string
    {
        return 'saving';
    }
}
