<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\SightseeingObjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'title',
    'slug',
    'lead',
    'description',
    'locality',
    'is_unesco',
    'opening_hours',
    'ticket_prices',
    'accessibility',
    'website',
    'data_source',
    'source_updated_at',
    'latitude',
    'longitude',
    'geometry',
    'voivodeship_id',
    'status',
    'published',
    'published_at',
])]
class SightseeingObject extends Model
{
    /** @use HasFactory<SightseeingObjectFactory> */
    use HasFactory, HasSlug;

    protected $attributes = [
        'is_unesco' => false,
        'published' => false,
        'status' => 'draft',
    ];

    public function voivodeship(): BelongsTo
    {
        return $this->belongsTo(Voivodeship::class);
    }

    public function objectTypes(): BelongsToMany
    {
        return $this->belongsToMany(ObjectType::class, 'object_object_type');
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published', true);
    }

    #[Scope]
    protected function inVoivodeship(Builder $query, ?string $slug): void
    {
        if ($slug === null || $slug === '') {
            return;
        }

        $query->whereHas('voivodeship', fn (Builder $query) => $query->where('slug', $slug));
    }

    #[Scope]
    protected function inObjectType(Builder $query, ObjectType|int|null $objectType): void
    {
        $this->applyObjectTypeScope($query, $objectType);
    }

    #[Scope]
    protected function inCategory(Builder $query, ObjectType|int|null $objectType): void
    {
        $this->applyObjectTypeScope($query, $objectType);
    }

    #[Scope]
    protected function unesco(Builder $query, ?bool $enabled = true): void
    {
        if ($enabled !== true) {
            return;
        }

        $query->where('is_unesco', true);
    }

    #[Scope]
    protected function searchByTitle(Builder $query, ?string $search): void
    {
        if ($search === null || trim($search) === '') {
            return;
        }

        $query->where('title', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($search)).'%');
    }

    #[Scope]
    protected function nearby(Builder $query, float $latitude, float $longitude, float $radiusKm = 20, int $limit = 3): void
    {
        $point = sprintf('POINT(%F %F)', $longitude, $latitude);
        $origin = 'ST_GeomFromText(?, 4326)';
        $searchableGeometry = "CASE WHEN ST_GeometryType(geometry) IN ('POLYGON', 'ST_POLYGON', 'MULTIPOLYGON', 'ST_MULTIPOLYGON') THEN ST_Centroid(geometry) ELSE geometry END";
        $distanceExpression = "ST_Distance_Sphere({$searchableGeometry}, {$origin})";

        $query
            ->published()
            ->select('sightseeing_objects.*')
            ->selectRaw("{$distanceExpression} as distance_meters", [$point])
            ->whereRaw("{$distanceExpression} <= ?", [$point, $radiusKm * 1000])
            ->orderByRaw($distanceExpression, [$point])
            ->limit($limit);
    }

    protected function getSlugSourceColumn(): string
    {
        return 'title';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_unesco' => 'boolean',
            'published' => 'boolean',
            'published_at' => 'datetime',
            'source_updated_at' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    private function applyObjectTypeScope(Builder $query, ObjectType|int|null $objectType): void
    {
        if ($objectType === null) {
            return;
        }

        $objectType = $objectType instanceof ObjectType ? $objectType : ObjectType::query()->find($objectType);

        if (! $objectType instanceof ObjectType) {
            return;
        }

        $typeIds = $objectType->descendantIds()->prepend($objectType->getKey());

        $query->whereHas('objectTypes', fn (Builder $query) => $query->whereIn('object_types.id', $typeIds));
    }
}
