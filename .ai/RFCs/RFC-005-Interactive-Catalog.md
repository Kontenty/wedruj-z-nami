# RFC-005: Interactive Catalog (Inertia + Svelte)

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_

**Status:** Proposed  
**Complexity:** High  
**Predecessors:** RFC-001, RFC-002  
**Successors:** RFC-006, RFC-007

---

## Summary

Build the `/katalog` page — the most interactive part of the application — using Inertia v3 with Svelte 5. This page combines a MapLibre map, filter sidebar, partial-phrase search, active filter chips, map/list synchronization, and a results card grid. Desktop shows a split-panel layout (filters + map + results); mobile defaults to a map-first view with a map/list segmented control and bottom-sheet filters.

---

## Features / Requirements Addressed

- US-001: Browse catalog (list of objects)
- US-002: Filter by voivodeship
- US-003: Filter by object type (hierarchical, 3 levels)
- US-004: Filter by UNESCO status
- US-005: Search by name (partial phrase)
- US-007: Map view with markers and simplified polygons

---

## Previous / Next

- **Builds on:** RFC-001 (models, scopes, MariaDB spatial support), RFC-002 (media URLs on models)
- **Built by future:** RFC-006 (object detail page linked from catalog), RFC-007 (nearby objects, print)

---

## Technical Approach

### Route

```php
// routes/web.php
Route::get('/katalog', CatalogController::class)->name('catalog.index');
```

### Controller

```php
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = SightseeingObject::query()->published()
            ->with(['voivodeship', 'objectTypes'])
            ->select([
                'id', 'title', 'slug', 'description',
                'latitude', 'longitude', 'is_unesco',
                'wojewodztwo_id', 'published_at',
            ]);

        // Apply filters
        $query->searchByTitle($request->query('q'));
        $query->inVoivodeship($request->query('wojewodztwo'));
        $query->inObjectType($request->query('objectType') ? (int) $request->query('objectType') : null);
        $query->unesco($request->boolean('unesco'));

        // Paginated list/card results
        $objects = (clone $query)
            ->orderByDesc('published_at')
            ->paginate(24)
            ->withQueryString();

        // All filtered map results (expected max ~500), including GeoJSON from geometry column
        $mapObjects = (clone $query)
            ->selectRaw('ST_AsGeoJSON(geometry) as geojson')
            ->where(function ($query): void {
                $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orWhereNotNull('geometry');
            })
            ->orderByDesc('published_at')
            ->get();

        return Inertia::render('Catalog/Index', [
            'objects' => ObjectResource::collection($objects),
            'mapObjects' => ObjectResource::collection($mapObjects),
            'filters' => [
                'q' => $request->query('q'),
                'wojewodztwo' => $request->query('wojewodztwo'),
                'objectType' => $request->query('objectType'),
                'unesco' => $request->boolean('unesco'),
            ],
            'objectTypes' => ObjectTypeResource::collection(
                ObjectType::whereNull('parent_id')->with('childrenRecursive')->get()
            ),
            'voivodeships' => Voivodeship::all(['id', 'name', 'slug']),
        ]);
    }
}
```

### API Resources

**`ObjectResource`:**

```php
class ObjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => Str::limit($this->description, 150),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_unesco' => $this->is_unesco,
            'thumbnail_url' => $this->thumbnail_url,
            'primary_image_url' => $this->primary_image_url,
            'voivodeship' => [
                'name' => $this->voivodeship->name,
                'slug' => $this->voivodeship->slug,
            ],
            'objectTypes' => $this->objectTypes->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ]),
            'geojson' => $this->geojson,
            // No detail URL until RFC-006.
        ];
    }
}
```

**`ObjectTypeResource`:**

```php
class ObjectTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'children' => ObjectTypeResource::collection($this->whenLoaded('childrenRecursive')),
        ];
    }
}
```

### Title Search Implementation

Use the `scopeSearchByTitle` from RFC-001, which relies on MariaDB case-insensitive `LIKE` for beta search.

If typo tolerance becomes a requirement later, move the search layer into a dedicated follow-up RFC and add Scout or another search backend there.

### Svelte Component Architecture

```
resources/js/pages/Catalog/Index.svelte
├── FilterSidebar.svelte          (desktop left panel)
├── MobileFilterSheet.svelte      (mobile bottom sheet)
├── SearchBar.svelte              (search input)
├── ActiveFilterChips.svelte      (active filter pills)
├── CatalogMap.svelte             (MapLibre map)
│   └── MapPopup.svelte           (marker popup content)
├── ObjectGrid.svelte             (card grid results)
│   └── ObjectCard.svelte         (single object card)
├── MobileViewToggle.svelte       (Mapa | Lista segmented control)
└── EmptyState.svelte             (no results message)
```

### State Management

Use Svelte 5 runes (`$state`, `$derived`, `$effect`) for local state. URL query params are the source of truth for filters:

```svelte
<script>
    import { router } from '@inertiajs/svelte';

    import { index as catalogIndex } from '@/routes/catalog';

    let { objects, mapObjects, filters, objectTypes, voivodeships } = $props();

    let searchQuery = $state(filters.q || '');
    let selectedVoivodeship = $state(filters.wojewodztwo || '');
    let selectedObjectType = $state(filters.objectType || '');
    let isUnesco = $state(filters.unesco || false);
    let activeView = $state('map'); // 'map' | 'list'
    let selectedObject = $state(null);

    function applyFilters() {
        router.get(
            catalogIndex.url(),
            {
                q: searchQuery || undefined,
                wojewodztwo: selectedVoivodeship || undefined,
                objectType: selectedObjectType || undefined,
                unesco: isUnesco || undefined,
            },
            {
                preserveState: true,
                replace: true,
                only: ['objects', 'mapObjects', 'filters'],
            },
        );
    }

    // Debounced search
    let searchTimeout;
    function onSearchInput(value) {
        searchQuery = value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    }
</script>
```

### Map Component (MapLibre)

```svelte
<!-- CatalogMap.svelte -->
<script>
    import maplibregl from 'maplibre-gl';
    import 'maplibre-gl/dist/maplibre-gl.css';

    let { mapObjects, selectedObject, onObjectSelect } = $props();
    let map;
    let isReady = false;

    $effect(() => {
        if (!map) {
            map = new maplibregl.Map({
                container: 'catalog-map',
                style: 'https://tiles.openfreemap.org/styles/liberty',
                center: [19.1, 51.9],
                zoom: 6,
            });

            map.on('load', () => {
                isReady = true;
            });
        }

        if (isReady) {
            updateLayers(mapObjects);
        }
    });

    function updateLayers(objects) {
        const geojson = {
            type: 'FeatureCollection',
            features: objects.flatMap((obj) => {
                if (obj.geojson) {
                    return [
                        {
                            type: 'Feature',
                            geometry: JSON.parse(obj.geojson),
                            properties: { ...obj, kind: 'polygon' },
                        },
                    ];
                }

                if (obj.latitude && obj.longitude) {
                    return [
                        {
                            type: 'Feature',
                            geometry: {
                                type: 'Point',
                                coordinates: [obj.longitude, obj.latitude],
                            },
                            properties: { ...obj, kind: 'point' },
                        },
                    ];
                }

                return [];
            }),
        };

        if (map.getSource('objects')) {
            map.getSource('objects').setData(geojson);
            return;
        }

        map.addSource('objects', {
            type: 'geojson',
            data: geojson,
            cluster: true,
            clusterRadius: 48,
        });

        map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'objects',
            filter: ['has', 'point_count'],
            paint: {
                'circle-color': '#2563eb',
                'circle-radius': 18,
            },
        });

        map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'objects',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': ['get', 'point_count_abbreviated'],
                'text-size': 12,
            },
        });

        map.addLayer({
            id: 'unclustered-point',
            type: 'circle',
            source: 'objects',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': '#ef4444',
                'circle-radius': 7,
            },
        });
    }
</script>

<div id="catalog-map" class="w-full h-[60vh] md:h-[70vh] rounded-lg"></div>
```

Implementation note: split `mapObjects` into separate point and polygon GeoJSON sources. Enable clustering only on the point source. Render polygons with fill and outline layers, and attach popup click handlers to both point and polygon layers.

### Filter Sidebar (Desktop)

```svelte
<!-- FilterSidebar.svelte -->
<aside class="w-72 lg:w-80 shrink-0 overflow-y-auto">
    <SearchBar value={searchQuery} oninput={onSearchInput} />

    <!-- Voivodeship filter -->
    <fieldset>
        <legend>Województwo</legend>
        <select bind:value={selectedVoivodeship} onchange={applyFilters}>
            <option value="">Wszystkie</option>
            {#each voivodeships as v}
                <option value={v.slug}>{v.name}</option>
            {/each}
        </select>
    </fieldset>

    <!-- Object type filter (hierarchical) -->
    <fieldset>
        <legend>Typ obiektu</legend>
        {#each objectTypes as objectType}
            <ObjectTypeAccordion
                objectType={objectType}
                selected={selectedObjectType}
                onSelect={(id) => {
                    selectedObjectType = id;
                    applyFilters();
                }}
            />
        {/each}
    </fieldset>

    <!-- UNESCO toggle -->
    <label>
        <input
            type="checkbox"
            bind:checked={isUnesco}
            onchange={applyFilters}
        />
        Tylko UNESCO
    </label>

    <button onclick={clearFilters}>Wyczyść filtry</button>
</aside>
```

### Mobile View

```svelte
<!-- Mobile layout -->
<div class="md:hidden">
    <!-- Segmented control: Mapa | Lista -->
    <div class="flex border-b">
        <button
            class:active={activeView === 'map'}
            onclick={() => (activeView = 'map')}>Mapa</button
        >
        <button
            class:active={activeView === 'list'}
            onclick={() => (activeView = 'list')}>Lista</button
        >
        <button onclick={() => (showFilters = true)}>Filtry</button>
    </div>

    {#if activeView === 'map'}
        <CatalogMap {mapObjects} {onObjectSelect} />
    {:else}
        <ObjectGrid {objects} {onObjectSelect} />
    {/if}

    {#if showFilters}
        <MobileFilterSheet
            bind:show={showFilters}
            {selectedVoivodeship}
            {selectedObjectType}
            {isUnesco}
            {applyFilters}
            {clearFilters}
        />
    {/if}
</div>
```

### Active Filter Chips

```svelte
<!-- ActiveFilterChips.svelte -->
{#if hasActiveFilters}
    <div class="flex flex-wrap gap-2 mb-4">
        {#if searchQuery}
            <span class="chip">
                Szukaj: {searchQuery}
                <button
                    onclick={() => {
                        searchQuery = '';
                        applyFilters();
                    }}>×</button
                >
            </span>
        {/if}
        {#if selectedVoivodeship}
            <span class="chip">
                {voivodeships.find((v) => v.slug === selectedVoivodeship)?.name}
                <button
                    onclick={() => {
                        selectedVoivodeship = '';
                        applyFilters();
                    }}>×</button
                >
            </span>
        {/if}
        <!-- ... similar for object type and UNESCO -->
        <button onclick={clearFilters}>Wyczyść filtry</button>
    </div>
    <p>Liczba wyników: {objects.total}</p>
{/if}
```

### Object Card

```svelte
<!-- ObjectCard.svelte -->
<script>
    let { object, onHover, onLeave } = $props();
</script>

<article
    class="group block bg-white rounded-lg shadow-sm border"
    onmouseenter={() => onHover?.(object)}
    onmouseleave={() => onLeave?.(object)}
>
    <img
        src={object.thumbnail_url}
        alt={object.title}
        class="w-full h-48 object-cover rounded-t-lg"
        loading="lazy"
    />
    <div class="p-4">
        <h3 class="font-semibold text-lg group-hover:text-primary">
            {object.title}
        </h3>
        <p class="text-sm text-gray-500">{object.voivodeship.name}</p>
        {#if object.is_unesco}
            <span class="badge">UNESCO</span>
        {/if}
    </div>
</article>
```

### URL State Persistence

Filters are persisted as URL query parameters, enabling:

- Shareable catalog states
- Browser back/forward navigation
- Direct link to filtered view

Example: `/katalog?wojewodztwo=malopolskie&objectType=12&unesco=true`

### Map-List Synchronization

- Hovering an object card highlights the corresponding map marker (popup opens or marker changes style)
- Clicking a map marker scrolls the corresponding card into view (on desktop)
- On mobile: map and list are separate views, no sync needed

### Partial Reloads

Use Inertia partial reloads to fetch updated list objects, map objects, and canonical filters when filters change:

```svelte
router.get(catalogIndex.url(), params, {
    preserveState: true,
    replace: true,
    only: ['objects', 'mapObjects', 'filters'],
});
```

---

## Data Flow

```
[RFC-001 Data] → CatalogController → Inertia::render → Catalog/Index.svelte
  │
  ├── filters (URL query params)
  ├── objects (filtered, paginated list/card results)
  ├── mapObjects (all filtered objects with coordinates and/or GeoJSON)
  ├── objectTypes (hierarchical tree)
  └── voivodeships (reference list)
  │
  └── Svelte Components
        ├── FilterSidebar → applyFilters() → Wayfinder catalog route → partial reload
        ├── CatalogMap → MapLibre markers/polygons
        ├── ObjectGrid → ObjectCard list
        └── MapPopup → show object summary (no detail navigation until RFC-006)
```

---

## UI/UX Specifications

### Desktop Layout

```
┌────────────────────────────────────────────────────────┐
│  Header                                                │
├──────────┬─────────────────────────────────────────────┤
│ SIDEBAR  │  Szukaj              Liczba wyników: 42   │
│          │  [Active filter chips]                      │
│ Szukaj   │                                             │
│ [input]  │  ┌─────────────────────────────────────────┐│
│          │  │                                         ││
│ Województwo│ │              MAP                        ││
│ ▼ select │  │         (MapLibre tiles)                ││
│          │  │                                         ││
│ Typ obiektu │  └─────────────────────────────────────────┘│
│ ▸ Zabytki│                                            │
│ ▸ Parki  │  ┌────┐ ┌────┐ ┌────┐ ┌────┐             │
│ ▸ Muzea│  │card│ │card│ │card│ │card│             │
│          │  └────┘ └────┘ └────┘ └────┘             │
│ UNESCO   │  ┌────┐ ┌────┐ ┌────┐ ┌────┐             │
│ ☐ only   │  │card│ │card│ │card│ │card│             │
│          │  └────┘ └────┘ └────┘ └────┘             │
│ [Wyczyść]  │  [Pagination]                              │
└──────────┴─────────────────────────────────────────────┘
```

### Mobile Layout

```
┌────────────────────────┐
│ Header                 │
├────────────────────────┤
│ Szukaj   [Filtry] │
├────────────────────────┤
│ [Map] [Lista]           │
├────────────────────────┤
│                        │
│       MAP              │
│    (full width)        │
│                        │
│  ┌──────────────────┐  │
│  │ Selected object  │  │
│  │ preview popup    │  │
│  └──────────────────┘  │
└────────────────────────┘
```

### Map Popup

```html
<div class="w-48">
    <img
        src="{thumbnail}"
        alt="{title}"
        class="h-24 w-full rounded object-cover"
    />
    <h4 class="mt-2 font-semibold">{title}</h4>
    <p class="text-xs text-gray-500">{voivodeship}</p>

</div>
```

### Empty State

```html
<div class="py-12 text-center">
    <p class="text-lg text-gray-500">
        Nie znaleziono obiektów dla wybranych filtrów.
    </p>
    <button onclick="{clearFilters}" class="btn btn-primary mt-4">
        Wyczyść filtry
    </button>
</div>
```

---

## Acceptance Criteria

- [ ] `/katalog` route renders Inertia Svelte page
- [ ] Map displays all filtered published objects with coordinates and/or GeoJSON
- [ ] Simplified polygons rendered for objects with geometry data
- [ ] Map popup shows thumbnail, title, and voivodeship, with no detail link until RFC-006
- [ ] Filter sidebar visible on desktop
- [ ] Voivodeship filter works: select narrows results, URL updates
- [ ] Object type filter: hierarchical accordion with 3 levels, selection filters results
- [ ] UNESCO toggle filters correctly
- [ ] Search input: debounced partial phrase search by title, updates results
- [ ] Active filter chips display current filters with remove buttons
- [ ] "Wyczyść filtry" clears all filters and shows full result set
- [ ] Liczba wyników displayed and updates with filters
- [ ] Mobile: segmented control switches between Mapa and Lista views
- [ ] Mobile: default view is Map
- [ ] Mobile: `Filtry` button opens bottom sheet with all filters
- [ ] Filters persist in URL query params (shareable, browser nav works)
- [ ] Partial reloads: objects, mapObjects, and filters refresh on filter change
- [ ] Loading state: skeleton cards while data loads
- [ ] Empty state with clear message and clear-filters action
- [ ] Object card hover highlights map marker (desktop)
- [ ] Map marker click shows popup with object info
- [ ] Pagination works for large result sets
- [ ] Published objects only (unpublished excluded)
- [ ] Pest tests for CatalogController (filter combinations, search, pagination)
- [ ] Pest tests for ObjectResource and ObjectTypeResource

---

## Testing Strategy

### Backend Feature Tests (Pest)

- `/katalog` returns Inertia response with correct props
- Filter by voivodeship returns only matching objects
- Filter by object type returns objects in type and descendants
- Filter by UNESCO returns only UNESCO objects
- Search by title finds matching objects (case-insensitive)
- Combined filters work together
- Unpublished objects not returned
- Pagination works correctly
- Empty results for non-matching filters

### Frontend

No frontend/browser/component tests are required for RFC-005.

---

## Error Handling

- Map tile loading failure: MapLibre shows gray tiles, no crash
- Object data missing coordinates: skip marker, still show in card grid
- Geometry parsing error: skip polygon, still show point marker
- Empty search: show all published objects
- Inertia request failure: show error toast, retry available
- Network error: show error state in map/card grid

---

## Performance Considerations

- Debounce search input (300ms)
- Lazy-load card images (`loading="lazy"`)
- Use `only: ['objects', 'mapObjects', 'filters']` partial reloads to avoid re-fetching objectTypes/voivodeships
- Limit map markers if volume grows (use MapLibre clustering)
- Use `thumbnail` conversion for card images (400×300, much smaller than originals)
- Stable map height to prevent layout shift
- Paginate results (24 per page)

---

## Accessibility Considerations

- Filter controls accessible by keyboard
- Object type accordion supports keyboard expand/collapse
- Search input has clear label
- Liczba wyników announced via aria-live region
- Map has text alternative (the results list serves as alternative content)
- Focus management: preserve focus after filter changes
- Map does not trap keyboard focus
- Touch targets minimum 44×44px

---

## Third-Party Dependencies

- `maplibre-gl` (npm, new) — map library
- Do not add `@types/maplibre-gl` unless the project conventions or TypeScript compiler require it
