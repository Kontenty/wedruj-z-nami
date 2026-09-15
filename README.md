# Wędruj z Nami

Ogólnopolski katalog obiektów krajoznawczych PTTK — publiczny serwis webowy prezentujący obiekty krajoznawcze w Polsce, zintegrowany z interaktywną mapą, umożliwiający przeglądanie, filtrowanie i przeglądanie szczegółowych informacji utrzymywanych redakcyjnie przez PTTK.

Nationwide PTTK Sightseeing Objects Catalog — a public editorial web service presenting sightseeing objects in Poland, integrated with an interactive map for browsing, filtering and viewing detailed object information curated by PTTK.

> Stack: **Laravel 13 · PHP 8.4+ · MariaDB · Svelte 5 · Inertia v3 · Filament v4 · Tailwind v4 · Wayfinder · Pest v4 · Pint · MapLibre GL · Spatie Media Library**

## Spis treści / Table of Contents

- [Wymagania / Requirements](#wymagania--requirements)
- [Szybki start / Quick Start](#szybki-start--quick-start)
- [Skrypty / Scripts](#skrypty--scripts)
- [Struktura projektu / Project Structure](#struktura-projektu--project-structure)
- [Funkcjonalności / Features](#funkcjonalności--features)
- [Panel CMS (Filament)](#panel-cms-filament)
- [Routing](#routing)
- [Testy / Testing](#testy--testing)
- [Licencja / License](#licencja--license)

## Wymagania / Requirements

| Zależność | Wersja                     |
| --------- | -------------------------- |
| PHP       | ^8.4                       |
| Node.js   | >= 20                      |
| MariaDB   | 10.11 (via Docker Compose) |
| Composer  | 2.x                        |
| npm / bun | npm (lockfile w repo)      |

## Szybki start / Quick Start

```bash
# 1. Sklonuj i zainstaluj zależności
git clone git@github.com:Kontenty/wedruj-z-nami.git
cd wedruj-z-nami

# 2. Jednorazowy setup (composer + .env + migracje + build)
composer setup

# 3. Uruchom bazę danych
docker compose up -d mariadb

# 4. Wypełnij bazę danymi (opcjonalnie z obrazami z database/fixtures/images/)
php artisan migrate --seed

# 5. Uruchom pełny stos deweloperski (server + queue + pail + vite)
composer dev
```

Aplikacja dostępna pod `http://localhost:8000`. Panel CMS pod `http://localhost:8000/cms`.

### Konfiguracja środowiska

```bash
cp .env.example .env
php artisan key:generate
```

Kluczowe zmienne w `.env.example`:

- `APP_LOCALE=pl`, `APP_FAKER_LOCALE=pl_PL` — domyślny język polski
- `DB_CONNECTION=mariadb` — MariaDB przez Docker Compose
- `FILESYSTEM_DISK=local`, media na dysku `public` (Spatie Media Library)

> Obrazy do seedowania umieść w `database/fixtures/images/` (katalog ignorowany w `.gitignore` — trzymaj w repo tylko lekki zestaw referencyjny).

## Skrypty / Scripts

### Composer

| Komenda               | Opis                                                                         |
| --------------------- | ---------------------------------------------------------------------------- |
| `composer setup`      | Instalacja + `.env` + migracje + `npm build` + `filament:assets`             |
| `composer dev`        | `serve` + `queue:listen` + `pail` + `vite` współbieżnie (via `concurrently`) |
| `composer lint`       | `pint --parallel`                                                            |
| `composer lint:check` | `pint --parallel --test`                                                     |
| `composer ci:check`   | `lint:check` + `format:check` + `types:check` + testy                        |
| `composer test`       | `config:clear` + przygotowanie bazy + `lint:check` + `php artisan test`      |

### npm

| Komenda                | Opis                                      |
| ---------------------- | ----------------------------------------- |
| `npm run dev`          | Vite dev server (HMR)                     |
| `npm run build`        | Build produkcyjny                         |
| `npm run build:ssr`    | Build + SSR                               |
| `npm run format`       | `prettier --write resources/`             |
| `npm run format:check` | `prettier --check resources/`             |
| `npm run lint`         | `eslint . --fix`                          |
| `npm run lint:check`   | `eslint .`                                |
| `npm run types:check`  | `svelte-check --tsconfig ./tsconfig.json` |

### Formatowanie PHP

```bash
vendor/bin/pint --dirty --format agent   # tylko zmienione pliki
vendor/bin/pint --format agent            # całe repo
```

## Struktura projektu / Project Structure

```
app/
├── Filament/Resources/     # Filament v4 resources (Articles, Localities, ObjectTypes, SightseeingObjects, Users)
├── Http/
│   ├── Controllers/        # HomeController, CatalogController, NewsController, Settings/*
│   └── Resources/          # ObjectResource, ObjectDetailResource, ObjectTypeResource
├── Models/                 # Article, Locality, ObjectType, SightseeingObject, Voivodeship, User
│   └── Concerns/HasSlug
└── Actions/Fortify/        # Fortify auth actions

resources/
├── css/app.css, home.css
├── js/
│   ├── app.ts
│   ├── actions/            # Wayfinder: generowane akcje kontrolerów
│   ├── routes/             # Wayfinder: generowane funkcje tras nazwanych
│   ├── pages/
│   │   ├── Catalog/        # Index.svelte, Show.svelte, ObjectCard, CatalogMap, FilterSidebar, ...
│   │   ├── Dashboard.svelte
│   │   ├── auth/
│   │   └── settings/
│   ├── layouts/
│   ├── components/         # bits-ui, shadcn-style
│   └── lib/utils.ts
└── views/home.blade.php    # Strona główna (Blade + cache)

routes/
├── web.php                 # home, catalog.*, news.*, dashboard
├── settings.php            # profile, security
└── console.php

database/
├── migrations/             # MariaDB + typy spatial (geometry), fulltext
├── factories/
├── seeders/
└── fixtures/images/        # obrazy do seedowania (gitignored)

tests/
├── Feature/
└── Unit/
```

## Funkcjonalności / Features

- **Strona główna** (`/`) — statystyki katalogu, przeglądanie po typach obiektów, najnowsze obiekty i aktualności (Blade, cache 5–15 min).
- **Katalog** (`/katalog`) — paginacja (12/strona), wyszukiwanie po tytule, filtry: województwa, typy obiektów, UNESCO; widok listy + mapa MapLibre GL z klastrowaniem/GeoJSON (`ST_AsGeoJSON`, kolumna `geometry`).
- **Szczegóły obiektu** (`/katalog/{slug}`) — galeria (Spatie Media Library, konwersje `thumbnail_webp`/`card_webp`/`gallery_webp`), informacje praktyczne, mapa obiektu, obiekty w pobliżu (`ST_Distance_Sphere`, promień 20 km).
- **Aktualności** (`/aktualnosci`, `/aktualnosci/{slug}`) — artykuły publikowane.
- **Auth** — Laravel Fortify (rejestracja, reset hasła, weryfikacja e-mail, throttling loginu), dashboard dla zalogowanych.
- **Geoprzestrzenność** — MariaDB `GEOMETRY`, `latitude`/`longitude`, wyszukiwanie `nearby` po geometrii lub współrzędnych.

## Panel CMS (Filament)

Dostępny pod `/cms` (Filament v4).

Resources: **Articles**, **Localities**, **SightseeingObjects**, **ObjectTypes**, **Users**.

Konwencje Filament w projekcie:

- `Filament\Forms\Components\*` — formularze, `Filament\Schemas\Components\*` — layouty, `Filament\Tables\Columns\*` / `Filters\*` — tabele
- `Repeater` używa `->schema()`
- `Select::make('author_id')->relationship('author', 'name')` dla `BelongsTo`
- `->visibility('public')` dla plików publicznych, `->columnSpanFull()` dla pełnej szerokości
- `->live(onBlur: true)` na polach tekstowych

## Routing

| Metoda           | Ścieżka                  | Nazwa              | Kontroler                     |
| ---------------- | ------------------------ | ------------------ | ----------------------------- |
| GET              | `/`                      | `home`             | `HomeController`              |
| GET              | `/katalog`               | `catalog.index`    | `CatalogController`           |
| GET              | `/katalog/{object:slug}` | `catalog.show`     | `CatalogController@show`      |
| GET              | `/aktualnosci`           | `news.index`       | `NewsController@index`        |
| GET              | `/aktualnosci/{slug}`    | `news.show`        | `NewsController@show`         |
| GET              | `/dashboard`             | `dashboard`        | Inertia (auth + verified)     |
| GET/PATCH/DELETE | `/settings/profile`      | `profile.*`        | `Settings\ProfileController`  |
| GET/PUT          | `/settings/security`     | `security.*`       | `Settings\SecurityController` |
| ANY              | `/cms/**`                | `filament.admin.*` | Filament                      |

Frontend używa **Wayfinder** — importy z `@/actions/` (kontrolery) i `@/routes/` (trasy nazwane), plugin `@laravel/vite-plugin-wayfinder` (`formVariants: true`). Hydratacja tras: `vite.config.ts` generuje `resources/js/actions` i `resources/js/routes`.

## Testy / Testing

- Framework: **Pest v4** (`pestphp/pest-plugin-laravel`), baza **MariaDB** (nie SQLite).
- Fabryki + `actingAs()` dla testów uwierzytelnionych/Filament.

```bash
composer test                              # pełna paczka (lint + baza + testy)
php artisan test --compact                 # tylko testy
php artisan test --compact --filter=Catalog
vendor/bin/pest tests/Feature/CatalogTest.php
```

## Licencja / License

MIT — zob. `LICENSE` (szkielet Laravel).
