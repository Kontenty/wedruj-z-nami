# RFC-006: Object Detail Page

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_

**Status:** Planned  
**Complexity:** Medium  
**Predecessors:** RFC-001, RFC-002, RFC-005  
**Successors:** RFC-007

---

## Summary

Build the object detail page at `/katalog/{slug}` using Inertia v3 with Svelte 5. This page displays all information about a single sightseeing object: title, description, image gallery with lightbox, location map (MapLibre), practical info, metadata, and a "back to catalog" link. The map is rendered via the same MapLibre/Svelte stack used in RFC-005. Print-friendly layout is prepared here with basic CSS; RFC-007 enhances it further.

Public UI copy must be Polish. Code identifiers, classes, methods, filenames, and tests remain English.

---

## Features / Requirements Addressed

- US-006: Object detail page (title, description, photos, practical info)
- US-009: Print object page (basic print CSS prepared; full enhancement in RFC-007)
- Object gallery (multiple images from Spatie Media Library)
- Practical info: opening hours, ticket prices, website
- Metadata: voivodeship, object types, UNESCO badge
- Back link to catalog

---

## Previous / Next

- **Builds on:** RFC-001 (SightseeingObject model with relationships), RFC-002 (media URLs), RFC-005 (catalog links here, MapLibre/Svelte stack)
- **Built by future:** RFC-007 (nearby objects section, enhanced print, object page enrichment)

---

## Technical Approach

### Route

```php
// routes/web.php
Route::get('/katalog/{object:slug}', [CatalogController::class, 'show'])->name('catalog.show');
```

This uses Laravel route model binding with a custom key. The SightseeingObject model must define:

```php
// On SightseeingObject model
public function getRouteKeyName(): string
{
    return 'slug';
}
```

### Controller

```php
use App\Http\Resources\ObjectDetailResource;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function show(SightseeingObject $object): Response
    {
        $object = SightseeingObject::query()
            ->published()
            ->whereKey($object)
            ->with(['voivodeship', 'objectTypes', 'media'])
            ->select('sightseeing_objects.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geojson')
            ->firstOrFail();

        return Inertia::render('Catalog/Show', [
            'object' => new ObjectDetailResource($object),
            'images' => $object->image_items,
            'geojson' => $object->geojson,
        ]);
    }
}
```

The `images` prop uses the existing `SightseeingObject::image_items` accessor from RFC-002. Frontend image objects therefore use `thumbnail_url` and `card_url`, not `thumb` and `card`.

### API Resource

```php
use Illuminate\Support\Str;

class ObjectDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lead' => $this->lead,
            'description' => Str::markdown((string) $this->description),
            'locality' => $this->locality,
            'is_unesco' => $this->is_unesco,
            'opening_hours' => $this->opening_hours,
            'ticket_prices' => $this->ticket_prices,
            'website' => $this->website,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'voivodeship' => [
                'name' => $this->voivodeship->name,
                'slug' => $this->voivodeship->slug,
            ],
            'objectTypes' => $this->objectTypes->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ]),
            'url' => route('catalog.show', $this->slug),
        ];
    }
}
```

### Svelte Component Architecture

```
resources/js/pages/Catalog/Show.svelte
├── ObjectMap.svelte          (MapLibre — point marker or polygon)
├── ImageGallery.svelte       (gallery grid + lightbox)
├── PracticalInfo.svelte      (opening hours, prices, website)
└── NearbyObjects.svelte      (placeholder, implemented in RFC-007)
```

### Show.svelte (Main Page Component)

```svelte
<script>
    import { Link } from '@inertiajs/svelte';
    import { index as catalogIndex } from '@/routes/catalog';
    import ObjectMap from './ObjectMap.svelte';
    import ImageGallery from './ImageGallery.svelte';
    import PracticalInfo from './PracticalInfo.svelte';
    import NearbyObjects from './NearbyObjects.svelte';

    let { object, images, geojson } = $props();

    function printPage() {
        window.print();
    }
</script>

<svelte:head>
    <title>{object.title} — Kanon</title>
</svelte:head>

<article class="max-w-4xl mx-auto px-4 py-8">
    <Link href={catalogIndex.url()} class="text-primary hover:underline mb-6 inline-block">
        ← Wróć do katalogu
    </Link>

    <h1 class="text-3xl font-bold mb-4">{object.title}</h1>

    <div class="flex flex-wrap gap-3 mb-6 text-sm text-gray-600">
        <span>{object.voivodeship.name}</span>
        {#if object.locality}
            <span>{object.locality}</span>
        {/if}
        {#each object.objectTypes as type}
            <span class="badge">{type.name}</span>
        {/each}
        {#if object.is_unesco}
            <span class="badge badge-unesco">UNESCO</span>
        {/if}
    </div>

    {#if object.lead}
        <p class="text-lg text-gray-600 mb-6">{object.lead}</p>
    {/if}

    {#if object.latitude && object.longitude}
        <ObjectMap
            lat={object.latitude}
            lng={object.longitude}
            {geojson}
            title={object.title}
        />
    {/if}

    <ImageGallery {images} title={object.title} />

    <div class="prose prose-lg max-w-none mb-8">
        {@html object.description}
    </div>

    <PracticalInfo
        openingHours={object.opening_hours}
        ticketPrices={object.ticket_prices}
        website={object.website}
    />

    <button onclick={printPage} class="btn btn-secondary mb-8 print-hidden">
        Drukuj
    </button>

    <NearbyObjects slug={object.slug} />
</article>
```

### ObjectMap.svelte

```svelte
<script>
    import maplibregl from 'maplibre-gl';
    import 'maplibre-gl/dist/maplibre-gl.css';

    let { lat, lng, geojson, title } = $props();
    let mapContainer;
    let map;

    $effect(() => {
        map = new maplibregl.Map({
            container: mapContainer,
            style: 'https://demotiles.maplibre.org/style.json',
            center: [lng, lat],
            zoom: 13,
        });

        map.on('load', () => {
            const geometry = typeof geojson === 'string' ? JSON.parse(geojson) : geojson;

            if (geometry && ['Polygon', 'MultiPolygon'].includes(geometry.type)) {
                map.addSource('object-area', { type: 'geojson', data: geometry });
                map.addLayer({
                    id: 'object-area-fill',
                    type: 'fill',
                    source: 'object-area',
                    paint: { 'fill-color': '#2563eb', 'fill-opacity': 0.2 },
                });
                map.addLayer({
                    id: 'object-area-outline',
                    type: 'line',
                    source: 'object-area',
                    paint: { 'line-color': '#2563eb', 'line-width': 2 },
                });
                const bounds = new maplibregl.LngLatBounds();
                const coordinates = geometry.type === 'Polygon' ? geometry.coordinates[0] : geometry.coordinates[0][0];
                coordinates.forEach(c => bounds.extend(c));
                map.fitBounds(bounds, { padding: 32 });
            } else {
                new maplibregl.Marker()
                    .setLngLat([lng, lat])
                    .setPopup(new maplibregl.Popup().setHTML(`<strong>${title}</strong>`))
                    .addTo(map);
            }
        });

        return () => map.remove();
    });
</script>

<div bind:this={mapContainer} class="w-full h-64 rounded-lg mb-8"></div>
```

### ImageGallery.svelte

```svelte
<script>
    let { images, title } = $props();
    let lightboxOpen = $state(false);
    let lightboxImage = $state('');

    function openLightbox(url) {
        lightboxImage = url;
        lightboxOpen = true;
    }

    function closeLightbox() {
        lightboxOpen = false;
    }

    function onKeydown(e) {
        if (e.key === 'Escape') closeLightbox();
    }
</script>

<svelte:window onkeydown={onKeydown} />

{#if images.length > 0}
    <figure class="mb-8">
        <img
            src={images[0].url}
            alt={title}
            class="w-full h-auto rounded-lg"
        />
    </figure>
{/if}

{#if images.length > 1}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-8">
        {#each images as image}
            <button
                type="button"
                onclick={() => openLightbox(image.url)}
                class="focus:ring-2 focus:ring-primary rounded"
            >
                <img
                    src={image.thumbnail_url}
                    alt={image.alt}
                    class="w-full h-32 object-cover rounded"
                    loading="lazy"
                />
            </button>
        {/each}
    </div>
{/if}

{#if lightboxOpen}
    <div
        class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
        onclick={closeLightbox}
        role="dialog"
        aria-modal="true"
    >
        <img
            src={lightboxImage}
            alt=""
            class="max-w-[90vw] max-h-[90vh] rounded"
        />
        <button
            class="absolute top-4 right-4 text-white text-2xl"
            aria-label="Zamknij"
        >✕</button>
    </div>
{/if}
```

### PracticalInfo.svelte

```svelte
<script>
    let { openingHours, ticketPrices, website } = $props();

    let hasData = $derived(openingHours || ticketPrices || website);
</script>

{#if hasData}
    <section class="bg-gray-50 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Informacje praktyczne</h2>
        <dl class="space-y-3">
            {#if openingHours}
                <div>
                    <dt class="font-medium text-gray-700">Godziny otwarcia</dt>
                    <dd class="text-gray-600">{openingHours}</dd>
                </div>
            {/if}
            {#if ticketPrices}
                <div>
                    <dt class="font-medium text-gray-700">Ceny biletów</dt>
                    <dd class="text-gray-600">{ticketPrices}</dd>
                </div>
            {/if}
            {#if website}
                <div>
                    <dt class="font-medium text-gray-700">Strona internetowa</dt>
                    <dd>
                        <a
                            href={website}
                            target="_blank"
                            rel="noopener"
                            class="text-primary hover:underline"
                        >
                            {website} ↗
                        </a>
                    </dd>
                </div>
            {/if}
        </dl>
    </section>
{/if}
```

### NearbyObjects.svelte (Stub)

```svelte
<script>
    let { slug } = $props();
</script>

<section id="nearby-objects" class="mt-12">
    <h2 class="text-xl font-semibold mb-4">Obiekty w pobliżu</h2>
    <p class="text-gray-500">Ładowanie…</p>
</section>
```

RFC-007 replaces this stub with the actual nearby objects implementation.

### Markdown Rendering

The `description` field is rendered server-side before being passed to Inertia, or rendered in Svelte using a Markdown library. The recommended approach is to render Markdown to HTML on the server:

```php
// In ObjectDetailResource
'description' => Str::markdown($this->description),
```

Then in Svelte, use `{@html object.description}` to render the pre-rendered HTML.

### Print Styles (Basic)

```css
@media print {
    header,
    footer,
    nav,
    .print-hidden,
    #nearby-objects {
        display: none !important;
    }
    article {
        max-width: 100%;
        padding: 0;
    }
    img {
        max-width: 100%;
        height: auto;
    }
    .prose {
        font-size: 12pt;
    }
}
```

RFC-007 enhances the print layout significantly.

---

## Data Flow

```
[RFC-001 Data] → CatalogController::show → Inertia::render → Catalog/Show.svelte
  │
  ├── object (ObjectDetailResource with voivodeship, objectTypes, published=true)
  ├── images (Spatie Media Library, 'images' collection)
  ├── geojson (ST_AsGeoJSON from geometry column)
  └── Svelte renders:
        ├── Title + metadata (voivodeship, locality, object types, UNESCO)
        ├── Lead (short description, if present)
        ├── ObjectMap (MapLibre — point marker or polygon)
        ├── ImageGallery (main image + thumbnail grid + lightbox)
        ├── Description (pre-rendered Markdown HTML)
        ├── PracticalInfo (opening hours, prices, website)
        ├── Print button
        └── NearbyObjects placeholder (RFC-007)
```

---

## UI/UX Specifications

### Page Layout

```
┌─────────────────────────────────────────────┐
│  Header (Inertia app shell)                 │
├─────────────────────────────────────────────┤
│                                             │
│  ← Wróć do katalogu                         │
│                                             │
│  Object Title                               │
│  [Voivodeship] [Locality] [Object Type]     │
│  [UNESCO]                                   │
│                                             │
│  Short description (lead) text here...      │
│                                             │
│  ┌─────────────────────────────────────┐    │
│  │         LOCATION MAP                │    │
│  │    (MapLibre, point or polygon)     │    │
│  └─────────────────────────────────────┘    │
│                                             │
│  ┌─────────────────────────────────────┐    │
│  │                                     │    │
│  │         MAIN IMAGE                  │    │
│  │                                     │    │
│  └─────────────────────────────────────┘    │
│                                             │
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐              │
│  │thumb│ │thumb│ │thumb│ │thumb│ (gallery)  │
│  └────┘ └────┘ └────┘ └────┘              │
│                                             │
│  DESCRIPTION                                │
│  Full description text rendered from        │
│  Markdown. Multiple paragraphs, rich        │
│  content.                                   │
│                                             │
│  ┌─────────────────────────────────────┐    │
│  │ PRACTICAL INFORMATION               │    │
│  │ Opening hours: ...                  │    │
│  │ Ticket prices: ...                  │    │
│  │ Strona internetowa: link ↗          │    │
│  └─────────────────────────────────────┘    │
│                                             │
│  [Drukuj]                                   │
│                                             │
│  OBIEKTY W POBLIŻU                          │
│  (ładowane dynamicznie - RFC-007)           │
│                                             │
├─────────────────────────────────────────────┤
│  Footer (Inertia app shell)                 │
└─────────────────────────────────────────────┘
```

---

## Implementation Plan

1. Extend `CatalogController` with `show()` and add the named `catalog.show` route.
2. Add slug route binding to `SightseeingObject` via `getRouteKeyName()`.
3. Create `ObjectDetailResource` for detail-page data; keep catalog-list `ObjectResource` unchanged.
4. Use the existing `image_items` accessor for gallery data.
5. Generate GeoJSON with `selectRaw('ST_AsGeoJSON(geometry) as geojson')`, matching RFC-005.
6. Create Svelte components under `resources/js/pages/Catalog/` using Svelte 5 runes.
7. Use Wayfinder route helpers for catalog navigation and regenerate routes after adding `catalog.show`.
8. Add global print styles to the existing CSS entrypoint.
9. Add Pest feature and resource tests, then run Pint and targeted tests.

---

## Acceptance Criteria

- [ ] `/katalog/{object:slug}` renders Inertia Svelte page for published objects
- [ ] `/katalog/{nonexistent}` returns 404
- [ ] `/katalog/{object:slug}` for unpublished objects returns 404
- [ ] Page displays: title, voivodeship, locality, object types, UNESCO badge
- [ ] Lead (short description) displayed when present
- [ ] Location map shows object marker (point) or polygon via MapLibre
- [ ] Location map centered on object coordinates
- [ ] Main image displayed prominently
- [ ] Gallery shows thumbnail grid when more than 1 image exists
- [ ] Gallery thumbnail click opens lightbox with full image
- [ ] Lightbox closes on Escape key and click outside
- [ ] Description rendered from Markdown to HTML
- [ ] Practical info section shown when data exists (opening hours, prices, website)
- [ ] Practical info section hidden when no data
- [ ] External website link opens in new tab with `rel="noopener"`
- [ ] Print button triggers `window.print()`
- [ ] Print CSS hides header, footer, nav, interactive elements
- [ ] Back link returns to catalog using Wayfinder-generated route helper
- [ ] Page responsive on mobile and desktop
- [ ] Nearby objects section container present (stub for now)
- [ ] Pest tests for CatalogController::show (published, unpublished, non-existent)
- [ ] Pest tests for ObjectDetailResource

---

## Testing Strategy

### Feature Tests (Pest)

- Object detail page returns Inertia response for published object
- Response contains correct props: object, images, geojson
- 404 for non-existent slug
- 404 for unpublished object slug
- ObjectDetailResource returns expected fields
- Images array contains existing `image_items` fields: url, thumbnail_url, card_url, alt, author, source, order
- GeoJSON returned for object with geometry
- Null geojson for object without geometry

---

## Error Handling

- Non-existent slug: 404 with standard Laravel 404 page
- Unpublished object: treated as non-existent (404)
- Missing images: empty array, Svelte shows no gallery
- Missing practical info: PracticalInfo component hides entirely
- Map initialization error: graceful degradation, page content still works
- Lightbox JS error: graceful fallback, image still viewable in gallery thumbnail

---

## Performance Considerations

- Eager-load `voivodeship` and `objectTypes` to prevent N+1
- Lazy-load gallery thumbnails (`loading="lazy"`)
- Use `thumbnail` conversion for gallery thumbnails
- Main image can use original or `card` conversion
- MapLibre loaded once in the Inertia app shell (shared with catalog page)
- Markdown rendered server-side (no client-side Markdown parser needed)

---

## Accessibility Considerations

- Semantic `<article>` element
- Heading hierarchy: h1 (title), h2 (sections)
- Image alt text uses object title
- Gallery thumbnails have descriptive alt text
- Lightbox traps focus when open, returns focus on close, and uses Polish close label (`Zamknij`)
- Print button has clear label
- External links have `target="_blank"` with `rel="noopener"` and visible indicator (↗)
- Map has text alternative (content is accessible without map rendering)
