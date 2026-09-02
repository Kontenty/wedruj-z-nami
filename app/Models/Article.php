<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['title', 'slug', 'excerpt', 'body', 'status', 'published', 'published_at', 'author_id', 'is_featured'])]
class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $attributes = [
        'published' => false,
        'status' => 'draft',
        'is_featured' => false,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->acceptsFile(fn (File $file): bool => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)
                && $file->size <= 5 * 1024 * 1024)
            ->useDisk('public')
            ->singleFile()
            ->useFallbackUrl('/images/placeholder-news.jpg')
            ->useFallbackUrl('/images/placeholder-news-thumb.jpg', 'thumbnail_webp');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! app()->environment('testing')) {
            $this->addMediaConversion('thumbnail_webp')
                ->fit(Fit::Crop, 600, 400)
                ->format('webp')
                ->quality(50)
                ->nonQueued();
        }
    }

    public function getCoverImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover');
    }

    public function getCoverThumbnailUrlAttribute(): string
    {
        return $this->cover_thumbnail_webp_url;
    }

    public function getCoverThumbnailWebpUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover', 'thumbnail_webp');
    }

    public function getHasCoverImageAttribute(): bool
    {
        return $this->hasMedia('cover');
    }

    /**
     * @return array{id: int|null, url: string, thumbnail_url: string, thumbnail_webp_url: string|null, alt: string, author: mixed, source: mixed}
     */
    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return [
            'id' => $media?->id,
            'url' => $this->cover_image_url,
            'thumbnail_url' => $this->cover_thumbnail_url,
            'thumbnail_webp_url' => $media ? $this->getConversionUrl($media, 'thumbnail_webp') : null,
            'alt' => $media?->getCustomProperty('alt', $this->title) ?? $this->title,
            'author' => $media?->getCustomProperty('author'),
            'source' => $media?->getCustomProperty('source'),
        ];
    }

    private function getConversionUrl(Media $media, string $conversionName): ?string
    {
        try {
            return $media->getUrl($conversionName);
        } catch (\Throwable) {
            return null;
        }
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published', true)->orderByDesc('published_at');
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
            'published' => 'boolean',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }
}
