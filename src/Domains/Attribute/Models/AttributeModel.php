<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use JayI\Foundation\Models\Concerns\DispatchesModelEvents;
use JayI\Keystone\Database\Factories\AttributeFactory;
use JayI\Keystone\Domains\Attribute\Enums\AttributeType;
use JayI\Keystone\Support\Models\Concerns\HasLabels;

/**
 * A typed product characteristic, defined at runtime: "color", "weight",
 * "description". Its code and type never change once created, because every
 * stored value depends on them.
 *
 * @property string $id
 * @property string $code
 * @property AttributeType $type
 * @property string|null $attribute_group_id
 * @property array<string, string>|null $labels
 * @property bool $is_unique
 * @property bool $is_localizable
 * @property bool $is_scopable
 * @property array<string, mixed>|null $settings
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AttributeGroupModel|null $group
 */
final class AttributeModel extends Model
{
    use DispatchesModelEvents;

    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    use HasLabels;
    use HasUlids;

    protected $table = 'keystone_attributes';

    protected $fillable = [
        'code',
        'type',
        'attribute_group_id',
        'labels',
        'is_unique',
        'is_localizable',
        'is_scopable',
        'settings',
        'sort_order',
    ];

    /**
     * @return BelongsTo<AttributeGroupModel, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroupModel::class, 'attribute_group_id');
    }

    /**
     * @return HasMany<AttributeOptionModel, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOptionModel::class, 'attribute_id')->orderBy('sort_order')->orderBy('code');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'labels' => 'array',
            'is_unique' => 'boolean',
            'is_localizable' => 'boolean',
            'is_scopable' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
    }
}
