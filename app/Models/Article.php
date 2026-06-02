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
            ->useFallbackUrl('/images/placeholder-news-thumb.jpg', 'thumbnail');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 600, 400)
            ->nonQueued();
    }

    public function getCoverImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover');
    }

    public function getCoverThumbnailUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover', 'thumbnail');
    }

    public function getHasCoverImageAttribute(): bool
    {
        return $this->hasMedia('cover');
    }

    /**
     * @return array{id: int|null, url: string, thumbnail_url: string, alt: string, author: mixed, source: mixed}
     */
    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return [
            'id' => $media?->id,
            'url' => $this->cover_image_url,
            'thumbnail_url' => $this->cover_thumbnail_url,
            'alt' => $media?->getCustomProperty('alt', $this->title) ?? $this->title,
            'author' => $media?->getCustomProperty('author'),
            'source' => $media?->getCustomProperty('source'),
        ];
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
