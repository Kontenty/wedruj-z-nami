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
- Optional image attribution metadata: author and source when known; otherwise images are treated as PTTK-owned
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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SightseeingObject extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxFileSize(10 * 1024 * 1024)
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder-object.jpg')
            ->useFallbackUrl('/images/placeholder-object-thumb.jpg', 'thumbnail')
            ->useFallbackUrl('/images/placeholder-object-card.jpg', 'card');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(400)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('card')
            ->width(800)
            ->height(600)
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

    /** Get all image URLs */
    public function getImageUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(fn ($m) => $m->getUrl())->toArray();
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
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxFileSize(5 * 1024 * 1024)
            ->useDisk('public')
            ->singleFile() // Only one cover image
            ->useFallbackUrl('/images/placeholder-news.jpg')
            ->useFallbackUrl('/images/placeholder-news-thumb.jpg', 'thumbnail');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(600)
            ->height(400)
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
}
```

### Media Attribution Metadata

The PRD requires storing image author and source when known. Store those values on each Spatie media item using custom properties instead of adding separate tables in this RFC:

```php
$obiekt
    ->addMedia($uploadedFile)
    ->withCustomProperties([
        'author' => $author, // nullable string
        'source' => $source, // nullable string; omitted means assumed PTTK-owned
    ])
    ->toMediaCollection('images');
```

The same `author` and `source` custom properties apply to the `cover` collection on `Article`.

### Media Ordering

For objects with multiple images, use Spatie's `order_column` column. The first image in the collection is the "primary" image used for cards and as the main image on the detail page.

Provide a method to reorder media:

```php
// On SightseeingObject model
public function reorderImages(array $mediaIds): void
{
    $validMediaIds = $this->media()
        ->where('collection_name', 'images')
        ->whereIn('id', $mediaIds)
        ->pluck('id')
        ->all();

    if (count($validMediaIds) !== count($mediaIds)) {
        throw new InvalidArgumentException('All media IDs must belong to this object images collection.');
    }

    Media::setNewOrder($mediaIds);
}
```

### Storage Configuration

Use Laravel's public filesystem disk for beta by configuring the Spatie media disk to `public` or by calling `useDisk('public')` on the media collections.

Media will be stored at `storage/app/public/` and served via the `public` symlink:

```bash
php artisan storage:link
```

### Eloquent API Resource Integration

Add media URLs to model arrays via Accessors (shown above). These accessors are consumed by API Resources in subsequent RFCs.

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
- [ ] Media is stored on the public disk and served through the `storage:link` symlink
- [ ] Optional image `author` and `source` attribution metadata can be stored as media custom properties
- [ ] Thumbnail conversion generated for objects (400×300)
- [ ] Card conversion generated for objects (800×600)
- [ ] Thumbnail conversion generated for news entries (600×400)
- [ ] `getPrimaryImageUrlAttribute` returns first image URL or fallback
- [ ] `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- [ ] `getCoverImageUrlAttribute` returns cover URL or fallback
- [ ] Image reordering works via `reorderImages()` and uses Spatie `Media::setNewOrder()` / `order_column`
- [ ] `storage:link` creates public symlink
- [ ] Pest tests: attach image to SightseeingObject, verify URL, verify ordering, verify fallback, verify attribution metadata
- [ ] Pest tests: attach cover to Article, verify single-file constraint

---

## Testing Strategy

- Unit tests for accessor methods on both models
- Feature tests for attaching media, retrieving URLs, reordering
- Test fallback URLs work when no media is attached
- Test media custom properties store and return optional `author` and `source`
- Test MIME type validation rejects non-image files
- Test file size limits

---

## Error Handling

- Invalid file type: validation error with clear message in CMS form
- File too large: validation error with max size message
- Missing media: fallback URL returned via `useFallbackUrl()`
- Storage driver failure: Laravel exception logged, user-facing error in CMS

---

## Performance Considerations

- `nonQueued` conversions are acceptable for beta (synchronous, small images)
- Consider queue-based conversions if upload volume grows
- Thumbnail/card sizes reduce bandwidth for list views
- Lazy-load images below the fold in catalog cards

---

## Third-Party Dependencies

- `spatie/laravel-medialibrary` (new)
