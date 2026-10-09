<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationModel;

/**
 * The associations from this record to other products and product models.
 */
trait HasAssociations
{
    /**
     * @return MorphMany<AssociationModel, $this>
     */
    public function associations(): MorphMany
    {
        return $this->morphMany(AssociationModel::class, 'source')->orderBy('sort_order');
    }
}
