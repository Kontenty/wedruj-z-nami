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

    public function wouldCreateParentLoop(?int $parentId): bool
    {
        if ($parentId === null || ! $this->exists) {
            return false;
        }

        if ($parentId === $this->getKey()) {
            return true;
        }

        $parent = self::query()->find($parentId);

        while ($parent instanceof self) {
            if ($parent->getKey() === $this->getKey()) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }

    public function wouldExceedMaximumDepth(?int $parentId, int $maximumDepth = 3): bool
    {
        $ancestorDepth = 0;
        $parent = $parentId === null ? null : self::query()->with('parent')->find($parentId);

        while ($parent instanceof self) {
            $ancestorDepth++;
            $parent = $parent->parent;
        }

        return ($ancestorDepth + 1 + $this->maximumDescendantDepth()) > $maximumDepth;
    }

    public function maximumDescendantDepth(): int
    {
        if (! $this->exists) {
            return 0;
        }

        $children = $this->children()->with('children')->get();

        if ($children->isEmpty()) {
            return 0;
        }

        return $children
            ->map(fn (self $child): int => 1 + $child->maximumDescendantDepth())
            ->max();
    }
}
