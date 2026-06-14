<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ObjectTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description'])]
class ObjectType extends Model
{
    /** @use HasFactory<ObjectTypeFactory> */
    use HasFactory, HasSlug;

    public function sightseeingObjects(): BelongsToMany
    {
        return $this->belongsToMany(SightseeingObject::class, 'object_object_type');
    }
}
