# RFC-002: Media Management Layer

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001  
**Successors:** RFC-003, RFC-005, RFC-006

---

## Summary

Integrate Spatie Laravel Media Library to handle image uploads for objects and news entries. Define media collections, configure upload constraints, store optional image attribution metadata, implement image ordering, and ensure first-image-is-primary semantics. This layer is consumed by the CMS (RFC-003), the interactive catalog (RFC-005), and the object detail page (RFC-006).

---

## Features / Requirements Addressed

- Spatie Media Library integration with SightseeingObject and Article models
- Media collections: `images` (for objects), `cover` (for news entries)
- Image upload constraints (max size, allowed MIME types)
- Media ordering: first image is primary/cover
- Optional image attribution metadata: author, source, and alt when known; otherwise images are treated as PTTK-owned
- Thumbnail generation for list views
- Public media URLs for frontend consumption
- Pest tests for media operations

---

## Previous / Next

- **Builds on:** RFC-001 (core models exist and can receive media)
- **Built by future:** RFC-003 (CMS uses media in forms), RFC-005 (catalog displays images), RFC-006 (detail page shows gallery)

---

## Technical Approach

### Package Installation

```bash
composer require spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

Add the media migration to `database/migrations/` (provided by Spatie).

### Media Collections

#### On `SightseeingObject` model

```php
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SightseeingObject extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)
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

    /** Get the primary (first) image URL */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images');
    }

    /** Get thumbnail URL for list/card views */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumbnail');
    }

    /** Get card URL for catalog views */
    public function getCardUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'card');
    }

    /** Get all image URLs */
    public function getImageUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(fn ($m) => $m->getUrl())->toArray();
    }

    public function getHasImagesAttribute(): bool
    {
        return $this->hasMedia('images');
    }

    public function getImageItemsAttribute(): array
    {
        return $this->getMedia('images')
            ->map(fn (Media $media) => [
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
}
```

#### On `Article` model

```php
class Article extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)
                && $file->size <= 5 * 1024 * 1024)
            ->useDisk('public')
            ->singleFile() // Only one cover image
            ->useFallbackUrl('/images/placeholder-news.jpg')
            ->useFallbackUrl('/images/placeholder-news-thumb.jpg', 'thumbnail');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 600, 400)
            ->nonQueued();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover');
    }

    public function getCoverThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'thumbnail');
    }

    public function getHasCoverImageAttribute(): bool
    {
        return $this->hasMedia('cover');
    }

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
}
```

`acceptsFile()` is used for model-level MIME and size enforcement because Spatie media collections do not expose a `maxFileSize()` collection method. RFC-003 should add friendly CMS validation messages before attempting to attach files, but RFC-002 must still reject invalid media at the model layer.

### Media Attribution Metadata

The PRD requires storing image author and source when known. Store those values on each Spatie media item using custom properties instead of adding separate tables in this RFC:

```php
$obiekt
    ->addMedia($uploadedFile)
    ->withCustomProperties([
        'author' => $author, // nullable string
        'source' => $source, // nullable string; omitted means assumed PTTK-owned
        'alt' => $alt,       // nullable string; falls back to object/article title in UI
    ])
    ->toMediaCollection('images');
```

The same `author`, `source`, and `alt` custom properties apply to the `cover` collection on `Article`.

### Media Ordering

For objects with multiple images, use Spatie's `order_column` column. The first image in the collection is the "primary" image used for cards and as the main image on the detail page.

Provide a method to reorder media. The method must receive exactly all current `images` media IDs once. Partial lists, duplicate IDs, IDs from another model, and IDs from another collection are invalid because they can break first-image-is-primary semantics.

```php
// On SightseeingObject model
public function reorderImages(array $mediaIds): void
{
    $currentMediaIds = $this->media()
        ->where('collection_name', 'images')
        ->orderBy('order_column')
        ->pluck('id')
        ->all();

    $submittedMediaIds = array_map('intval', $mediaIds);

    if (array_values(array_unique($submittedMediaIds)) !== $submittedMediaIds
        || collect($submittedMediaIds)->sort()->values()->all() !== collect($currentMediaIds)->sort()->values()->all()) {
        throw new InvalidArgumentException('Media IDs must contain every object image exactly once.');
    }

    Media::setNewOrder($submittedMediaIds);
}
```

### Storage Configuration

Use Laravel's public filesystem disk for beta by configuring the Spatie media disk to `public` or by calling `useDisk('public')` on the media collections.

Media will be stored at `storage/app/public/` and served via the `public` symlink:

```bash
php artisan storage:link
```

### Eloquent API Resource Integration

Expose media through accessors consumed by API Resources in subsequent RFCs. URL accessors return fallback URLs for empty collections. Boolean flags (`has_images`, `has_cover_image`) allow the UI to distinguish real media from placeholders. Full payload accessors (`image_items`, `cover_image`) expose URLs, conversions, attribution metadata, alt text, and ordering for the catalog, CMS, and object detail page.

---

## Data Flow

```
[RFC-003 CMS] ──upload──► Spatie Media Library ──store──► storage/app/public/
                                                            │
[RFC-005 Catalog] ◄──getUrl('card')─────────────────────────┘
[RFC-006 Detail]  ◄──getMedia('images')─────────────────────┘
```

---

## Acceptance Criteria

- [ ] Spatie Media Library installed and migration published
- [ ] `SightseeingObject` model implements `HasMedia` with `images` collection
- [ ] `Article` model implements `HasMedia` with `cover` collection (single file)
- [ ] Image upload limited to JPEG, PNG, WebP
- [ ] Object max file size: 10 MB; Article max file size: 5 MB
- [ ] File size limits use Spatie byte values, not kilobyte values
- [ ] File size limits are enforced with `acceptsFile()` callbacks, not a non-existent `maxFileSize()` collection method
- [ ] Media is stored on the public disk and served through the `storage:link` symlink
- [ ] Placeholder JPG files exist under `public/images/` for all configured fallback URLs
- [ ] Optional image `author`, `source`, and `alt` attribution metadata can be stored as media custom properties
- [ ] Thumbnail conversion generated for objects (400×300)
- [ ] Card conversion generated for objects (800×600)
- [ ] Thumbnail conversion generated for news entries (600×400)
- [ ] `getPrimaryImageUrlAttribute` returns first image URL or fallback
- [ ] `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- [ ] `getCardUrlAttribute` returns card URL or fallback
- [ ] `getImageUrlsAttribute` returns array of all image URLs
- [ ] `getCoverImageUrlAttribute` returns cover URL or fallback
- [ ] `getCoverThumbnailUrlAttribute` returns cover thumbnail URL or fallback
- [ ] `getHasImagesAttribute` and `getHasCoverImageAttribute` distinguish real media from fallback placeholders
- [ ] `getImageItemsAttribute` returns resource-ready image objects with conversion URLs, attribution metadata, alt text, and order
- [ ] `getCoverImageAttribute` returns a resource-ready cover object with fallback-aware URLs and attribution metadata
- [ ] Image reordering works via `reorderImages()` and uses Spatie `Media::setNewOrder()` / `order_column`
- [ ] `reorderImages()` rejects partial lists, duplicate IDs, foreign model IDs, and wrong-collection IDs
- [ ] `storage:link` creates public symlink
- [ ] Pest tests: attach image to SightseeingObject, verify URL, verify ordering, verify fallback, verify flags, verify payload shape, verify attribution metadata
- [ ] Pest tests: attach cover to Article, verify single-file constraint
- [ ] Pest tests: MIME type validation rejects non-image files
- [ ] Pest tests: file size limits enforced (10 MB objects, 5 MB articles)

---

## Testing Strategy

- Unit tests for accessor methods on both models
- Feature tests for attaching media, retrieving URLs, reordering
- Test fallback URLs work when no media is attached and boolean flags remain false
- Test media custom properties store and return optional `author`, `source`, and `alt`
- Test full object image and article cover payloads expose conversions and attribution metadata
- Test MIME type validation rejects non-image files
- Test file size limits
- Test strict reordering rejects partial lists, duplicate IDs, foreign model IDs, and wrong-collection IDs

---

## Error Handling

- Invalid file type: Spatie collection rejects the file at model level; RFC-003 adds clear CMS validation messages
- File too large: Spatie collection rejects the file at model level; RFC-003 adds clear CMS validation messages
- Missing media: fallback URL returned via `useFallbackUrl()`
- Storage driver failure: Laravel exception logged, user-facing error in CMS

---

## Performance Considerations

- `nonQueued` conversions are acceptable for beta (synchronous, small images)
- Consider queue-based conversions if upload volume grows
- Thumbnail/card sizes reduce bandwidth for list views
- Lazy-load images below the fold in catalog cards
- EXIF and GD PHP extensions are required for Spatie image conversions in all environments

---

## Third-Party Dependencies

- `spatie/laravel-medialibrary` (new)
