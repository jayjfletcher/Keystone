<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * The shape a Scout engine indexes a product in.
 *
 * Not the Product model: Scout's trait would tie every Keystone install to
 * Scout. This stand-in reads the same table and carries the prepared
 * document, and is only loaded when the `scout` search engine is chosen.
 *
 * @property string $id
 */
final class SearchableProductModel extends Model
{
    use Searchable;

    public $incrementing = false;

    protected $table = 'keystone_products';

    protected $keyType = 'string';

    /**
     * @var array<string, mixed>
     */
    private array $document = [];

    /**
     * @param  array<string, mixed>  $document
     */
    public static function fromDocument(array $document): self
    {
        $product = new self;
        $product->setAttribute('id', $document['id'] ?? null);
        $product->document = $document;

        return $product;
    }

    public function searchableAs(): string
    {
        return (string) config('keystone.search.scout.index', 'keystone_products');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return $this->document;
    }
}
