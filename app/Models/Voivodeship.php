<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\VoivodeshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Voivodeship extends Model
{
    /** @use HasFactory<VoivodeshipFactory> */
    use HasFactory, HasSlug;

    public function sightseeingObjects(): HasMany
    {
        return $this->hasMany(SightseeingObject::class);
    }
}
