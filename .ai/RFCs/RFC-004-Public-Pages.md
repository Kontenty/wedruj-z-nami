# RFC-004: Public Pages (Homepage, News)

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001  
**Successors:** RFC-007

---

## Summary

Build all Blade-based public pages: the homepage with project description and latest objects, the news listing page, and individual news detail pages. These pages use Blade templates and Tailwind CSS, and serve as the informational layer of the application.

---

## Features / Requirements Addressed

- US-015: Homepage with project description
- US-016: Latest objects on homepage
- US-017: News listing
- US-018: News detail page
- Public navigation (header, footer)
- Responsive design (mobile + desktop)
- WCAG accessibility (basic)

---

## Previous / Next

- **Builds on:** RFC-001 (models and data exist)
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
            @yield('title', 'Kanon - Sightseeing Objects Catalog of Poland')
        </title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white text-gray-900 antialiased">
        {{-- Public Header --}}
        <header>
            <a href="{{ route('home') }}">Kanon</a>
            <nav>
                <a href="{{ route('home') }}">Homepage</a>
                <a href="/katalog">Map</a>
                <a href="/katalog?view=list">Catalog</a>
                <a href="{{ route('news.index') }}">News</a>
            </nav>
        </header>

        {{-- Main Content --}}
        <main>@yield('content')</main>

        {{-- Public Footer --}}
        <footer>
            <div>
                <p>Kanon — Sightseeing Objects Catalog of Poland</p>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
```

### Homepage: `HomeController` + `resources/views/home.blade.php`

**Controller:**

```php
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestObjects = Obiekt::published()
            ->with(['wojewodztwo', 'kategorie'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $latestNews = Artkul::published()
            ->limit(3)
            ->get();

        return view('home', compact('latestObjects', 'latestNews'));
    }
}
```

**Page sections:**

1. **Hero / Intro section**
    - Title: "Sightseeing Objects Catalog of Poland"
    - Short description of the project purpose
    - Primary CTA: "Show Map" → `/katalog`
    - Secondary CTA: "Browse Catalog" → `/katalog?view=list`

2. **For whom section** — who benefits from the catalog
    - Tourists, Teachers, Trip planners

3. **Latest objects section**
    - Grid of 4 object cards (image, title, voivodeship, link)
    - Each card links to `/katalog/{slug}`
    4. **Latest news teaser**
    - Grid of up to 3 news cards (cover image, title, date, excerpt)
    - Each card links to `/aktualnosci/{slug}`
    - "View All" link to `/aktualnosci`

### News Listing: `NewsController` + `resources/views/news/index.blade.php`

**Controller:**

```php
class NewsController extends Controller
{
    public function index(): View
    {
        $news = Artkul::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $latestObjects = Obiekt::published()
            ->with('wojewodztwo')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.index', compact('news', 'latestObjects'));
    }

    public function show(string $slug): View
    {
        $newsItem = Artkul::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('news.show', compact('newsItem'));
    }
}
```

**Page structure:**

- Page title: "News"
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

- Back link: "← News"
- Title
- Publication date
- Cover image (if exists)
- Markdown body rendered to HTML (using `Str::markdown()` or a Markdown parser)
- Contextual CTA at bottom: "Show Map" / "Browse Catalog"

### Markdown Rendering

For news bodies, use Laravel's `Str::markdown()`:

```blade
<div class="prose prose-lg max-w-none">
    {!! Str::markdown($newsItem->body) !!}
</div>
```

Add Tailwind Typography (`@tailwindcss/typography`) for the `prose` classes.

### Public Header Component

Extract to `resources/views/components/public-header.blade.php`:

```blade
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="text-xl font-bold">
                Kanon
            </a>
            <nav class="hidden md:flex space-x-8">
                <a href="/katalog" class="...">Map</a>
                <a href="/katalog?view=list" class="...">Catalog</a>
                <a href="{{ route('news.index') }}" class="...">News</a>
            </nav>
            {{-- Mobile hamburger menu --}}
            <button class="md:hidden" aria-label="Menu">☰</button>
        </div>
    </div>
    {{-- Mobile nav dropdown --}}
</header>
```

### Public Footer Component

Extract to `resources/views/components/public-footer.blade.php`:

```blade
<footer class="bg-gray-50 border-t border-gray-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <p>Kanon — Sightseeing Objects Catalog of Poland</p>
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
  ├── latestObjects (Obiekt::published, limit 4)
  └── latestNews (Artkul::published, limit 3)

[RFC-001 Data] → NewsController::index → news/index.blade.php
  ├── news (Artkul::published, paginated)
  └── latestObjects (Obiekt::published, limit 4)

[RFC-001 Data] → NewsController::show → news/show.blade.php
  └── newsItem (Artkul by slug, published only)
```

---

## UI/UX Specifications

### Homepage Layout

```
┌─────────────────────────────────────────┐
│  Header: Logo | Map | Catalog | News    │
├─────────────────────────────────────────┤
│                                         │
│  HERO                                   │
│  "Sightseeing Objects Catalog           │
│   of Poland"                            │
│  Short project description              │
│  [Show Map] [Browse Catalog]            │
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
│  NEWS                                   │
│  ┌────┐ ┌────┐ ┌────┐                  │
│  │new │ │new │ │new │                  │
│  └────┘ └────┘ └────┘                  │
│  [View All]                             │
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

### Object Card (Homepage)

```blade
<a href="{{ route('catalog.show', $object->slug) }}" class="group">
    <img src="{{ $object->thumbnail_url }}" alt="{{ $object->title }}" class="..." />
    <h3 class="...">{{ $object->title }}</h3>
    <p class="...">{{ $object->wojewodztwo->name }}</p>
</a>
```

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
- [ ] Homepage "Show Map" links to `/katalog`
- [ ] Homepage "Browse Catalog" links to `/katalog?view=list`
- [ ] Homepage latest objects section shows up to 4 published objects
- [ ] Object cards show image, title, and voivodeship
- [ ] Homepage latest news section shows up to 3 published news entries
- [ ] News cards show cover image (if exists), title, date, excerpt
- [ ] `/aktualnosci` lists published news entries in reverse chronological order
- [ ] `/aktualnosci` shows pagination
- [ ] `/aktualnosci/{slug}` shows single news entry with Markdown body rendered
- [ ] News detail has back link to News
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

- Eager-load relationships (`wojewodztwo`, `kategorie`) to prevent N+1 queries
- Paginate news listing (12 per page)
- Use `thumbnail` conversion for card images (smaller file sizes)
- Cache latest objects and news queries for homepage (short TTL, e.g., 5 minutes)

---

## Accessibility Considerations

- Semantic HTML: `<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`
- All images have meaningful `alt` attributes
- Form inputs have associated `<label>` elements
- Focus states visible on all interactive elements
- Skip-to-content link
- Color contrast meets WCAG AA
- Keyboard navigable header and forms
