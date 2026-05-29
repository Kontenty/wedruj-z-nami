# Implementation Prompt: RFC-003

**Title:** Filament CMS  
**ID:** RFC-003  
**Brief Description:** Install and configure Filament v4 at `/cms` as the editorial CMS, with PRD-aligned resources for objects, object types, and news, role-based permissions (administrator/editor), media upload with attribution metadata, publication workflows, and dashboard widgets.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = *typ obiektu*; "news" = *aktualności*; technical news model/resource name = `Article`

---

You are implementing RFC-003 for the Kanon project. This RFC builds the editorial CMS that the team will use to manage all content.

This implementation must follow `.ai/PRD.md` and `.ai/tech-stack.md`. If there is any conflict, PRD wins.

## Prerequisites

- RFC-001 must be completed (models exist)
- RFC-002 must be completed (media library integrated into models)

## What to Build

1. **Install Filament v4** via Composer and panel installer
2. **Configure Filament panel** at `/cms` with Polish UI and Fortify-backed authentication
3. **Implement role-based authorization** for `administrator` and `editor` (delete only for administrator)
4. **Create SightseeingObjectResource** with PRD fields: core content, object type assignment, UNESCO, geometry (point/polygon), practical info, media gallery, attribution metadata, and draft/published status
5. **Create ObjectTypeResource** with editable taxonomy, hierarchical parent selection, loop prevention, and 3-level depth validation
6. **Create ArticleResource** for news with draft/published status, Markdown body, cover media, and optional attribution metadata
7. **Build dashboard widgets** (counts by status + latest objects/news)
8. **Write Pest tests** for CMS auth, permissions, CRUD, and validation

## Key Files to Create/Modify

- `composer.json` — add `filament/filament`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/SightseeingObjectResource.php` (form, table, pages)
- `app/Filament/Resources/ObjectTypeResource.php`
- `app/Filament/Resources/ArticleResource.php`
- `app/Policies/*` (or equivalent permission layer)
- `app/Filament/Widgets/StatsOverview.php`
- `app/Filament/Widgets/LatestObjects.php`
- `app/Filament/Widgets/LatestArticles.php`
- `tests/Feature/CmsTest.php`

## Critical Requirements

- CMS accessible at `/cms/login` with Fortify authentication
- CMS interface in Polish language
- Roles and permissions enforced:
  - `administrator`: full permissions including delete
  - `editor`: create/edit but cannot delete
- SightseeingObject form:
  - images upload (multiple, reorderable, with main image semantics)
  - image attribution metadata (`author`, `source`) supported
  - coordinate inputs validated for point mode
  - polygon geometry input validated for polygon mode
  - status uses PRD workflow: `draft` / `published`
- SightseeingObject table: thumbnail from Spatie, filterable by voivodeship/object type/UNESCO/status
- ObjectType form: parent selection prevents loops, max 3 levels validated
- Article form:
  - Markdown body editor
  - cover image single upload
  - optional attribution metadata (`author`, `source`)
  - status uses PRD workflow: `draft` / `published`
- Delete actions require confirmation and are visible only to administrator
- Dashboard shows totals and status breakdowns plus latest records

## Do NOT

- Do not create public-facing routes or controllers
- Do not create Blade views or Svelte components for public pages
- Do not implement catalog map/list UI (handled in RFC-005)
- Do not implement public news pages (handled in RFC-004)
- Do not modify existing migrations or models beyond what is strictly needed to satisfy PRD-aligned CMS workflows

## Reference

Read the full RFC at: `.ai/RFCs/RFC-003-Filament-CMS.md`
