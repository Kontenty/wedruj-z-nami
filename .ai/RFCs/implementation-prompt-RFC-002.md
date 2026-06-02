# Implementation Prompt: RFC-002

**Title:** Media Management Layer  
**ID:** RFC-002  
**Brief Description:** Integrate Spatie Laravel Media Library for image uploads on objects and news entries with collections, conversions, ordering, attribution metadata, and accessors.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "news" = *aktualności*; "article" = *artykuł* / technical model `Article`

---

You are implementing RFC-002 for the Kanon project. This RFC adds image management to the models created in RFC-001.

This implementation must follow `.ai/PRD.md`. If the RFC and PRD conflict, the PRD wins.

## Prerequisites

- RFC-001 must be completed (MariaDB database with all models and migrations)

## What to Build

1. **Install Spatie Laravel Media Library** via Composer
2. **Configure media collections** on SightseeingObject (multiple images) and Article/news entries (single cover)
3. **Define image conversions** (thumbnail, card sizes)
4. **Add accessor methods** for image URLs with fallback support
5. **Store optional image attribution metadata** (`author`, `source`, `alt`) using Spatie media custom properties
6. **Expose resource-ready image payloads** with fallback flags for downstream RFCs
7. **Configure public storage symlink**
8. **Write Pest tests** for media operations

## Key Files to Create/Modify

- `composer.json` — add `spatie/laravel-medialibrary`
- `database/migrations/` — Spatie's media migration
- `app/Models/SightseeingObject.php` — add `HasMedia`, `InteractsWithMedia`, media collections, conversions, accessors
- `app/Models/Article.php` — add `HasMedia`, `InteractsWithMedia`, cover collection, conversions, accessors
- `public/images/placeholder-object.jpg`
- `public/images/placeholder-object-thumb.jpg`
- `public/images/placeholder-object-card.jpg`
- `public/images/placeholder-news.jpg`
- `public/images/placeholder-news-thumb.jpg`
- `tests/Feature/MediaTest.php`

## Critical Requirements

- Object images collection: accepts JPEG, PNG, WebP; max 10MB; multiple files; reorderable
- Article cover collection: accepts JPEG, PNG, WebP; max 5MB; single file only
- Store media on the public disk used by the `storage:link` symlink; do not store public images on a private local disk
- Enforce MIME and size constraints with Spatie `acceptsFile()` callbacks using `File::$mimeType` and `File::$size`
- Do not use `maxFileSize()` on media collections; Spatie Media Library v11 does not expose that collection method
- Size values must be bytes (`10 * 1024 * 1024`, `5 * 1024 * 1024`), not kilobytes
- RFC-003 will add user-facing Polish CMS validation messages; RFC-002 is responsible for model-level rejection
- Preserve all existing RFC-001 model behavior while editing `SightseeingObject` and `Article`: traits, slugging, fillable attributes, relationships, scopes, casts, defaults, and query helpers must remain intact
- Store optional PRD attribution fields for every media item as custom properties: `author`, `source`, and `alt`; if absent, downstream UI should treat the image as PTTK-owned and use the object/article title as alt text
- Thumbnail conversion for objects: 400×300
- Card conversion for objects: 800×600
- Thumbnail conversion for articles: 600×400
- Conversion dimensions should be deterministic for card/list layout; use crop/fit behavior rather than unconstrained resizing
- URL accessors must use `getFirstMediaUrl(...)` with configured fallback URLs so empty collections return fallback URLs, not `null`
- `getPrimaryImageUrlAttribute` returns first image URL or fallback
- `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- `getCardUrlAttribute` returns card URL or fallback
- `getImageUrlsAttribute` returns array of all image URLs in the `images` collection
- `getCoverImageUrlAttribute` returns cover URL or fallback
- `getCoverThumbnailUrlAttribute` returns cover thumbnail URL or fallback
- `getHasImagesAttribute` returns whether object images contain real media, not just fallback placeholders
- `getHasCoverImageAttribute` returns whether an article has a real cover image, not just fallback placeholders
- `getImageItemsAttribute` returns an array of image objects with `id`, `url`, `thumbnail_url`, `card_url`, `alt`, `author`, `source`, and `order`
- `getCoverImageAttribute` returns a cover object with `id`, `url`, `thumbnail_url`, `alt`, `author`, and `source`
- `reorderImages(array $mediaIds)` method on SightseeingObject
- `reorderImages(array $mediaIds)` must validate the list contains exactly every current media ID from that SightseeingObject's `images` collection once, then use Spatie's `Media::setNewOrder($mediaIds)` / `order_column` ordering
- `reorderImages(array $mediaIds)` must reject partial lists, duplicate IDs, IDs from another model, and IDs from another collection
- `storage:link` creates public symlink
- Tests must verify attribution custom properties are saved and retrievable
- Tests must verify MIME type enforcement rejects non-image files
- Tests must verify file size limits are enforced (10 MB for objects, 5 MB for articles)
- Tests must verify fallback URLs and `has_*` flags separately so downstream UI can distinguish real media from placeholders
- Tests must verify `image_items` and `cover_image` payload shapes
- Tests must verify strict image reordering success and rejection cases
- Tests should use existing project Pest style (`test(...)`) and Laravel `Storage::fake('public')` / `UploadedFile::fake()` helpers
- Spatie image conversions require EXIF and GD PHP extensions in local, CI, and deployment environments

## Do NOT

- Do not install Filament
- Do not create routes or controllers
- Do not create Blade views or Svelte components
- Do not modify migrations from RFC-001

## Reference

Read the full RFC at: `.ai/RFCs/RFC-002-Media-Management.md`
