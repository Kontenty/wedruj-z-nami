# RFC-006: Object Detail Page

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "object type" = _typ obiektu_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001, RFC-002, RFC-005  
**Successors:** RFC-007

---

## Summary

Build the object detail page at `/katalog/{slug}` — a document-like, reference-oriented Blade page displaying all information about a single sightseeing object. Includes title, description, image gallery, practical info, metadata, and a "back to catalog" link. Print-friendly layout is prepared here with basic CSS; RFC-007 enhances it further.

---

## Features / Requirements Addressed

- US-008: Object detail page (title, description, photos, practical info)
- US-009: Print object page (basic print CSS prepared; full enhancement in RFC-007)
- Object gallery (multiple images from Spatie Media Library)
- Practical info: opening hours, ticket prices, website
- Metadata: voivodeship, categories, UNESCO badge
- Back link to catalog

---

## Previous / Next

- **Builds on:** RFC-001 (Obiekt model with relationships), RFC-002 (media URLs), RFC-005 (catalog links here)
- **Built by future:** RFC-007 (nearby objects section, enhanced print, object page enrichment)

---

## Technical Approach

### Route

```php
// routes/web.php
Route::get('/katalog/{slug}', [ObjectController::class, 'show'])->name('catalog.show');
```

### Controller

```php
class ObjectController extends Controller
{
    public function show(string $slug): View
    {
        $object = Obiekt::published()
            ->with(['wojewodztwo', 'kategorie'])
            ->where('slug', $slug)
            ->firstOrFail();

        $images = $object->getMedia('images')
            ->map(fn ($media) => [
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumbnail'),
                'alt' => $media->getCustomProperty('alt', $object->title),
            ]);

        return view('objects.show', compact('object', 'images'));
    }
}
```

### View: `resources/views/objects/show.blade.php`

```blade
@extends('layouts.public')

@section('title', $object->title . ' — Kanon')

@section('content')
<article class="max-w-4xl mx-auto px-4 py-8">

    {{-- Back link --}}
    <a href="{{ route('catalog.index') }}" class="text-primary hover:underline mb-6 inline-block">
        ← Back to Catalog
    </a>

    {{-- Title --}}
    <h1 class="text-3xl font-bold mb-4">{{ $object->title }}</h1>

    {{-- Metadata row --}}
    <div class="flex flex-wrap gap-3 mb-6 text-sm text-gray-600">
        <span>{{ $object->wojewodztwo->name }}</span>
        @foreach($object->kategorie as $kategoria)
            <span class="badge">{{ $kategoria->name }}</span>
        @endforeach
        @if($object->is_unesco)
            <span class="badge badge-unesco">UNESCO</span>
        @endif
    </div>

    {{-- Main image --}}
    @if($images->isNotEmpty())
        <figure class="mb-8">
            <img
                src="{{ $images->first()['url'] }}"
                alt="{{ $object->title }}"
                class="w-full h-auto rounded-lg"
            />
        </figure>
    @endif

    {{-- Gallery (if more than 1 image) --}}
    @if($images->count() > 1)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-8">
            @foreach($images as $image)
                <button
                    type="button"
                    onclick="openLightbox('{{ $image['url'] }}', '{{ $image['alt'] }}')"
                    class="focus:ring-2 focus:ring-primary rounded"
                >
                    <img
                        src="{{ $image['thumb'] }}"
                        alt="{{ $image['alt'] }}"
                        class="w-full h-32 object-cover rounded"
                        loading="lazy"
                    />
                </button>
            @endforeach
        </div>
    @endif

    {{-- Description --}}
    <div class="prose prose-lg max-w-none mb-8">
        {!! Str::markdown($object->description) !!}
    </div>

    {{-- Practical info --}}
    @hasanysection([$object->opening_hours, $object->ticket_prices, $object->website])
        <section class="bg-gray-50 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Practical Information</h2>
            <dl class="space-y-3">
                @if($object->opening_hours)
                    <div>
                        <dt class="font-medium text-gray-700">Opening Hours</dt>
                        <dd class="text-gray-600">{{ $object->opening_hours }}</dd>
                    </div>
                @endif
                @if($object->ticket_prices)
                    <div>
                        <dt class="font-medium text-gray-700">Ticket Prices</dt>
                        <dd class="text-gray-600">{{ $object->ticket_prices }}</dd>
                    </div>
                @endif
                @if($object->website)
                    <div>
                        <dt class="font-medium text-gray-700">Website</dt>
                        <dd>
                            <a href="{{ $object->website }}" target="_blank" rel="noopener"
                               class="text-primary hover:underline">
                                {{ $object->website }} ↗
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </section>
    @endhasanysection

    {{-- Print button --}}
    <button onclick="window.print()" class="btn btn-secondary mb-8">
        🖨️ Print Page
    </button>

    {{-- Nearby objects (placeholder for RFC-007) --}}
    <section id="nearby-objects" class="mt-12">
        <h2 class="text-xl font-semibold mb-4">Nearby Objects</h2>
        <p class="text-gray-500">Loading...</p>
    </section>

</article>

{{-- Lightbox (simple modal for gallery) --}}
<div id="lightbox" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="" class="max-w-[90vw] max-h-[90vh] rounded" />
    <button class="absolute top-4 right-4 text-white text-2xl" aria-label="Close">✕</button>
</div>
@endsection

@push('scripts')
<script>
    function openLightbox(url, alt) {
        const lb = document.getElementById('lightbox');
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox-img').alt = alt;
        lb.classList.remove('hidden');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });
</script>
@endpush
```

### Nearby Objects (Stub)

The "Nearby Objects" section is a placeholder in this RFC. RFC-007 implements it with MariaDB spatial queries and dynamic loading. The section container and heading are defined here so the page layout is complete.

### Route Model Binding Alternative

Instead of manual slug lookup, consider route model binding with a custom key:

```php
Route::get('/katalog/{object:slug}', [ObjectController::class, 'show'])->name('catalog.show');
```

This leverages Laravel's `getRouteKeyName()` on the Obiekt model:

```php
// On Obiekt model
public function getRouteKeyName(): string
{
    return 'slug';
}
```

---

## Data Flow

```
[RFC-001 Data] → ObjectController::show → objects/show.blade.php
  │
  ├── object (Obiekt with wojewodztwo, kategorie, published=true)
  ├── images (Spatie Media Library, 'images' collection)
  └── Page renders:
        ├── Title + metadata
        ├── Main image
        ├── Gallery (if > 1 image)
        ├── Description (Markdown rendered)
        ├── Practical info
        ├── Print button
        └── Nearby objects placeholder (RFC-007)
```

---

## UI/UX Specifications

### Page Layout

```
┌─────────────────────────────────────────────┐
│  Header                                     │
├─────────────────────────────────────────────┤
│                                             │
│  ← Back to Catalog                          │
│                                             │
│  Object Title                               │
│  [Voivodeship] [Category] [UNESCO]          │
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
│  │ Website: link ↗                     │    │
│  └─────────────────────────────────────┘    │
│                                             │
│  [🖨️ Print Page]                           │
│                                             │
│  NEARBY OBJECTS                             │
│  (loaded dynamically - RFC-007)             │
│                                             │
├─────────────────────────────────────────────┤
│  Footer                                     │
└─────────────────────────────────────────────┘
```

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

## Acceptance Criteria

- [ ] `/katalog/{slug}` renders object detail page for published objects
- [ ] `/katalog/{nonexistent}` returns 404
- [ ] `/katalog/{slug}` for unpublished objects returns 404
- [ ] Page displays: title, voivodeship, object types, UNESCO badge
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
- [ ] Back link returns to catalog
- [ ] Page responsive on mobile and desktop
- [ ] Nearby objects section container present (empty for now)
- [ ] Pest tests for ObjectController::show (published, unpublished, non-existent)
- [ ] Pest tests for image display logic

---

## Testing Strategy

### Feature Tests (Pest)

- Object detail page loads for published object
- Page contains expected content: title, description, voivodeship
- Page contains image when object has media
- 404 for non-existent slug
- 404 for unpublished object slug
- Practical info section renders when data present
- Practical info section hidden when empty
- Page contains nearby objects section placeholder

---

## Error Handling

- Non-existent slug: 404 with standard Laravel 404 page
- Unpublished object: treated as non-existent (404)
- Missing images: main image placeholder, no gallery shown
- Missing practical info: section hidden entirely
- Lightbox JS error: graceful fallback, image still viewable in gallery thumbnail

---

## Performance Considerations

- Eager-load `wojewodztwo` and `kategorie` to prevent N+1
- Lazy-load gallery thumbnails (`loading="lazy"`)
- Use `thumbnail` conversion for gallery thumbnails
- Main image can use original or `card` conversion
- Consider preloading the main image: `<link rel="preload" as="image" href="...">`

---

## Accessibility Considerations

- Semantic `<article>` element
- Heading hierarchy: h1 (title), h2 (sections)
- Image alt text uses object title
- Gallery thumbnails have descriptive alt text
- Lightbox trap focus when open, return on close
- Print button has clear label
- External links have `target="_blank"` with `rel="noopener"` and visible indicator (↗)
