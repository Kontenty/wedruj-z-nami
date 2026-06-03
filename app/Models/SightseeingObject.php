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
use InvalidArgumentException;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
    'author_id',
    'status',
    'published',
    'published_at',
])]
class SightseeingObject extends Model implements HasMedia
{
    /** @use HasFactory<SightseeingObjectFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $attributes = [
        'is_unesco' => false,
        'published' => false,
        'status' => 'draft',
    ];

    public function voivodeship(): BelongsTo
    {
        return $this->belongsTo(Voivodeship::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function objectTypes(): BelongsToMany
    {
        return $this->belongsToMany(ObjectType::class, 'object_object_type');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsFile(fn (File $file): bool => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)
                && $file->size <= 10 * 1024 * 1024)
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder-object.jpg')
            ->useFallbackUrl('/images/placeholder-object-thumb.jpg', 'thumbnail')
            ->useFallbackUrl('/images/placeholder-object-card.jpg', 'card');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 400, 300)
            ->nonQueued();

        $this->addMediaConversion('card')
            ->fit(Fit::Crop, 800, 600)
            ->nonQueued();
    }

    public function reorderImages(array $mediaIds): void
    {
        $currentMediaIds = $this->media()
            ->where('collection_name', 'images')
            ->orderBy('order_column')
            ->pluck('id')
            ->map(fn (int|string $mediaId): int => (int) $mediaId)
            ->all();

        $submittedMediaIds = array_map(fn (int|string $mediaId): int => (int) $mediaId, $mediaIds);
        $uniqueSubmittedMediaIds = array_values(array_unique($submittedMediaIds));
        $sortedCurrentMediaIds = $currentMediaIds;
        $sortedSubmittedMediaIds = $submittedMediaIds;

        sort($sortedCurrentMediaIds);
        sort($sortedSubmittedMediaIds);

        if ($submittedMediaIds !== $uniqueSubmittedMediaIds || $sortedSubmittedMediaIds !== $sortedCurrentMediaIds) {
            throw new InvalidArgumentException('Media IDs must contain every object image exactly once.');
        }

        Media::setNewOrder($submittedMediaIds);
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('images');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('images', 'thumbnail');
    }

    public function getCardUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('images', 'card');
    }

    /**
     * @return array<int, string>
     */
    public function getImageUrlsAttribute(): array
    {
        return $this->getMedia('images')
            ->map(fn (Media $media): string => $media->getUrl())
            ->values()
            ->all();
    }

    public function getHasImagesAttribute(): bool
    {
        return $this->hasMedia('images');
    }

    /**
     * @return array<int, array{id: int, url: string, thumbnail_url: string, card_url: string, alt: string, author: mixed, source: mixed, order: int|null}>
     */
    public function getImageItemsAttribute(): array
    {
        return $this->getMedia('images')
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumbnail_url' => $media->getUrl('thumbnail'),
                'card_url' => $media->getUrl('card'),
                'alt' => $media->getCustomProperty('alt', $this->title),
                'author' => $media->getCustomProperty('author'),
                'source' => $media->getCustomProperty('source'),
                'order' => $media->order_column,
            ])
            ->values()
            ->all();
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
    protected function nearby(Builder $query, self|int $origin, float $radiusKm = 20, int $limit = 3): void
    {
        $originId = $origin instanceof self ? $origin->getKey() : $origin;
        $table = $this->getTable();
        $qualifiedGeometryColumn = $this->qualifyColumn('geometry');

        $candidateGeometryExpression = "CASE WHEN ST_GeometryType({$qualifiedGeometryColumn}) IN ('POLYGON', 'ST_POLYGON', 'MULTIPOLYGON', 'ST_MULTIPOLYGON') THEN ST_Centroid({$qualifiedGeometryColumn}) ELSE {$qualifiedGeometryColumn} END";
        $originGeometryExpression = "(SELECT CASE WHEN ST_GeometryType(origin.geometry) IN ('POLYGON', 'ST_POLYGON', 'MULTIPOLYGON', 'ST_MULTIPOLYGON') THEN ST_Centroid(origin.geometry) ELSE origin.geometry END FROM {$table} AS origin WHERE origin.id = ? LIMIT 1)";
        $distanceExpression = "ST_Distance_Sphere({$candidateGeometryExpression}, {$originGeometryExpression})";

        $query
            ->published()
            ->where($this->qualifyColumn('id'), '!=', $originId)
            ->whereNotNull($this->qualifyColumn('geometry'))
            ->select("{$table}.*")
            ->selectRaw("{$distanceExpression} as distance_meters", [$originId])
            ->whereRaw("{$distanceExpression} <= ?", [$originId, $radiusKm * 1000])
            ->orderByRaw($distanceExpression, [$originId])
            ->limit($limit);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
