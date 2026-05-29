# Implementation Prompt: RFC-002

**Title:** Media Management Layer  
**ID:** RFC-002  
**Brief Description:** Integrate Spatie Laravel Media Library for image uploads on objects and articles with collections, conversions, ordering, and accessors.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "article" = *artykuł*

---

You are implementing RFC-002 for the Kanon project. This RFC adds image management to the models created in RFC-001.

## Prerequisites

- RFC-001 must be completed (MariaDB database with all models and migrations)

## What to Build

1. **Install Spatie Laravel Media Library** via Composer
2. **Configure media collections** on Obiekt (multiple images) and Artkul (single cover)
3. **Define image conversions** (thumbnail, card sizes)
4. **Add accessor methods** for image URLs with fallback support
5. **Configure public storage symlink**
6. **Write Pest tests** for media operations

## Key Files to Create/Modify

- `composer.json` — add `spatie/laravel-medialibrary`
- `database/migrations/` — Spatie's media migration
- `app/Models/Obiekt.php` — add `HasMedia`, `InteractsWithMedia`, media collections, conversions, accessors
- `app/Models/Artkul.php` — add `HasMedia`, `InteractsWithMedia`, cover collection, conversions, accessors
- `tests/Feature/MediaTest.php`

## Critical Requirements

- Object images collection: accepts JPEG, PNG, WebP; max 10MB; multiple files; reorderable
- Article cover collection: accepts JPEG, PNG, WebP; max 5MB; single file only
- Thumbnail conversion for objects: 400×300
- Card conversion for objects: 800×600
- Thumbnail conversion for articles: 600×400
- `getPrimaryImageUrlAttribute` returns first image URL or fallback
- `getThumbnailUrlAttribute` returns thumbnail URL or fallback
- `getCoverImageUrlAttribute` returns cover URL or fallback
- `reorderImages(array $mediaIds)` method on Obiekt
- `storage:link` creates public symlink

## Do NOT

- Do not install Filament
- Do not create routes or controllers
- Do not create Blade views or Svelte components
- Do not modify migrations from RFC-001

## Reference

Read the full RFC at: `.ai/RFCs/RFC-002-Media-Management.md`
