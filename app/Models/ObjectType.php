<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ObjectTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['parent_id', 'name', 'slug', 'description'])]
class ObjectType extends Model
{
    /** @use HasFactory<ObjectTypeFactory> */
    use HasFactory, HasSlug;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function sightseeingObjects(): BelongsToMany
    {
        return $this->belongsToMany(SightseeingObject::class, 'object_object_type');
    }

    /**
     * @return array<int, self>
     */
    public function breadcrumb(): array
    {
        $path = [$this];
        $current = $this;

        while ($current->parent !== null) {
            $current = $current->parent;
            array_unshift($path, $current);
        }

        return $path;
    }

    /**
     * @return Collection<int, int>
     */
    public function descendantIds(int $maxDepth = 2): Collection
    {
        $ids = collect();
        $currentParentIds = collect([$this->getKey()]);

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            $childIds = self::query()
                ->whereIn('parent_id', $currentParentIds)
                ->pluck('id');

            if ($childIds->isEmpty()) {
                break;
            }

            $ids = $ids->merge($childIds);
            $currentParentIds = $childIds;
        }

        return $ids->values();
    }
}
