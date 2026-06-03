# RFC-004: Public Pages (Homepage, News)

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

**Status:** Implemented  
**Complexity:** Medium  
**Predecessors:** RFC-001, RFC-002  
**Successors:** RFC-007

---

## Summary

Build all Blade-based public pages: the homepage with project description and latest objects, the news listing page, and individual news detail pages. These pages use Blade templates and Tailwind CSS, and serve as the informational layer of the application.

> **Grilling decisions applied:** Alpine.js via CDN for mobile menu, no dark mode, placeholder `#` links for object cards (RFC-006 dependency), no homepage caching, standard Laravel pagination, flat news listing with objects at bottom.

---

## Features / Requirements Addressed

- PRD 5.1: Homepage (project/mission intro, latest objects, latest news)
- PRD 5.4: News listing and news detail page
- US-007: Read news
- Public navigation (header, footer)
- Responsive design (mobile + desktop)
- WCAG accessibility (basic)
- MVP language: Polish-only interface copy
- No dark mode for public Blade pages

---

## Previous / Next

- **Builds on:** RFC-001 (models and data exist), RFC-002 (media URLs, thumbnails, and cover images exist)
- **Built by future:** RFC-007 (nearby objects on homepage enhancement)

---

## Technical Approach

### Routing

```php
// routes/web.php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;

Route::get('/', HomeController::class)->name('home');
Route::get('/aktualnosci', [NewsController::class, 'index'])->name('news.index');
Route::get('/aktualnosci/{slug}', [NewsController::class, 'show'])->name('news.show');
```

### Layout: `resources/views/layouts/public.blade.php`

Shared layout for all public pages:

```html
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>
            @yield('title', 'Kanon - Katalog obiektow krajoznawczych Polski')
        </title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white text-gray-900 antialiased">
        {{-- Public Header --}}
        <x-public-header />

        {{-- Main Content --}}
        <main>@yield('content')</main>

        {{-- Public Footer --}}
        <x-public-footer />

        {{-- Alpine.js via CDN for mobile menu --}}
        <script
            defer
            src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
        ></script>
    </body>
</html>
```

> **Note:** The existing `resources/views/app.blade.php` (Inertia root layout) remains untouched. It's only used by auth pages and dashboard.

### Homepage: `HomeController` + `resources/views/home.blade.php`

**Controller:**

```php
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestObjects = SightseeingObject::published()
            ->with(['voivodeship', 'objectTypes'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $latestNews = Article::published()
            ->limit(3)
            ->get();

        return view('home', compact('latestObjects', 'latestNews'));
    }
}
```

**Page sections:**

1. **Hero / Intro section**
    - Title: "Katalog obiektow krajoznawczych Polski"
    - Short description of the project purpose
    - Primary CTA: "Pokaz mape" → `/katalog`
    - Secondary CTA: "Przegladaj katalog" → `/katalog?view=list`

2. **For whom section** — who benefits from the catalog
    - Tourists, Teachers, Trip planners

3. **Latest objects section**
    - Grid of 4 object cards (image, title, voivodeship, link)
    - Each card links to `/katalog/{slug}`
4. **Latest news teaser**
    - Grid of up to 3 news cards (cover image, title, date, excerpt)
    - Each card links to `/aktualnosci/{slug}`
    - "Zobacz wszystkie" link to `/aktualnosci`

### News Listing: `NewsController` + `resources/views/news/index.blade.php`

**Controller:**

```php
class NewsController extends Controller
{
    public function index(): View
    {
        $news = Article::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $latestObjects = SightseeingObject::published()
            ->with('voivodeship')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.index', compact('news', 'latestObjects'));
    }

    public function show(string $slug): View
    {
        $newsItem = Article::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('news.show', compact('newsItem'));
    }
}
```

**Page structure:**

- Page title: "Aktualnosci"
- Optional short intro paragraph
- News card grid:
    - Cover image (if exists)
    - Title
    - Publication date
    - Excerpt (if exists)
    - Link to news detail
- Pagination
- Latest objects section at bottom

### News Detail: `resources/views/news/show.blade.php`

**Page structure:**

- Back link: "← Aktualnosci"
- Title
- Publication date
- Cover image (if exists)
- Markdown body rendered to HTML (using `Str::markdown()` or a Markdown parser)
- Contextual CTA at bottom: "Pokaz mape" / "Przegladaj katalog"

All labels and navigational copy should be in Polish.

### Markdown Rendering

For news bodies, use Laravel's `Str::markdown()` with XSS protection:

```blade
<div class="prose prose-lg max-w-none">
    {!! Str::markdown($newsItem->body, [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]) !!}
</div>
```

> **Security:** `html_input` strips raw HTML to prevent XSS. `allow_unsafe_links` prevents `javascript:` URIs. Use `html_input` with `strip` for editorial content.

Add Tailwind Typography (`@tailwindcss/typography`) for the `prose` classes.

### Public Header Component

Extract to `resources/views/components/public-header.blade.php`:

```blade
<header class="sticky top-0 z-50 bg-white border-b border-gray-200" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="text-xl font-bold">
                Kanon
            </a>
            <nav class="hidden md:flex space-x-8">
                <a href="/katalog" class="text-gray-600 hover:text-gray-900">Mapa</a>
                <a href="/katalog?view=list" class="text-gray-600 hover:text-gray-900">Katalog</a>
                <a href="{{ route('news.index') }}" class="text-gray-600 hover:text-gray-900">Aktualnosci</a>
            </nav>
            {{-- Mobile hamburger menu --}}
            <button @click="open = !open" class="md:hidden p-2 text-gray-600 hover:text-gray-900" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    {{-- Mobile nav dropdown --}}
    <div x-show="open" x-transition class="md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-3">
            <a href="/katalog" class="block text-gray-600 hover:text-gray-900">Mapa</a>
            <a href="/katalog?view=list" class="block text-gray-600 hover:text-gray-900">Katalog</a>
            <a href="{{ route('news.index') }}" class="block text-gray-600 hover:text-gray-900">Aktualnosci</a>
        </div>
    </div>
</header>
```

> **Decision:** Alpine.js via CDN powers the mobile menu toggle. No JavaScript files needed — all logic inline in the Blade component.

### Public Footer Component

Extract to `resources/views/components/public-footer.blade.php`:

```blade
<footer class="bg-gray-50 border-t border-gray-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <p>Kanon — Katalog obiektow krajoznawczych Polski</p>
    </div>
</footer>
```

### Tailwind Typography

Install the plugin:

```bash
npm install @tailwindcss/typography
```

Configure in CSS:

```css
@plugin "@tailwindcss/typography";
```

---

## Data Flow

```
[RFC-001 Data] → HomeController → home.blade.php
  ├── latestObjects (SightseeingObject::published, limit 4)
  └── latestNews (Article::published, limit 3)

[RFC-001 Data] → NewsController::index → news/index.blade.php
  ├── news (Article::published, paginated)
  └── latestObjects (SightseeingObject::published, limit 4)

[RFC-001 Data] → NewsController::show → news/show.blade.php
  └── newsItem (Article by slug, published only)
```

---

## UI/UX Specifications

### Homepage Layout

```
┌─────────────────────────────────────────┐
│  Header: Logo | Mapa | Katalog | Aktualnosci │
├─────────────────────────────────────────┤
│                                         │
│  HERO                                   │
│  "Katalog obiektow krajoznawczych       │
│   Polski"                               │
│  Short project description              │
│  [Pokaz mape] [Przegladaj katalog]      │
│                                         │
├─────────────────────────────────────────┤
│  FOR WHOM                               │
│  [Tourists] [Teachers] [Trip Planners]  │
│                                         │
├─────────────────────────────────────────┤
│  LATEST OBJECTS                         │
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐          │
│  │card│ │card│ │card│ │card│          │
│  └────┘ └────┘ └────┘ └────┘          │
│                                         │
├─────────────────────────────────────────┤
│  AKTUALNOSCI                            │
│  ┌────┐ ┌────┐ ┌────┐                  │
│  │new │ │new │ │new │                  │
│  └────┘ └────┘ └────┘                  │
│  [Zobacz wszystkie]                     │
│                                         │
├─────────────────────────────────────────┤
│  Footer                                 │
└─────────────────────────────────────────┘
```

### Responsive Behavior

- Mobile: single column, hamburger nav
- Tablet: 2-column card grids
- Desktop: 4-column latest objects, 3-column news cards
- Header collapses to hamburger on mobile
- Public copy remains Polish across breakpoints

### Object Card (Homepage)

```blade
<a href="#" class="group" title="Szczegóły obiektu będą dostępne wkrótce">
    <img src="{{ $object->thumbnail_url }}" alt="{{ $object->title }}" class="..." />
    <h3 class="...">{{ $object->title }}</h3>
    <p class="...">{{ $object->voivodeship->name }}</p>
</a>
```

> **Note:** Object card links use placeholder `#` until RFC-006 (Object Detail Page) creates the `catalog.show` route. Cards are non-functional placeholders pending that RFC.

### News Card

```blade
<a href="{{ route('news.show', $news->slug) }}" class="group">
    @if($news->cover_image_url)
        <img src="{{ $news->cover_thumbnail_url }}" alt="" class="..." />
    @endif
    <time class="...">{{ $news->published_at->format('d.m.Y') }}</time>
    <h3 class="...">{{ $news->title }}</h3>
    @if($news->excerpt)
        <p class="...">{{ $news->excerpt }}</p>
    @endif
</a>
```

---

## Acceptance Criteria

- [ ] Public layout with header and footer renders on all public pages
- [ ] Header navigation links work: home, map, catalog, news
- [ ] Header responsive: hamburger on mobile, full nav on desktop
- [ ] Homepage loads with project description and CTAs
- [ ] Homepage "Pokaz mape" links to `/katalog`
- [ ] Homepage "Przegladaj katalog" links to `/katalog?view=list`
- [ ] Homepage latest objects section shows up to 4 published objects
- [ ] Object cards show image, title, and voivodeship
- [ ] Homepage latest news section shows up to 3 published news entries
- [ ] News cards show cover image (if exists), title, date, excerpt
- [ ] `/aktualnosci` lists published news entries in reverse chronological order
- [ ] `/aktualnosci` shows pagination
- [ ] `/aktualnosci/{slug}` shows single news entry with Markdown body rendered
- [ ] News detail has back link to Aktualnosci
- [ ] News detail has contextual CTA to catalog/map
- [ ] All pages responsive on mobile and desktop
- [ ] Unpublished objects/news do not appear on public pages
- [ ] Pest tests for HomeController, NewsController

---

## Testing Strategy

### Feature Tests (Pest)

- Homepage loads and contains expected sections
- Homepage latest objects are limited to 4, ordered by published_at
- Homepage latest news are limited to 3
- `/aktualnosci` returns paginated published news entries
- `/aktualnosci` does not show unpublished news entries
- `/aktualnosci/{slug}` returns news entry with matching slug
- `/aktualnosci/{nonexistent}` returns 404

---

## Error Handling

- 404 page for non-existent news slugs
- Missing images: fallback URL from Spatie Media Library

---

## Performance Considerations

- Eager-load relationships (`voivodeship`, `objectTypes`) to prevent N+1 queries
- Paginate news listing (12 per page)
- Use `thumbnail` conversion for card images (smaller file sizes)
- **No caching for MVP** — simple queries (limit 4/3) don't warrant cache invalidation complexity

---

## Accessibility Considerations

- Semantic HTML: `<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`
- All images have meaningful `alt` attributes
- Focus states visible on all interactive elements
- Skip-to-content link
- Color contrast meets WCAG AA
- Keyboard navigable header and links
