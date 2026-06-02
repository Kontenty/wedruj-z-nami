# Implementation Prompt: RFC-003

**Title:** Filament CMS  
**ID:** RFC-003  
**Brief Description:** Install and configure Filament v4 at `/cms` as the editorial CMS, with a Filament-owned login page on the existing Laravel session stack, PRD-aligned resources for objects, object types, and news, role-based permissions (administrator/editor), automatic author assignment, media upload with attribution metadata, publication workflows, and dashboard widgets.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = *typ obiektu*; "news" = *aktualności*; technical news model/resource name = `Article`

---

You are implementing RFC-003 for the Kanon project. This RFC builds the editorial CMS that the team will use to manage all content.

This implementation must follow `.ai/PRD.md` and `.ai/tech-stack.md`. If there is any conflict, PRD wins.

## Prerequisites

- RFC-001 must be completed (models exist)
- RFC-002 must be completed (media library integrated into models)

## What to Build

1. **Install Filament v4** via Composer and panel installer
2. **Configure Filament panel** at `/cms` with Polish UI and a Filament-owned login page on the existing `web` guard/session
3. **Implement role-based authorization** for `administrator` and `editor` (delete only for administrator)
4. **Persist CMS roles and authorship** with a simple `users.role` field plus automatic `author_id` assignment on content
5. **Create SightseeingObjectResource** with PRD fields: core content, object type assignment, UNESCO, geometry (point/polygon), practical info, media gallery, attribution metadata, automatic author assignment, and draft/published status
6. **Create ObjectTypeResource** with editable taxonomy, hierarchical parent selection, loop prevention, and 3-level depth validation
7. **Create ArticleResource** for news with draft/published/archived status, featured flag, Markdown body, cover media, and automatic author assignment
8. **Build dashboard widgets** (counts by status + latest objects/news)
9. **Write Pest tests** for CMS auth, permissions, CRUD, authorship, and validation

## Key Files to Create/Modify

- `composer.json` — add `filament/filament`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/SightseeingObjectResource.php` (form, table, pages)
- `app/Filament/Resources/ObjectTypeResource.php`
- `app/Filament/Resources/ArticleResource.php`
- database migration(s) for `users.role`, `sightseeing_objects.author_id`, `articles.author_id`, and `articles.is_featured`
- `app/Policies/*` (or equivalent permission layer)
- `app/Filament/Widgets/StatsOverview.php`
- `app/Filament/Widgets/LatestObjects.php`
- `app/Filament/Widgets/LatestArticles.php`
- `tests/Feature/CmsTest.php`

## Critical Requirements

- CMS accessible at `/cms/login` with a Filament-provided login page on the existing `web` guard/session
- CMS interface in Polish language
- Roles and permissions enforced:
  - `administrator`: full permissions including delete
  - `editor`: create/edit but cannot delete
- Roles stored as a simple enum/string field on `users`
- SightseeingObject form:
  - images upload (multiple, reorderable, with main image semantics)
  - image attribution metadata (`author`, `source`) supported
  - coordinate inputs validated for point mode
  - polygon geometry input validated for polygon mode
  - `author_id` assigned automatically from the authenticated CMS user
  - status uses PRD workflow: `draft` / `published`
- SightseeingObject table: thumbnail from Spatie, filterable by voivodeship/object type/UNESCO/status
- ObjectType form: parent selection prevents loops, max 3 levels validated
- Article form:
  - Markdown body editor
  - cover image single upload
  - optional attribution metadata (`author`, `source`)
  - `author_id` assigned automatically from the authenticated CMS user
  - featured toggle available
  - status uses PRD workflow: `draft` / `published` / `archived`
- Delete actions require confirmation and are visible only to administrator
- Dashboard shows totals and status breakdowns plus latest records, including archived and featured news counts

## Do NOT

- Do not create public-facing routes or controllers
- Do not create Blade views or Svelte components for public pages
- Do not implement catalog map/list UI (handled in RFC-005)
- Do not implement public news pages (handled in RFC-004)
- Do not modify existing migrations beyond what is strictly needed to satisfy PRD-aligned CMS workflows; add forward-only migrations for role, author, and featured fields

## Reference

Read the full RFC at: `.ai/RFCs/RFC-003-Filament-CMS.md`
