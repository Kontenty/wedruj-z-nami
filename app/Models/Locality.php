<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\LocalityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'voivodeship_id'])]
class Locality extends Model
{
    /** @use HasFactory<LocalityFactory> */
    use HasFactory, HasSlug;

    public function voivodeship(): BelongsTo
    {
        return $this->belongsTo(Voivodeship::class);
    }

    public function sightseeingObjects(): HasMany
    {
        return $this->hasMany(SightseeingObject::class);
    }
}
