# RFC-001: Database Foundation & Core Models

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_; "news" = _aktualności_

**Status:** Implemented  
**Complexity:** Medium  
**Predecessors:** None  
**Successors:** RFC-002, RFC-003, RFC-004, RFC-005, RFC-006, RFC-007

---

## Summary

Establish the MariaDB 10.11 database, define all core domain models and migrations, implement Eloquent relationships, scopes, and query patterns, and seed the database with voivodeship data and sample content. This RFC creates the data backbone that every subsequent RFC builds upon.

---

## Features / Requirements Addressed

- Database switch from SQLite to MariaDB with native spatial features
- `sightseeing_objects` table with geometry support
- `object_types` hierarchical table (3 levels)
- `voivodeships` reference table with seed data
- `articles` (news) table
- `object_object_type` pivot table
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

#### `voivodeships`

```sql
CREATE TABLE voivodeships (
    id bigserial PRIMARY KEY,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    created_at timestamp,
    updated_at timestamp
);
```

Seed with 16 Polish voivodeships:
dolnośląskie, kujawsko-pomorskie, lubelskie, lubuskie, łódzkie, małopolskie, mazowieckie, opolskie, podkarpackie, podlaskie, pomorskie, śląskie, świętokrzyskie, warmińsko-mazurskie, wielkopolskie, zachodniopomorskie.

#### `object_types` (hierarchical, 3 levels)

```sql
CREATE TABLE object_types (
    id bigserial PRIMARY KEY,
    parent_id bigint REFERENCES object_types(id) ON DELETE SET NULL,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text,
    created_at timestamp,
    updated_at timestamp
);
```

Object types are self-referencing with `parent_id`. Max depth enforced at application level (3 levels).

#### `sightseeing_objects`

```sql
CREATE TABLE sightseeing_objects (
    id bigserial PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    lead text,
    description text NOT NULL,
    locality varchar(255),
    is_unesco boolean NOT NULL DEFAULT false,
    opening_hours text,
    ticket_prices text,
    website varchar(500),
    latitude numeric(10, 7),
    longitude numeric(10, 7),
    geometry geometry NOT NULL,
    voivodeship_id bigint NOT NULL REFERENCES voivodeships(id),
    published boolean NOT NULL DEFAULT false,
    published_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

CREATE INDEX idx_sightseeing_objects_voivodeship ON sightseeing_objects(voivodeship_id);
CREATE INDEX idx_sightseeing_objects_published ON sightseeing_objects(published) WHERE published = true;
CREATE INDEX idx_sightseeing_objects_slug ON sightseeing_objects(slug);
CREATE SPATIAL INDEX idx_sightseeing_objects_geometry ON sightseeing_objects (geometry);
CREATE INDEX idx_sightseeing_objects_published_at ON sightseeing_objects(published_at DESC NULLS LAST);
```

**Design decisions:**

- `latitude` / `longitude` stored as numeric for fast point queries and simple display.
- `geometry` column stores MariaDB geometry for spatial queries (nearby search, polygon areas). It can be a POINT for simple locations or a POLYGON for area objects (parks).
- `slug` is unique and URL-safe for SEO.
- `published_at` is used for "latest objects" ordering (nullable; falls back to `created_at`).
- `published` boolean controls public visibility.

#### `object_object_type` (pivot)

```sql
CREATE TABLE object_object_type (
    sightseeing_object_id bigint NOT NULL REFERENCES sightseeing_objects(id) ON DELETE CASCADE,
    object_type_id bigint NOT NULL REFERENCES object_types(id) ON DELETE CASCADE,
    PRIMARY KEY (sightseeing_object_id, object_type_id)
);
```

Sightseeing objects can belong to multiple object types.

#### `articles` (news)

```sql
CREATE TABLE articles (
    id bigserial PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    excerpt text,
    body text NOT NULL,
    published boolean NOT NULL DEFAULT false,
    published_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

CREATE INDEX idx_articles_published ON articles(published) WHERE published = true;
CREATE INDEX idx_articles_published_at ON articles(published_at DESC);
CREATE INDEX idx_articles_slug ON articles(slug);
```

---

## Eloquent Models

### `App\Models\Voivodeship`

```php
class Voivodeship extends Model
{
    protected $table = 'voivodeships';

    public function sightseeingObjects(): HasMany
    {
        return $this->hasMany(SightseeingObject::class);
    }
}
```

### `App\Models\ObjectType` (object type taxonomy)

```php
class ObjectType extends Model
{
    protected $table = 'object_types';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function sightseeingObjects(): BelongsToMany
    {
        return $this->belongsToMany(SightseeingObject::class, 'object_object_type');
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

### `App\Models\SightseeingObject`

```php
class SightseeingObject extends Model
{
    protected $table = 'sightseeing_objects';

    protected $casts = [
        'is_unesco' => 'boolean',
        'published' => 'boolean',
        'published_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function voivodeship(): BelongsTo
    {
        return $this->belongsTo(Voivodeship::class);
    }

    public function objectTypes(): BelongsToMany
    {
        return $this->belongsToMany(ObjectType::class, 'object_object_type');
    }

    /** Get objects within a radius in km, ordered by distance */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $point = sprintf('POINT(%f %f)', $lng, $lat);

        return $query
            ->selectRaw("*, ST_Distance_Sphere(geometry, ST_GeomFromText(?, 4326)) as distance", [$point])
            ->whereRaw("ST_Distance_Sphere(geometry, ST_GeomFromText(?, 4326)) <= ?", [$point, $radiusKm * 1000])
            ->orderByRaw("ST_Distance_Sphere(geometry, ST_GeomFromText(?, 4326))", [$point]);
    }

    /** Filter by voivodeship slug */
    public function scopeInVoivodeship(Builder $query, ?string $slug): Builder
    {
        if (!$slug) return $query;

        return $query->whereHas('voivodeship', fn ($q) => $q->where('slug', $slug));
    }

    /** Filter by object type ID (includes descendants) */
    public function scopeInObjectType(Builder $query, ?int $typeId): Builder
    {
        if (!$typeId) return $query;

        $typeIds = ObjectType::where('id', $typeId)
            ->orWhere('parent_id', $typeId)
            ->orWhereIn('parent_id', function ($q) use ($typeId) {
                $q->select('id')->from('object_types')->where('parent_id', $typeId);
            })
            ->pluck('id');

        return $query->whereHas('objectTypes', fn ($q) => $q->whereIn('object_types.id', $typeIds));
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

### `App\Models\Article`

```php
class Article extends Model
{
    protected $table = 'articles';

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
    ├── voivodeships (seeded reference data)
    ├── object_types (hierarchical, seeded with common types)
    ├── sightseeing_objects (created/edited via CMS in RFC-003)
    │     ├── belongs to voivodeship
    │     ├── belongs to many object_types
    │     ├── has geometry (MariaDB spatial)
    │     └── published_at drives "latest" ordering
    ├── articles (news created/edited via CMS in RFC-003)
    │     └── published_at drives chronological ordering
    └── Title search scope available for catalog filtering
```

---

## Acceptance Criteria

- [ ] MariaDB database configured and connection working
- [ ] Spatial columns and indexes created successfully
- [ ] All migrations run successfully
- [ ] All 5 tables created with correct columns, indexes, and constraints
- [ ] `Voivodeship` seeded with all 16 Polish voivodeships
- [ ] Sample `ObjectType` hierarchy seeded (at least 3 parent object types with children)
- [ ] Sample `SightseeingObject` records seeded (at least 5, including at least 1 UNESCO, at least 1 with polygon geometry)
- [ ] Sample `Article` records seeded (at least 2)
- [ ] Eloquent relationships work: `sightseeingObject.voivodeship`, `sightseeingObject.objectTypes`, `objectType.children`, etc.
- [ ] `scopeNearby` returns objects sorted by distance within given radius
- [ ] `scopeInVoivodeship` filters correctly by voivodeship slug
- [ ] `scopeInObjectType` filters by object type and its descendants
- [ ] `scopeSearchByTitle` performs case-insensitive partial matching
- [ ] `scopePublished` returns only published records
- [ ] Slug auto-generation works with collision handling
- [ ] Pest tests pass for all models, relationships, and scopes

---

## Testing Strategy

### Unit Tests (Pest)

- `SightseeingObjectTest`: test all scopes (nearby, search, filter, published), test relationships
- `ObjectTypeTest`: test hierarchical queries, breadcrumb, descendant resolution
- `ArticleTest`: test published scope ordering
- `VoivodeshipTest`: test relationship to sightseeingObjects

### Feature Tests (Pest)

- Test migration can run and rollback cleanly
- Test seeder produces expected record counts
- Test scopeNearby returns correct results within radius with factory data

### Factories

Create factories for all models:

- `SightseeingObjectFactory`: generate with random coordinates within Poland bounds, random voivodeship
- `ObjectTypeFactory`: support parent state
- `ArticleFactory`: support published/unpublished states
- `VoivodeshipFactory`: (minimal, primarily seeded)

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
