# Technology Stack Recommendation

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

## Chat Summary

The project is a public web application for a Polish catalog of sightseeing objects, with a map-first catalog, object detail pages, filters, fuzzy search, nearby objects, printable pages, a simple blog/news section, and an editorial CMS for a small nontechnical team.

Initial stack options discussed included:

- Next.js + Payload CMS + PostgreSQL/PostGIS.
- Astro + Directus + PostgreSQL/PostGIS.
- Next.js + Sanity + Supabase/PostGIS.
- Django + GeoDjango + PostgreSQL/PostGIS.
- Ruby on Rails + PostgreSQL/PostGIS.
- WordPress + custom post types.

The first recommendation was Next.js + Payload CMS + PostgreSQL/PostGIS because it keeps the frontend, CMS, API, and structured content model close together in TypeScript. After considering PHP + Laravel + Filament, the preferred direction shifted toward Laravel if the developer is comfortable in that ecosystem, because it can deliver the CMS and CRUD-heavy editorial workflows faster.

The final architectural question was whether native Blade templates can be mixed with Inertia + Svelte only for the interactive catalog page. The answer was yes: this is a good fit, as long as the mix happens by route rather than trying to render one page partly as Blade and partly as Inertia.

## Recommended Stack

| Layer | Recommendation |
|---|---|
| Backend/app framework | Laravel |
| CMS/admin panel | Filament |
| Public static/content pages | Blade templates |
| Interactive catalog page | Inertia + Svelte |
| Database | PostgreSQL + PostGIS |
| Search | Laravel Scout database driver first; Meilisearch or Typesense later if needed |
| Map | Leaflet for beta; MapLibre later if advanced vector styling is needed |
| Media/files | Laravel filesystem with S3-compatible storage; optionally Spatie Media Library |
| Styling | Tailwind CSS |
| Hosting | Laravel Forge/VPS, Hetzner, DigitalOcean, Render, Fly.io, Railway, or similar |

## Recommended Routing Approach

Use Blade for mostly content-oriented and document-like public pages:

```text
/                         Blade
/obiekty/[slug]           Blade
/aktualnosci              Blade
/aktualnosci/[slug]       Blade
/kontakt                  Blade
/cms/*                    Filament
/katalog                  Inertia + Svelte
```

This keeps the application simple while using Svelte only where it provides clear value: map interaction, filters, active chips, selected marker state, result updates, and mobile map/list switching.

## Why Laravel + Filament Fits This Project

Laravel provides strong foundations for routing, validation, authentication, authorization policies, file storage, mail, migrations, queues, testing, and Eloquent models.

Filament is well suited for the editorial CMS requirements:

- Object management.
- Article/news management.
- Publish/unpublish status.
- Form validation.
- Image upload.
- Tables with filters and actions.
- Dashboard widgets.
- Safe editorial workflows without building a custom CMS from scratch.

PostgreSQL + PostGIS fits the geospatial requirements:

- Object coordinates.
- Nearby-object search with 5 km and 20 km fallback.
- Polygon/GeoJSON support for parks and area objects.
- Efficient spatial indexes.

## Why Use Inertia + Svelte for `/katalog`

The catalog is the most interactive part of the product. It needs:

- Map/list synchronization.
- Immediate filter updates.
- Search state.
- Active filter chips.
- Marker popups.
- Mobile segmented control for map/list.
- Mobile filter bottom sheet.
- Shareable URLs with query parameters.

Inertia lets Laravel remain the server-side routing and data layer while Svelte owns the interactive page UI. This avoids building a separate API-first SPA for the whole application.

## Recommended `/katalog` Data Flow

Use a Laravel controller that renders an Inertia page:

```php
Route::get('/katalog', CatalogController::class)
    ->name('catalog.index');
```

```php
use Inertia\Inertia;

class CatalogController
{
    public function __invoke(Request $request)
    {
        return Inertia::render('Catalog/Index', [
            'filters' => [
                'q' => $request->query('q'),
                'wojewodztwo' => $request->query('wojewodztwo'),
                'category' => $request->query('category'),
                'unesco' => $request->boolean('unesco'),
            ],
            'objects' => ObjectResource::collection(
                ObjectQuery::fromRequest($request)->paginate()
            ),
            'categories' => CategoryResource::collection(Category::tree()),
            'voivodeships' => Voivodeship::all(),
        ]);
    }
}
```

Filter changes should update the URL query string. Start with Inertia visits using `replace`, `preserveState`, and partial reloads. If map performance becomes a problem later, keep the initial page in Inertia and move result refreshes to a JSON endpoint.

## Suggested File Structure

```text
resources/views/
  layouts/public.blade.php
  home.blade.php
  objects/show.blade.php
  articles/index.blade.php
  articles/show.blade.php
  app.blade.php

resources/js/
  app.ts
  Pages/
    Catalog/
      Index.svelte
      FilterSidebar.svelte
      MobileFilterSheet.svelte
      SearchBar.svelte
      ActiveFilterChips.svelte
      CatalogMap.svelte
      MapPopup.svelte
      ObjectGrid.svelte
      ObjectCard.svelte
```

## Strengths

- Fast CMS delivery with Filament.
- Strong fit for a small team and beta timeline.
- Blade keeps simple public pages simple and SEO-friendly.
- Svelte is used only where rich interaction is needed.
- Laravel keeps backend, CMS, auth, mail, storage, and validation in one cohesive app.
- PostgreSQL/PostGIS directly supports the map and nearby-object requirements.
- Hosting can stay conventional and cost-effective.

## Weaknesses and Risks

- PostGIS in Laravel may require raw SQL or custom query scopes for distance and geometry operations.
- A pure Blade/Livewire catalog would be awkward for map-heavy interactions, so `/katalog` should really be handled by Svelte or another frontend adapter.
- Inertia and Blade should be mixed by route, not inside the same rendered page body.
- Laravel Scout database search is acceptable for beta, but true typo tolerance may require Meilisearch or Typesense later.
- Filament is an admin-panel framework, not a traditional standalone CMS, so content modeling must be designed carefully.

## Final Recommendation

Use:

```text
Laravel + Filament + PostgreSQL/PostGIS + Blade + Inertia/Svelte + Leaflet
```

This is a pragmatic and well-balanced stack for the product. It gives the editorial team a capable CMS, keeps most public pages simple, and reserves Svelte for the one area where the UX needs a richer client-side application.
