<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use JayI\Foundation\Models\Concerns\DispatchesModelEvents;
use JayI\Keystone\Database\Factories\LocaleFactory;
use JayI\Keystone\Support\Models\Concerns\HasLabels;

/**
 * A language the catalog holds content in: `en`, `fr_CA`. Localizable values
 * can only be written in a locale that exists.
 *
 * @property string $id
 * @property string $code
 * @property array<string, string>|null $labels
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class LocaleModel extends Model
{
    use DispatchesModelEvents;

    /** @use HasFactory<LocaleFactory> */
    use HasFactory;

    use HasLabels;
    use HasUlids;

    protected $table = 'keystone_locales';

    protected $fillable = [
        'code',
        'labels',
    ];

    /**
     * @return BelongsToMany<ChannelModel, $this>
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(ChannelModel::class, 'keystone_channel_locale', 'locale_id', 'channel_id')->orderBy('code');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'labels' => 'array',
        ];
    }

    protected static function newFactory(): LocaleFactory
    {
        return LocaleFactory::new();
    }
}
