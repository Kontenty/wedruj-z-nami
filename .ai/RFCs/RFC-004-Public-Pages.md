# RFC-004: Public Pages (Homepage, Blog, Contact)

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*; "article" = *artykuł*

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001  
**Successors:** RFC-007 (contact form enhancement)

---

## Summary

Build all Blade-based public pages: the homepage with project description and latest objects, the articles listing page (News), individual article detail pages, and a contact page. These pages use Blade templates, Tailwind CSS, and serve as the informational and editorial layer of the application.

---

## Features / Requirements Addressed

- US-014: Contact team (form or email link)
- US-015: Homepage with project description
- US-016: Latest objects on homepage
- US-017: Article listing (News)
- US-018: Article detail page
- Public navigation (header, footer)
- Responsive design (mobile + desktop)
- WCAG accessibility (basic)

---

## Previous / Next

- **Builds on:** RFC-001 (models and data exist)
- **Built by future:** RFC-007 (contact form backend, nearby objects on homepage enhancement)

---

## Technical Approach

### Routing

```php
// routes/web.php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;

Route::get('/', HomeController::class)->name('home');
Route::get('/aktualnosci', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/aktualnosci/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/kontakt', [ContactController::class, 'index'])->name('contact');
Route::post('/kontakt', [ContactController::class, 'store'])->name('contact.store');
```

### Layout: `resources/views/layouts/public.blade.php`

Shared layout for all public pages:

```html
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kanon - Sightseeing Objects Catalog of Poland')</title>
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
            <a href="{{ route('articles.index') }}">News</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>
    </header>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Public Footer --}}
    <footer>
        <div>
            <p>Kanon — Sightseeing Objects Catalog of Poland</p>
            <a href="{{ route('contact') }}">Contact</a>
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

        $latestArticles = Artkul::published()
            ->limit(3)
            ->get();

        return view('home', compact('latestObjects', 'latestArticles'));
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
   - Each card links to `/obiekty/{slug}`

4. **Latest articles teaser**
   - Grid of up to 3 article cards (cover image, title, date, excerpt)
   - Each card links to `/aktualnosci/{slug}`
   - "View All" link to `/aktualnosci`

5. **Contact prompt**
   - Short "Have feedback?" text
   - Link to `/kontakt`

### Article Listing: `ArticleController` + `resources/views/articles/index.blade.php`

**Controller:**

```php
class ArticleController extends Controller
{
    public function index(): View
    {
        $articles = Artkul::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $latestObjects = Obiekt::published()
            ->with('wojewodztwo')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('articles.index', compact('articles', 'latestObjects'));
    }

    public function show(string $slug): View
    {
        $article = Artkul::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('articles.show', compact('article'));
    }
}
```

**Page structure:**
- Page title: "News"
- Optional short intro paragraph
- Article card grid:
  - Cover image (if exists)
  - Title
  - Publication date
  - Excerpt (if exists)
  - Link to article
- Pagination
- Latest objects section at bottom

### Article Detail: `resources/views/articles/show.blade.php`

**Page structure:**
- Back link: "← News"
- Title
- Publication date
- Cover image (if exists)
- Markdown body rendered to HTML (using `Str::markdown()` or a Markdown parser)
- Contextual CTA at bottom: "Show Map" / "Browse Catalog"

### Contact Page: `ContactController` + `resources/views/contact.blade.php`

**Controller:**

```php
class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // For beta: send email to team
        Mail::to('kontakt@kanon.example.com')->send(new ContactMessage($validated));

        return redirect()->route('contact')
            ->with('success', 'Message sent. Thank you!');
    }
}
```

**Page structure:**
- Page title: "Contact"
- Contact form: name, email, message, submit button
- OR display email address directly (team preference)
- Success/error flash message handling

### Markdown Rendering

For article bodies, use Laravel's `Str::markdown()`:

```blade
<div class="prose prose-lg max-w-none">
    {!! Str::markdown($article->body) !!}
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
                <a href="{{ route('articles.index') }}" class="...">News</a>
                <a href="{{ route('contact') }}" class="...">Contact</a>
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
        <a href="{{ route('contact') }}">Contact</a>
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
  └── latestArticles (Artkul::published, limit 3)

[RFC-001 Data] → ArticleController::index → articles/index.blade.php
  ├── articles (Artkul::published, paginated)
  └── latestObjects (Obiekt::published, limit 4)

[RFC-001 Data] → ArticleController::show → articles/show.blade.php
  └── article (Artkul by slug, published only)

[ContactController] → contact.blade.php → ContactMessage mail
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
│  │art │ │art │ │art │                  │
│  └────┘ └────┘ └────┘                  │
│  [View All]                             │
│                                         │
├─────────────────────────────────────────┤
│  Have feedback? [Contact Us]            │
│                                         │
├─────────────────────────────────────────┤
│  Footer                                 │
└─────────────────────────────────────────┘
```

### Responsive Behavior

- Mobile: single column, hamburger nav
- Tablet: 2-column card grids
- Desktop: 4-column latest objects, 3-column articles
- Header collapses to hamburger on mobile

### Object Card (Homepage)

```blade
<a href="{{ route('objects.show', $object->slug) }}" class="group">
    <img src="{{ $object->thumbnail_url }}" alt="{{ $object->title }}" class="..." />
    <h3 class="...">{{ $object->title }}</h3>
    <p class="...">{{ $object->wojewodztwo->name }}</p>
</a>
```

### Article Card

```blade
<a href="{{ route('articles.show', $article->slug) }}" class="group">
    @if($article->cover_image_url)
        <img src="{{ $article->cover_thumbnail_url }}" alt="" class="..." />
    @endif
    <time class="...">{{ $article->published_at->format('d.m.Y') }}</time>
    <h3 class="...">{{ $article->title }}</h3>
    @if($article->excerpt)
        <p class="...">{{ $article->excerpt }}</p>
    @endif
</a>
```

---

## Acceptance Criteria

- [ ] Public layout with header and footer renders on all public pages
- [ ] Header navigation links work: home, map, catalog, news, contact
- [ ] Header responsive: hamburger on mobile, full nav on desktop
- [ ] Homepage loads with project description and CTAs
- [ ] Homepage "Show Map" links to `/katalog`
- [ ] Homepage "Browse Catalog" links to `/katalog?view=list`
- [ ] Homepage latest objects section shows up to 4 published objects
- [ ] Object cards show image, title, and voivodeship
- [ ] Homepage latest articles section shows up to 3 published articles
- [ ] Article cards show cover image (if exists), title, date, excerpt
- [ ] `/aktualnosci` lists published articles in reverse chronological order
- [ ] `/aktualnosci` shows pagination
- [ ] `/aktualnosci/{slug}` shows single article with Markdown body rendered
- [ ] Article detail has back link to News
- [ ] Article detail has contextual CTA to catalog/map
- [ ] `/kontakt` displays contact form with name, email, message fields
- [ ] Contact form validates required fields
- [ ] Contact form sends email on successful submission
- [ ] Contact form shows success confirmation
- [ ] All pages responsive on mobile and desktop
- [ ] Unpublished objects/articles do not appear on public pages
- [ ] Pest tests for HomeController, ArticleController, ContactController
- [ ] Pest tests for contact form validation and submission

---

## Testing Strategy

### Feature Tests (Pest)

- Homepage loads and contains expected sections
- Homepage latest objects are limited to 4, ordered by published_at
- Homepage latest articles are limited to 3
- `/aktualnosci` returns paginated published articles
- `/aktualnosci` does not show unpublished articles
- `/aktualnosci/{slug}` returns article with matching slug
- `/aktualnosci/{nonexistent}` returns 404
- `/kontakt` renders form
- `/kontakt` POST with valid data sends mail and redirects with success
- `/kontakt` POST with invalid data returns validation errors
- Contact email contains expected content

---

## Error Handling

- 404 page for non-existent article slugs
- Contact form validation errors returned to form with old input
- Mail sending failure: catch exception, return error flash message
- Missing images: fallback URL from Spatie Media Library

---

## Performance Considerations

- Eager-load relationships (`wojewodztwo`, `kategorie`) to prevent N+1 queries
- Paginate article listing (12 per page)
- Use `thumbnail` conversion for card images (smaller file sizes)
- Cache latest objects and articles queries for homepage (short TTL, e.g., 5 minutes)

---

## Accessibility Considerations

- Semantic HTML: `<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`
- All images have meaningful `alt` attributes
- Form inputs have associated `<label>` elements
- Focus states visible on all interactive elements
- Skip-to-content link
- Color contrast meets WCAG AA
- Keyboard navigable header and forms
