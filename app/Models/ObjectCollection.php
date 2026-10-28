<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ObjectCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description'])]
class ObjectCollection extends Model
{
    /** @use HasFactory<ObjectCollectionFactory> */
    use HasFactory, HasSlug;

    public function sightseeingObjects(): BelongsToMany
    {
        return $this->belongsToMany(SightseeingObject::class)
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position')
            ->orderBy('sightseeing_objects.id');
    }

    public function objectTypes(): BelongsToMany
    {
        return $this->belongsToMany(ObjectType::class, 'object_collection_object_type');
    }

    protected function getSlugSourceColumn(): string
    {
        return 'name';
    }
}
