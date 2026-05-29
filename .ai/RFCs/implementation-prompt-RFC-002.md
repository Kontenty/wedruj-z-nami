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
5. **Store optional image attribution metadata** (`author`, `source`) using Spatie media custom properties
6. **Configure public storage symlink**
7. **Write Pest tests** for media operations

## Key Files to Create/Modify

- `composer.json` — add `spatie/laravel-medialibrary`
- `database/migrations/` — Spatie's media migration
- `app/Models/SightseeingObject.php` — add `HasMedia`, `InteractsWithMedia`, media collections, conversions, accessors
- `app/Models/Article.php` — add `HasMedia`, `InteractsWithMedia`, cover collection, conversions, accessors
- `tests/Feature/MediaTest.php`

## Critical Requirements

- Object images collection: accepts JPEG, PNG, WebP; max 10MB; multiple files; reorderable
- Article cover collection: accepts JPEG, PNG, WebP; max 5MB; single file only
- Store media on the public disk used by the `storage:link` symlink; do not store public images on a private local disk
- Spatie `maxFileSize()` values must be bytes (`10 * 1024 * 1024`, `5 * 1024 * 1024`), not kilobytes
- Store optional PRD attribution fields for every media item as custom properties: `author` and `source`; if absent, downstream UI should treat the image as PTTK-owned
- Thumbnail conversion for objects: 400×300
- Card conversion for objects: 800×600
- Thumbnail conversion for articles: 600×400
- URL accessors must use `getFirstMediaUrl(...)` with configured fallback URLs so empty collections return fallback URLs, not `null`
- `getPrimaryImageUrlAttribute` returns first image URL or fallback
- `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- `getCoverImageUrlAttribute` returns cover URL or fallback
- `reorderImages(array $mediaIds)` method on SightseeingObject
- `reorderImages(array $mediaIds)` must validate all IDs belong to that SightseeingObject's `images` collection, then use Spatie's `Media::setNewOrder($mediaIds)` / `order_column` ordering
- `storage:link` creates public symlink
- Tests must verify attribution custom properties are saved and retrievable

## Do NOT

- Do not install Filament
- Do not create routes or controllers
- Do not create Blade views or Svelte components
- Do not modify migrations from RFC-001

## Reference

Read the full RFC at: `.ai/RFCs/RFC-002-Media-Management.md`
