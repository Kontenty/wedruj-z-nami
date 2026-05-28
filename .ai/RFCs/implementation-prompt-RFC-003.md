# Implementation Prompt: RFC-003

**Title:** Filament CMS  
**ID:** RFC-003  
**Brief Description:** Install and configure Filament v4 as the CMS admin panel with full CRUD resources for objects, categories, and articles, including media upload, validation, publication status, dashboard widgets, and Polish localization.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "category" = *kategoria*; "article" = *artykuł*; "news" = *aktualności*

---

You are implementing RFC-003 for the Kanon project. This RFC builds the editorial CMS that the team will use to manage all content.

## Prerequisites

- RFC-001 must be completed (models exist)
- RFC-002 must be completed (media library integrated into models)

## What to Build

1. **Install Filament v4** via Composer
2. **Configure Filament panel** at `/cms` with Polish language, authentication via Fortify
3. **Create ObiektResource** with full form (title, slug, description, coordinates, categories, UNESCO, images, practical info, publication status) and table (thumbnail, title, voivodeship, categories, status, actions)
4. **Create KategoriaResource** with CRUD, hierarchical parent selection, 3-level depth validation
5. **Create ArtkulResource** with form (title, slug, excerpt, Markdown body, cover image, publication date, status) and table
6. **Build dashboard widgets** (stats overview, latest objects, latest articles)
7. **Write Pest tests** for CMS CRUD, validation, and authentication

## Key Files to Create/Modify

- `composer.json` — add `filament/filament`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/ObiektResource.php` (form, table, pages)
- `app/Filament/Resources/KategoriaResource.php`
- `app/Filament/Resources/ArtkulResource.php`
- `app/Filament/Widgets/StatsOverview.php`
- `app/Filament/Widgets/LatestObjects.php`
- `app/Filament/Widgets/LatestArticles.php`
- `tests/Feature/CmsTest.php`

## Critical Requirements

- CMS accessible at `/cms/login` with Fortify authentication
- CMS interface in Polish language
- Obiekt form: images upload with min 1, reorderable, coordinate inputs validated
- Obiekt table: thumbnail from Spatie, filterable by voivodeship/category/UNESCO/status
- Kategoria form: parent selection prevents loops, max 3 levels validated
- Artkul form: Markdown body editor, cover image single upload, datetime picker for publication date
- Publish/unpublish toggle on all resources
- Delete with confirmation dialog
- Dashboard shows total counts and latest records

## Do NOT

- Do not create public-facing routes or controllers
- Do not create Blade views or Svelte components for public pages
- Do not modify existing migrations or models beyond what's needed for Filament integration

## Reference

Read the full RFC at: `.ai/RFCs/RFC-003-Filament-CMS.md`
