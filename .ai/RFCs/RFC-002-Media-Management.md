# RFC-002: Media Management Layer

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001  
**Successors:** RFC-003, RFC-005, RFC-006

---

## Summary

Integrate Spatie Laravel Media Library to handle image uploads for objects and news entries. Define media collections, configure upload constraints, implement image ordering, and ensure first-image-is-primary semantics. This layer is consumed by the CMS (RFC-003), the interactive catalog (RFC-005), and the object detail page (RFC-006).

---

## Features / Requirements Addressed

- Spatie Media Library integration with Obiekt and Artkul models
- Media collections: `images` (for objects), `cover` (for news entries)
- Image upload constraints (max size, allowed MIME types)
- Media ordering: first image is primary/cover
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

#### On `Obiekt` model

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Obiekt extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxFileSize(10240) // 10 MB
            ->useFallbackUrl('/images/placeholder-object.jpg');
    }

    public function registerMediaConversions(Media $media = null): void
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
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl() : null;
    }

    /** Get thumbnail URL for list/card views */
    public function getThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl('thumbnail') : null;
    }

    /** Get all image URLs */
    public function getImageUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(fn ($m) => $m->getUrl())->toArray();
    }
}
```

#### On `Artkul` model

```php
class Artkul extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxFileSize(5120) // 5 MB
            ->singleFile() // Only one cover image
            ->useFallbackUrl('/images/placeholder-news.jpg');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(600)
            ->height(400)
            ->nonQueued();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('cover');
        return $media ? $media->getUrl() : null;
    }

    public function getCoverThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('cover');
        return $media ? $media->getUrl('thumbnail') : null;
    }
}
```

### Media Ordering

For objects with multiple images, use Spatie's `ordering` column. The first image in the collection is the "primary" image used for cards and as the main image on the detail page.

Provide a method to reorder media:

```php
// On Obiekt model
public function reorderImages(array $mediaIds): void
{
    $this->reorderMedia('images', $mediaIds);
}
```

### Storage Configuration

Use the local filesystem for beta. Configure `FILESYSTEM_DISK=local` in `.env` (already set).

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
- [ ] `Obiekt` model implements `HasMedia` with `images` collection
- [ ] `Artkul` model implements `HasMedia` with `cover` collection (single file)
- [ ] Image upload limited to JPEG, PNG, WebP
- [ ] Object max file size: 10 MB; Article max file size: 5 MB
- [ ] Thumbnail conversion generated for objects (400×300)
- [ ] Card conversion generated for objects (800×600)
- [ ] Thumbnail conversion generated for news entries (600×400)
- [ ] `getPrimaryImageUrlAttribute` returns first image URL or fallback
- [ ] `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- [ ] `getCoverImageUrlAttribute` returns cover URL or fallback
- [ ] Image reordering works via `reorderImages()`
- [ ] `storage:link` creates public symlink
- [ ] Pest tests: attach image to Obiekt, verify URL, verify ordering, verify fallback
- [ ] Pest tests: attach cover to Artkul, verify single-file constraint

---

## Testing Strategy

- Unit tests for accessor methods on both models
- Feature tests for attaching media, retrieving URLs, reordering
- Test fallback URLs work when no media is attached
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
