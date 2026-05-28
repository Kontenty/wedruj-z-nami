# RFC-003: Filament CMS

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "category" = *kategoria*; "article" = *artykuł*; "news" = *aktualności*

**Status:** Proposed  
**Complexity:** High  
**Predecessors:** RFC-001, RFC-002  
**Successors:** — (enables content creation for all public pages)

---

## Summary

Install and configure Filament v4 as the CMS admin panel. Build resource pages for managing objects and articles with full CRUD, media upload, category assignment, publication status, form validation, and dashboard widgets. Protect CMS routes with authentication (Fortify already installed).

---

## Features / Requirements Addressed

- US-010: Add new object (CMS)
- US-011: Edit object (CMS)
- US-012: Delete object (CMS)
- US-013: Secure CMS access (login + password)
- US-019: Add article (CMS)
- US-020: Edit article (CMS)
- US-021: Delete article (CMS)
- CMS dashboard with overview widgets
- Media upload integration with Spatie Media Library
- Category management (hierarchical)
- Publication status toggle (publish/unpublish)
- Form validation for required fields
- Polish-language CMS interface

---

## Previous / Next

- **Builds on:** RFC-001 (models exist), RFC-002 (media library integrated)
- **Built by future:** All public page RFCs consume CMS-managed content

---

## Technical Approach

### Package Installation

```bash
composer require filament/filament
php artisan filament:install --panels
```

Configure Filament to use the existing `User` model for authentication.

### Filament Panel Configuration

Create `app/Providers/Filament/AdminPanelProvider.php`:

```php
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cms')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->navigationGroups([
                'Objects',
                'News',
                'Settings',
            ])
            ->widgets([
                // Dashboard widgets registered here
            ])
            ->pages([
                // Dashboard page
            ]);
    }
}
```

### Polish Localization

Configure Filament to use Polish:

```php
// config/filament.php or panel provider
->language('pl')
->darkMode(false)
```

Publish Polish language files:
```bash
php artisan vendor:publish --tag="filament-panels-translations"
```

### Resources

#### `ObiektResource`

**Table columns:**
- Thumbnail (from Spatie Media Library, `images` collection, `thumbnail` conversion)
- Title (searchable)
- Voivodeship (relationship column)
- Categories (relationship column, badges)
- Status (published/unpublished, color badge)
- Published at (date)
- Actions: Edit, View, Delete

**Form fields:**
- `title` (text input, required, max 255)
- `slug` (text input, auto-generated from title, editable, unique)
- `description` (rich text editor or textarea, required)
- `wojewodztwo_id` (select dropdown, required, relationship)
- `kategorie` (multi-select or checkbox list, relationship, optional)
- `is_unesco` (toggle, default false)
- `coordinates` (two number inputs: latitude, longitude, required)
  - Latitude: -90 to 90, 7 decimal places
  - Longitude: -180 to 180, 7 decimal places
- `geometry` (textarea for GeoJSON, optional, validated as valid GeoJSON geometry)
- `opening_hours` (textarea, optional)
- `ticket_prices` (textarea, optional)
- `website` (url input, optional, validated as valid URL)
- `images` (file upload, min 1, multiple, reorderable)
- `published` (toggle)
- `published_at` (datetime picker)

**Validation rules:**
```php
public static function getRules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'slug' => ['required', 'string', 'max:255', 'unique:obiekty,slug,' . $this->record?->id],
        'description' => ['required', 'string'],
        'wojewodztwo_id' => ['required', 'exists:wojewodztwa,id'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'website' => ['nullable', 'url', 'max:500'],
        'images' => ['required_without:record', 'array', 'min:1'],
        'images.*' => ['file', 'mimes:jpeg,png,webp', 'max:10240'],
    ];
}
```

**Filters:**
- By voivodeship (select)
- By category (select)
- Published / Unpublished
- UNESCO only

**Actions:**
- Create / Edit / Delete (with confirmation dialog)
- Publish / Unpublish (bulk and individual)

#### `KategoriaResource`

**Table columns:**
- Name (searchable)
- Parent (relationship)
- Children count
- Objects count

**Form fields:**
- `name` (text input, required)
- `slug` (auto-generated)
- `description` (textarea, optional)
- `parent_id` (select, optional, relationship to self, exclude current and descendants)

**Validation:**
- Max 3 levels deep (validate on save)

#### `ArtkulResource`

**Table columns:**
- Cover thumbnail (from Spatie, `cover` collection, `thumbnail` conversion)
- Title (searchable)
- Published at (date)
- Status (published/unpublished)
- Actions: Edit, Delete

**Form fields:**
- `title` (text input, required, max 255)
- `slug` (auto-generated)
- `excerpt` (textarea, optional, max 500)
- `body` (markdown editor, required)
- `published_at` (datetime picker, required)
- `cover` (file upload, optional, single, mimes: jpeg, png, webp, max 5MB)
- `published` (toggle)

### Dashboard Widgets

**Latest Objects Widget:**
- Show 5 most recently created objects
- Columns: thumbnail, title, voivodeship, published status

**Latest Articles Widget:**
- Show 5 most recently published articles
- Columns: title, published at, status

**Stats Overview Widget:**
- Total objects (published / unpublished)
- Total articles (published / unpublished)

### Filament Route Protection

Filament routes are already protected by its built-in authentication middleware. Since Fortify is installed, the existing `User` model and login flow work out of the box.

CMS routes will be under `/cms/*` and require authentication:

```php
// Filament handles this automatically
// /cms/login → Filament login page
// /cms/* → requires auth
```

---

## Data Flow

```
[Editor] → /cms/login → [Fortify Auth] → /cms/dashboard
  │
  ├── /cms/obiekty (list, create, edit, delete)
  │     └── Spatie Media Library → storage/app/public/
  │
  ├── /cms/kategorie (list, create, edit)
  │
  └── /cms/artykuly (list, create, edit, delete)
        └── Spatie Media Library → storage/app/public/
```

---

## Acceptance Criteria

- [ ] Filament v4 installed and configured
- [ ] CMS accessible at `/cms/login` with login form
- [ ] Fortify auth integration works (existing User model)
- [ ] CMS interface in Polish language
- [ ] `ObiektResource` with full CRUD, form validation, table with filters
- [ ] Image upload in Obiekt form (min 1, multiple, reorderable)
- [ ] Coordinates input (latitude/longitude) validated
- [ ] Category assignment (multi-select) in Obiekt form
- [ ] UNESCO toggle in Obiekt form
- [ ] Publication status toggle (publish/unpublish)
- [ ] `KategoriaResource` with CRUD, hierarchical parent selection, max 3-level validation
- [ ] `ArtkulResource` with full CRUD, Markdown body editor, cover image upload
- [ ] Dashboard with stats widgets and latest records
- [ ] Publish/unpublish actions work correctly
- [ ] Object deletion confirms before executing
- [ ] Article deletion confirms before executing
- [ ] Unpublished objects/articles do not appear in public views (enforced by scopes in RFC-001)
- [ ] Pest tests for CMS resource CRUD operations
- [ ] Pest tests for form validation (required fields, file types, coordinates)
- [ ] Pest tests for publish/unpublish actions

---

## Testing Strategy

### Feature Tests (Pest)

- Test CMS login page loads
- Test authenticated user can access dashboard
- Test unauthenticated user redirected to login
- Test ObiektResource: create with valid data, create with missing required fields (validation errors)
- Test ObiektResource: edit, update coordinates, toggle publish status
- Test ObiektResource: delete with confirmation
- Test ArtkulResource: create with valid data, validation errors
- Test KategoriaResource: create parent-child, prevent > 3 levels

### Auth Tests

- Test CMS login with valid credentials
- Test CMS login with invalid credentials
- Test CMS routes require authentication

---

## Error Handling

- Validation errors displayed inline in Filament forms (built-in)
- File upload errors: type mismatch, size exceeded → clear error message
- Slug collision: automatic suffix incrementing
- Invalid GeoJSON: validation error with descriptive message
- Category depth > 3: validation error on save

---

## Performance Considerations

- Lazy-load relationship columns in Filament tables
- Use Filament's built-in pagination for large datasets
- Thumbnail generation is synchronous (acceptable for beta admin usage)
- Consider queue for media conversions if CMS usage grows

---

## Security Considerations

- All CMS routes require authentication (Filament middleware)
- File upload validation prevents executable uploads
- CSRF protection built into Filament
- XSS prevention via Filament's escaping of user input
- Password stored via Fortify's bcrypt hashing

---

## Third-Party Dependencies

- `filament/filament` (new) — includes all panels, forms, tables, actions
