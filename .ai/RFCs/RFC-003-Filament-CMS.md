# RFC-003: Filament CMS

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_; "news" = _aktualności_; technical news model/resource name = `Article`

**Status:** Proposed  
**Complexity:** High  
**Predecessors:** RFC-001, RFC-002  
**Successors:** RFC-004, RFC-005, RFC-006, RFC-007

---

## Summary

Install and configure Filament v4 as the editorial CMS under `/cms`, integrated with existing Fortify authentication. Implement resources for sightseeing objects, object types, and news, including role-based actions (administrator vs editor), media handling via Spatie Media Library, PRD-aligned publication workflows, and dashboard widgets for editorial operations.

This RFC implements only the CMS/editorial layer. Public pages remain in RFC-004 (Blade) and the interactive catalog remains in RFC-005 (Inertia + Svelte), consistent with the route-by-route architecture in `.ai/tech-stack.md`.

---

## Features / Requirements Addressed

- PRD 7.1: Objects management (create/edit, object type assignment, UNESCO flag, point/polygon support)
- PRD 7.2: News management (create/edit, draft/published)
- PRD 7.3: Users and roles (`administrator`, `editor`) with delete restricted to administrator
- PRD 7.4: Media upload with optional attribution (`author`, `source`) and main image semantics
- US-008: Add object
- US-009: Edit object
- US-010: Manage news entry
- US-011: Secure CMS access
- Polish-language CMS for non-technical editors

---

## Previous / Next

- **Builds on:** RFC-001 (core schema/models), RFC-002 (media layer)
- **Built by future:** Public pages and catalog RFCs consume CMS-authored content

---

## Technical Approach

### Package Installation

```bash
composer require filament/filament
php artisan filament:install --panels
```

Use Filament with the existing `User` model and Fortify authentication stack.

### Panel Configuration (`/cms`)

Create `app/Providers/Filament/AdminPanelProvider.php` with:

- Panel path: `/cms`
- Login enabled (`/cms/login`)
- Polish language (`pl`)
- Registration of resources and dashboard widgets
- Auth middleware stack compatible with Fortify session auth

### Localization

- Set Filament locale to Polish
- Publish Filament translation files when needed for overrides
- Keep CMS labels, helper text, and validation copy in Polish for editorial users

### Authorization Model (Roles and Actions)

Define and enforce two roles from PRD:

- `administrator`: full permissions, including delete operations
- `editor`: create and edit permissions, no delete permissions

Apply this consistently via policies and Filament resource/page/action visibility:

- Delete row actions hidden/forbidden for `editor`
- Delete bulk actions hidden/forbidden for `editor`
- Create/edit allowed for both roles

### Resources

#### `SightseeingObjectResource` (sightseeing objects)

**Required form capabilities (PRD-aligned):**

- Core identity: `title`, `slug`
- Content: short description/lead, full description
- Classification: object types (`objectTypes` relationship), UNESCO toggle
- Location:
    - `wojewodztwo_id` and optional locality field (if present in RFC-001 schema)
    - Geometry type selection (`point` or `polygon` behavior)
    - Point coordinates (`latitude`, `longitude`) for point-based objects
    - Polygon geometry input for area objects (GeoJSON/WKT strategy chosen in implementation)
- Practical information (optional): opening hours, ticket prices, accessibility
- Provenance: data source and last update/source metadata fields that exist in schema
- Publication workflow: status `draft|published` and publication timestamp
- Media:
    - multiple image upload (Spatie `images` collection)
    - minimum one image for publish-ready content
    - reorderable gallery
    - main image = first ordered image
    - optional attribution metadata (`author`, `source`) per image

**Table expectations:**

- Thumbnail (from Spatie conversions)
- Title
- Voivodeship
- Object types (badges)
- UNESCO indicator
- Publication status (`draft`/`published`)
- Updated/published timestamp
- Row actions respecting role permissions

**Filters:**

- By voivodeship
- By object type
- By UNESCO flag
- By publication status

#### `ObjectTypeResource` (object type taxonomy)

**Scope:** editable taxonomy used by objects.

**Requirements:**

- CRUD for object types
- Parent selection for hierarchical tree
- Validation to prevent loops/self-parenting
- Validation for maximum depth: 3 levels
- Helpful display of parent path/breadcrumb in table/forms

#### `ArticleResource` (news / aktualności)

**Form requirements:**

- `title`, `slug`
- `excerpt` (optional)
- `body` (Markdown editor)
- Publication status: `draft|published`
- `published_at` datetime
- Cover image upload (Spatie `cover` single-file collection)
- Optional attribution metadata (`author`, `source`) for cover image
- Author assignment field, if author relation/field exists in schema

**Table expectations:**

- Cover thumbnail
- Title
- Status badge (`draft`/`published`)
- Published at
- Row actions respecting role permissions

### Dashboard Widgets

Provide an editorial dashboard oriented to PRD operations:

- Stats overview:
    - total objects
    - objects by status (`draft`, `published`)
    - total news entries
    - news by status (`draft`, `published`)
- Latest objects widget (recently created/updated)
- Latest news widget (recently published/updated)

### Route Protection and Authentication

Filament routes live under `/cms/*` and require authentication.

- `/cms/login` available for CMS users
- Unauthenticated access to `/cms/*` redirects to login
- Authenticated users can access CMS according to role permissions

Fortify remains the authentication backend; this RFC does not introduce public-user auth.

---

## Data Flow

```
[Administrator/Editor] -> /cms/login -> [Fortify session auth] -> /cms
  |
  +-- /cms/sightseeing-objects (create/edit; delete only administrator)
  |      \-> Spatie Media Library (images + attribution metadata)
  |
  +-- /cms/object-types (taxonomy CRUD with max 3-level depth)
  |
  +-- /cms/articles (create/edit; delete only administrator)
         \-> Spatie Media Library (cover + attribution metadata)
```

---

## Acceptance Criteria

- [ ] Filament v4 installed and panel configured at `/cms`
- [ ] CMS login works at `/cms/login` using Fortify-authenticated users
- [ ] CMS interface and labels are Polish-first
- [ ] Roles enforced: `administrator` full access, `editor` create/edit without delete
- [ ] `SightseeingObjectResource` supports PRD object fields, point/polygon handling, UNESCO, object types, and draft/published status
- [ ] Object media gallery supports multiple files, ordering, main image semantics, and optional attribution metadata
- [ ] `ObjectTypeResource` supports hierarchical CRUD with loop prevention and max 3-level depth validation
- [ ] `ArticleResource` supports draft/published status, Markdown body, and cover image upload
- [ ] Delete actions require confirmation and are available only to administrator
- [ ] Dashboard widgets show counts and latest records for objects/news
- [ ] Pest feature tests cover auth, role permissions, CRUD, and key validation rules

---

## Testing Strategy

### Feature Tests (Pest)

- CMS login page renders
- Unauthenticated user cannot access `/cms/*`
- Authenticated administrator can access dashboard and all resource actions
- Authenticated editor can create/edit but cannot delete objects/news
- Object create/edit validates required fields, geometry mode, and coordinate ranges
- Object media validation covers file type/size and min-image requirement for publish-ready status
- Object type hierarchy validation blocks loops and depth > 3
- News create/edit validates status enum

### Authorization Tests

- Resource policies enforce role matrix consistently
- Delete endpoints/actions forbidden for editor role

---

## Error Handling

- Filament inline validation errors for all form rules
- Geometry validation errors provide actionable input guidance
- Slug collision handled via automatic unique suffixing
- Upload validation errors shown for unsupported MIME type and oversize files
- Clear errors when category depth/parent constraints are violated

---

## Performance Considerations

- Use Filament table pagination and searchable columns
- Eager-load relationships shown in tables to avoid N+1 queries
- Use image conversions for thumbnails in list views
- Keep synchronous conversions for MVP; consider queued conversions if editorial throughput grows

---

## Security Considerations

- All CMS routes require authenticated session
- Role-based authorization for mutating/destructive actions
- File upload validation blocks executable/script payloads
- CSRF/session protections are inherited from Laravel + Filament middleware

---

## Third-Party Dependencies

- `filament/filament` (new in this RFC)
- `spatie/laravel-medialibrary` (from RFC-002, consumed by CMS forms)
