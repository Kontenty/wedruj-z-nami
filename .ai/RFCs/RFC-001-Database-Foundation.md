# RFC-001: Database Foundation & Core Models

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** None  
**Successors:** RFC-002, RFC-003, RFC-004, RFC-005, RFC-006, RFC-007

---

## Summary

Establish the MariaDB 10.11 database, define all core domain models and migrations, implement Eloquent relationships, scopes, and query patterns, and seed the database with voivodeship data and sample content. This RFC creates the data backbone that every subsequent RFC builds upon.

---

## Features / Requirements Addressed

- Database switch from SQLite to MariaDB with native spatial features
- `obiekty` (objects) table with geometry support
- `kategorie` (object type taxonomy) hierarchical table (3 levels)
- `wojewodztwa` (voivodeships) reference table with seed data
- `artykuly` (news) table
- `object_category` pivot table
- Eloquent models with relationships, scopes, and query methods
- Database seeders with Polish voivodeships and sample data
- Title search scope for the catalog
- Pest tests for models, scopes, and relationships

---

## Previous / Next

- **Builds on:** Nothing (foundation RFC)
- **Built by future:** RFC-002 (media), RFC-003 (CMS), RFC-004 (public pages), RFC-005 (catalog), RFC-006 (detail), RFC-007 (advanced)

---

## Technical Approach

### Database Migration

Switch `.env` from `DB_CONNECTION=sqlite` to `DB_CONNECTION=mysql`. Create a new MariaDB database and configure the Laravel `mysql` connection for it.

### Table Schema

#### `wojewodztwa` (voivodeships)

```sql
CREATE TABLE wojewodztwa (
    id bigserial PRIMARY KEY,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    created_at timestamp,
    updated_at timestamp
);
```

Seed with 16 Polish voivodeships:
dolnośląskie, kujawsko-pomorskie, lubelskie, lubuskie, łódzkie, małopolskie, mazowieckie, opolskie, podkarpackie, podlaskie, pomorskie, śląskie, świętokrzyskie, warmińsko-mazurskie, wielkopolskie, zachodniopomorskie.

#### `kategorie` (categories — hierarchical, 3 levels)

```sql
CREATE TABLE kategorie (
    id bigserial PRIMARY KEY,
    parent_id bigint REFERENCES kategorie(id) ON DELETE SET NULL,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text,
    created_at timestamp,
    updated_at timestamp
);
```

Categories are self-referencing with `parent_id`. Max depth enforced at application level (3 levels).

#### `obiekty` (objects)

```sql
CREATE TABLE obiekty (
    id bigserial PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text NOT NULL,
    is_unesco boolean NOT NULL DEFAULT false,
    opening_hours text,
    ticket_prices text,
    website varchar(500),
    latitude numeric(10, 7),
    longitude numeric(10, 7),
    geometry geometry NOT NULL,
    wojewodztwo_id bigint NOT NULL REFERENCES wojewodztwa(id),
    published boolean NOT NULL DEFAULT false,
    published_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

CREATE INDEX idx_obiekty_wojewodztwo ON obiekty(wojewodztwo_id);
CREATE INDEX idx_obiekty_published ON obiekty(published) WHERE published = true;
CREATE INDEX idx_obiekty_slug ON obiekty(slug);
CREATE SPATIAL INDEX idx_obiekty_geometry ON obiekty (geometry);
CREATE INDEX idx_obiekty_published_at ON obiekty(published_at DESC NULLS LAST);
```

**Design decisions:**

- `latitude` / `longitude` stored as numeric for fast point queries and simple display.
- `geometry` column stores MariaDB geometry for spatial queries (nearby search, polygon areas). It can be a POINT for simple locations or a POLYGON for area objects (parks).
- `slug` is unique and URL-safe for SEO.
- `published_at` is used for "latest objects" ordering (nullable; falls back to `created_at`).
- `published` boolean controls public visibility.

#### `object_category` (pivot)

```sql
CREATE TABLE object_category (
    object_id bigint NOT NULL REFERENCES obiekty(id) ON DELETE CASCADE,
    category_id bigint NOT NULL REFERENCES kategorie(id) ON DELETE CASCADE,
    PRIMARY KEY (object_id, category_id)
);
```

Objects can belong to multiple categories.

#### `artykuly` (news)

```sql
CREATE TABLE artykuly (
    id bigserial PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    excerpt text,
    body text NOT NULL,
    published boolean NOT NULL DEFAULT false,
    published_at timestamp NOT NULL,
    created_at timestamp,
    updated_at timestamp
);

CREATE INDEX idx_artykuly_published ON artykuly(published) WHERE published = true;
CREATE INDEX idx_artykuly_published_at ON artykuly(published_at DESC);
CREATE INDEX idx_artykuly_slug ON artykuly(slug);
```

---

## Eloquent Models

### `App\Models\Wojewodztwo`

```php
class Wojewodztwo extends Model
{
    protected $table = 'wojewodztwa';

    public function obiekty(): HasMany
    {
        return $this->hasMany(Obiekt::class);
    }
}
```

### `App\Models\Kategoria` (object type)

```php
class Kategoria extends Model
{
    protected $table = 'kategorie';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function obiekty(): BelongsToMany
    {
        return $this->belongsToMany(Obiekt::class, 'object_category');
    }

    /** Recursively load children up to 3 levels */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /** Get breadcrumb path: [grandparent, parent, self] */
    public function breadcrumb(): array
    {
        $path = [$this];
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            array_unshift($path, $current);
        }
        return $path;
    }
}
```

### `App\Models\Obiekt`

```php
class Obiekt extends Model
{
    protected $table = 'obiekty';

    protected $casts = [
        'is_unesco' => 'boolean',
        'published' => 'boolean',
        'published_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function wojewodztwo(): BelongsTo
    {
        return $this->belongsTo(Wojewodztwo::class);
    }

    public function kategorie(): BelongsToMany
    {
        return $this->belongsToMany(Kategoria::class, 'object_category');
    }

    /** Get objects within a radius in km, ordered by distance */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $point = "ST_GeomFromText(CONCAT('POINT(', {$lng}, ' ', {$lat}, ')'), 4326)";
        $distance = "ST_Distance_Sphere(geometry, {$point})";

        return $query
            ->selectRaw("*, ({$distance}) as distance")
            ->whereRaw("ST_Distance_Sphere(geometry, {$point}) <= ?", [$radiusKm * 1000])
            ->orderByRaw("{$distance}");
    }

    /** Get objects within a radius, with automatic fallback from 5km to 20km */
    public function scopeNearbyWithFallback(Builder $query, float $lat, float $lng): Builder
    {
        $results = $query->clone()->nearby($lat, $lng, 5)->get();

        if ($results->isEmpty()) {
            return $query->nearby($lat, $lng, 20);
        }

        return $query->nearby($lat, $lng, 5);
    }

    /** Filter by voivodeship slug */
    public function scopeInVoivodeship(Builder $query, ?string $slug): Builder
    {
        if (!$slug) return $query;

        return $query->whereHas('wojewodztwo', fn ($q) => $q->where('slug', $slug));
    }

    /** Filter by object type ID (includes descendants) */
    public function scopeInCategory(Builder $query, ?int $categoryId): Builder
    {
        if (!$categoryId) return $query;

        $categoryIds = Kategoria::where('id', $categoryId)
            ->orWhere('parent_id', $categoryId)
            ->orWhereIn('parent_id', function ($q) use ($categoryId) {
                $q->select('id')->from('kategorie')->where('parent_id', $categoryId);
            })
            ->pluck('id');

        return $query->whereHas('kategorie', fn ($q) => $q->whereIn('kategorie.id', $categoryIds));
    }

    /** Filter UNESCO objects */
    public function scopeUnesco(Builder $query, ?bool $enabled): Builder
    {
        if (!$enabled) return $query;

        return $query->where('is_unesco', true);
    }

    /** Fuzzy search by title */
    public function scopeSearchByTitle(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;

        return $query->where('title', 'like', "%{$search}%");
    }

    /** Published objects only */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
```

### `App\Models\Artkul`

```php
class Artkul extends Model
{
    protected $table = 'artykuly';

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Published news, newest first */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)
            ->orderByDesc('published_at');
    }
}
```

### Sluggable Behavior

Implement a `HasSlug` trait or use `Str::slug()` in the model `boot()` method to auto-generate slugs from titles on creation. If a slug collides, append `-2`, `-3`, etc.

---

## Data Flow

```
[RFC-001 Foundation]
    │
    ├── wojewodztwa (seeded reference data)
    ├── kategorie (hierarchical, seeded with common types)
    ├── obiekty (created/edited via CMS in RFC-003)
    │     ├── belongs to wojewodztwo
    │     ├── belongs to many kategorie
    │     ├── has geometry (MariaDB spatial)
    │     └── published_at drives "latest" ordering
    ├── artykuly (news created/edited via CMS in RFC-003)
    │     └── published_at drives chronological ordering
    └── Title search scope available for catalog filtering
```

---

## Acceptance Criteria

- [ ] MariaDB database configured and connection working
- [ ] Spatial columns and indexes created successfully
- [ ] All migrations run successfully
- [ ] All 5 tables created with correct columns, indexes, and constraints
- [ ] `Wojewodztwo` seeded with all 16 Polish voivodeships
- [ ] Sample `Kategoria` hierarchy seeded (at least 3 parent object types with children)
- [ ] Sample `Obiekt` records seeded (at least 5, including at least 1 UNESCO, at least 1 with polygon geometry)
- [ ] Sample `Artkul` records seeded (at least 2)
- [ ] Eloquent relationships work: `obiekt.wojewodztwo`, `obiekt.kategorie`, `kategoria.children`, etc.
- [ ] `scopeNearby` returns objects sorted by distance within given radius
- [ ] `scopeNearbyWithFallback` returns 5km results when available, 20km when not
- [ ] `scopeInVoivodeship` filters correctly by voivodeship slug
- [ ] `scopeInCategory` filters by object type and its descendants
- [ ] `scopeSearchByTitle` performs case-insensitive partial matching
- [ ] `scopePublished` returns only published records
- [ ] Slug auto-generation works with collision handling
- [ ] Pest tests pass for all models, relationships, and scopes

---

## Testing Strategy

### Unit Tests (Pest)

- `ObiektTest`: test all scopes (nearby, search, filter, published), test relationships
- `KategoriaTest`: test hierarchical queries, breadcrumb, descendant resolution
- `ArtkulTest`: test published scope ordering
- `WojewodztwoTest`: test relationship to obiekty

### Feature Tests (Pest)

- Test migration can run and rollback cleanly
- Test seeder produces expected record counts
- Test scopeNearbyWithFallback fallback logic with factory data

### Factories

Create factories for all models:

- `ObiektFactory`: generate with random coordinates within Poland bounds, random wojewodztwo
- `KategoriaFactory`: support parent state
- `ArtkulFactory`: support published/unpublished states
- `WojewodztwoFactory`: (minimal, primarily seeded)

---

## Error Handling

- Migration failures: clear error message, log and halt
- Slug collisions: automatic suffix incrementing, no user intervention needed
- MariaDB geometry errors: validate geometry format before insert, return validation error
- Missing required fields: Laravel validation in model/requests

---

## Performance Considerations

- SPATIAL index on `geometry` column for fast spatial queries
- Partial indexes on `published` for filtered queries
- Composite index on `published_at DESC` for "latest" queries
- Ensure geometry columns are `NOT NULL` before creating spatial indexes

---

## Security Considerations

- No public-facing routes exposed in this RFC
- Database credentials managed via `.env`
- All queries use parameterized bindings (Eloquent handles this)

---

## Third-Party Dependencies

- `doctrine/dbal` may be needed for column modifications if using `Schema::getColumnType()`
- No new Composer packages required for this RFC (spatial support is server-side in MariaDB)
