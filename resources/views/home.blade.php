@extends('layouts.public')

@section('title', 'Wędruj z Nami — Katalog obiektów krajoznawczych Polski')

@section('content')
@vite('resources/css/home.css')
<section class="homepage-hero relative isolate overflow-hidden dark-box">
    <div class="absolute inset-0">
        <img
            src="{{ asset('images/tyniec/main.webp') }}"
            alt="Panorama Tyniecka z Wisłą i klasztorem w tle"
            author="Fot. Magdalena Grabowska"
            class="h-full w-full object-cover object-center"
            fetchpriority="high">
        <div class="absolute inset-0 bg-linear-to-r from-pine-950/30 via-pine-900/12 to-transparent"></div>
    </div>
    <div class="homepage-grid-overlay opacity-70" aria-hidden="true"></div>
    <div class="relative mx-auto flex min-h-[72svh] max-w-7xl items-end px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
        <div class="hero-panel max-w-2xl">
            <p class="hero-eyebrow mb-5 section-label">
                PTTK • Katalog obiektów krajoznawczych
            </p>
            <h1 class="text-pine-900 max-w-3xl text-3xl leading-[0.94] text-balance sm:text-5xl lg:text-4xl xl:text-5xl">
                Odkrywaj Polskę
            </h1>
            <p class="section-copy mt-6 max-w-xl sm:text-lg">
                Zabytki, rezerwaty, muzea i miejsca pamięci zebrane w jednym katalogu PTTK — gotowe do wygodnego przeglądania na każdym ekranie.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a
                    href="{{ route('catalog.index') }}"
                    class="btn-primary">
                    Przeglądaj katalog
                </a>
                <a
                    href="{{ route('catalog.index') }}"
                    class="btn-glass">
                    Przeglądaj katalog
                </a>
            </div>
        </div>
    </div>
</section>

<section class="border-y border-stone-200/80 bg-sand-50">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1.18fr)_minmax(0,1.82fr)] lg:items-start lg:px-8 lg:py-12">
        <div class="max-w-xl">
            <p class="section-label">Zaufane źródło PTTK</p>
            <h2 class="font-heading mt-2 text-2xl font-semibold text-stone-950 sm:text-3xl">
                Katalog oparty na rzetelnej bazie obiektów z całego kraju.
            </h2>
            <p class="section-copy mt-4">
                Selekcja i opis obiektów pomagają szybko przejść od inspiracji do planu trasy.
            </p>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <p class="text-sm text-stone-500">Opublikowane obiekty</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['objects'], 0, ',', ' ') }}</p>
            </div>
            <div class="metric-card">
                <p class="text-sm text-stone-500">Kategorie miejsc</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['object_types'], 0, ',', ' ') }}</p>
            </div>
            <div class="metric-card">
                <p class="text-sm text-stone-500">Województwa w katalogu</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['voivodeships'], 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>
</section>

@if($latestObjects->isNotEmpty())
<section class="home-latest-bg py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 pb-8 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="section-label">Kuratorski wybór</p>
                <h2 class="section-heading">
                    Najnowsze obiekty dodane do katalogu
                </h2>
                <p class="section-copy mt-3">
                    Świeże dodatki w katalogu — sprawdź, co warto zapisać na mapę.
                </p>
            </div>
            <a
                href="{{ route('catalog.index') }}"
                class="btn-outline">
                Zobacz cały katalog
            </a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($latestObjects as $object)
            <a
                href="{{ route('catalog.show', $object->slug) }}"
                class="home-card-shadow group overflow-hidden rounded-4xl border border-stone-200/80 bg-[#fffdf9] transition hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pine-700 focus-visible:ring-offset-2">
                <div class="aspect-4/3 overflow-hidden bg-stone-200">
                    <picture>
                        @if($object->card_webp_url)
                        <source srcset="{{ $object->card_webp_url }}" type="image/webp">
                        @endif
                        <img
                            src="{{ $object->card_url }}"
                            alt="{{ $object->title }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy">
                    </picture>
                </div>
                <div class="flex h-full flex-col gap-4 p-5">
                    <div class="flex flex-wrap gap-2 text-xs font-medium text-stone-500">
                        @foreach($object->objectTypes->take(2) as $objectType)
                        <span class="rounded-full bg-stone-100 px-3 py-1">{{ $objectType->name }}</span>
                        @endforeach
                    </div>
                    <h3 class="card-title card-title--object transition-colors group-hover:text-pine-800">
                        {{ $object->title }}
                    </h3>
                    @if($object->locality?->voivodeship)
                    <p class="text-sm text-stone-600">
                        {{ $object->locality->voivodeship->name }}
                    </p>
                    @endif
                    <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-pine-800">
                        Otwórz kartę obiektu
                        <span aria-hidden="true">→</span>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($browseTypes->isNotEmpty())
<section class="dark-box py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.6fr)] lg:items-start">
            <div>
                <p class="section-label">Przeglądaj według typu</p>
                <h2 class="section-heading">
                    Wybierz kategorię i zawęź trasę.
                </h2>
                <p class="section-copy mt-3 max-w-md">
                    Zamki, muzea, rezerwaty i obiekty sakralne — wybierz kategorię i odkrywaj dalej.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($browseTypes as $objectType)
                <a
                    href="{{ route('catalog.index', ['objectTypes' => [$objectType->getKey()]]) }}"
                    class="browse-type-card group">
                    <div>
                        <p class="card-kicker">Typ obiektu</p>
                        <h3 class="card-title card-title--browse mt-3">{{ $objectType->name }}</h3>
                        @if($objectType->description)
                        <p class="card-meta mt-3">{{ \Illuminate\Support\Str::limit($objectType->description, 110) }}</p>
                        @endif
                    </div>
                    <div class="mt-6 flex items-center justify-between gap-4">
                        <span class="card-meta">{{ number_format($objectType->published_objects_count, 0, ',', ' ') }} obiektów</span>
                        <span class="text-sm font-semibold text-pine-800 transition group-hover:translate-x-1">
                            Przeglądaj
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<section class="home-map-section overflow-hidden bg-sand-50 py-16 sm:py-20">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:px-8">
        <div class="rounded-4xl border border-stone-200 bg-white px-6 py-8 shadow-sm sm:px-8 sm:py-10">
            <p class="section-label">Szlak na mapie</p>
            <h2 class="section-heading">
                Zobacz, które obiekty są blisko siebie i zaplanuj objazd.
            </h2>
            <p class="section-copy mt-4 max-w-xl">
                Widok mapy pomaga odkrywać klastry atrakcji, łączyć miejsca w sensowne pętle i planować kolejne etapy podróży.
            </p>
            <a
                href="{{ route('catalog.index') }}"
                class="mt-8 btn-primary">
                Przeglądaj katalog
            </a>
        </div>
        <div class="map-panel relative rounded-4xl border border-stone-200 p-5 shadow-sm sm:p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="map-feature-card rounded-3xl border border-stone-200 bg-white/85 p-4 shadow-sm backdrop-blur-sm">
                    <p class="text-sm text-stone-500">Szybki start</p>
                    <p class="mt-3 font-heading text-2xl font-semibold text-stone-950">Mapa</p>
                    <p class="card-meta mt-2">Przesuwaj mapę i klikaj w obiekty — cały czas widzisz region.</p>
                </div>
                <div class="map-feature-card rounded-3xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-stone-500">Dopasowany do ekranu</p>
                    <h4 class="mt-3 font-heading text-2xl font-semibold text-stone-950">Mapa i lista</h4>
                    <p class="card-meta mt-2">Na dużym ekranie mapa i lista działają razem, a na telefonie katalog skupia się na wygodnym przeglądaniu wyników.</p>
                </div>
            </div>
            <div class="mt-5 grid gap-3">
                <div class="map-highlight rounded-3xl border border-pine-200/60 bg-pine-50 px-4 py-4 card-meta text-pine-950">
                    Filtruj po województwie, typie obiektu i UNESCO — szybciej znajdziesz to, czego szukasz.
                </div>
                <div class="rounded-3xl border border-dashed border-stone-300 px-4 py-4 card-meta">
                    Nie wiesz, gdzie jechać? Otwórz katalog, wybierz region i rozwiń mapę, gdy chcesz zaplanować trasę.
                </div>
            </div>
        </div>
    </div>
</section>

@if($latestNews->isNotEmpty())
<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="section-label">Aktualności</p>
                <h2 class="section-heading">
                    Co słychać na szlaku
                </h2>
            </div>
            <a
                href="{{ route('news.index') }}"
                class="btn-outline hover:bg-stone-50">
                Zobacz wszystkie
            </a>
        </div>
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach($latestNews as $newsItem)
            <a
                href="{{ route('news.show', $newsItem->slug) }}"
                class="news-card-shadow group overflow-hidden rounded-[1.75rem] border border-stone-200 bg-white transition hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pine-700 focus-visible:ring-offset-2">
                @if($newsItem->has_cover_image)
                <div class="aspect-3/2 overflow-hidden bg-stone-200">
                    <picture>
                        @if($newsItem->cover_thumbnail_webp_url)
                        <source srcset="{{ $newsItem->cover_thumbnail_webp_url }}" type="image/webp">
                        @endif
                        <img
                            src="{{ $newsItem->cover_thumbnail_url }}"
                            alt="{{ $newsItem->title }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy">
                    </picture>
                </div>
                @endif
                <div class="p-5">
                    <time class="text-sm text-stone-500" datetime="{{ $newsItem->published_at->format('Y-m-d') }}">
                        {{ $newsItem->published_at->format('d.m.Y') }}
                    </time>
                    <h3 class="card-title card-title--news mt-2 transition-colors group-hover:text-pine-800">
                        {{ $newsItem->title }}
                    </h3>
                    @if($newsItem->excerpt)
                    <p class="card-meta mt-3 line-clamp-3">
                        {{ $newsItem->excerpt }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
