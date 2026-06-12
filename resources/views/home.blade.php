@extends('layouts.public')

@section('title', 'Wędruj z Nami — Katalog obiektów krajoznawczych Polski')

@section('content')
<section class="homepage-hero relative isolate overflow-hidden bg-stone-950 text-stone-50">
    <div class="absolute inset-0">
        <img
            src="{{ asset('images/wawel/main.jpg') }}"
            alt="Panorama Wawelu nad Wisłą"
            class="h-full w-full object-cover object-center">
        <div
            class="absolute inset-0 bg-linear-to-r from-primary/90 to-transparent"></div>
    </div>
    <div class="homepage-grid-overlay absolute inset-0 opacity-70" aria-hidden="true"></div>
    <div class="relative mx-auto flex min-h-[78svh] max-w-7xl items-end px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
        <div class="max-w-3xl">
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.32em] text-emerald-200">
                PTTK • Polska sieć odkrywania
            </p>
            <h1 class="font-heading max-w-2xl text-4xl font-bold leading-none text-balance sm:text-5xl lg:text-7xl">
                Odkrywaj Polskę przez mapę, miejsca i gotowe tropy podróży.
            </h1>
            <p class="mt-5 max-w-xl text-base leading-7 text-stone-200 sm:text-lg">
                Katalog obiektów krajoznawczych PTTK prowadzi od pierwszego widoku do sprawdzonych miejsc:
                od zamków i muzeów po rezerwaty, skanseny i punkty widokowe.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ route('catalog.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-full bg-emerald-300 px-6 py-3 text-sm font-semibold text-stone-950 transition hover:bg-emerald-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-100 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950">
                    Pokaż mapę
                </a>
                <a
                    href="{{ route('catalog.index', ['view' => 'list']) }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-full border border-white/30 bg-white/8 px-6 py-3 text-sm font-semibold text-white backdrop-blur-xs transition hover:bg-white/14 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-100 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950">
                    Przeglądaj katalog
                </a>
            </div>
        </div>
    </div>
</section>

<section class="border-y border-stone-200/80 bg-stone-50">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,2fr)] lg:px-8 lg:py-8">
        <div class="max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">Zaufane źródło PTTK</p>
            <h2 class="font-heading mt-2 text-2xl font-semibold text-stone-950 sm:text-3xl">
                Katalog budowany na realnych obiektach i zasięgu całego kraju.
            </h2>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-3xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-sm text-stone-500">Opublikowane obiekty</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['objects'], 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-sm text-stone-500">Typy do przeglądania</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['object_types'], 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-sm text-stone-500">Województwa w katalogu</p>
                <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($catalogStats['voivodeships'], 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>
</section>

@if($latestObjects->isNotEmpty())
<section class="bg-[radial-gradient(circle_at_top_left,_rgba(17,94,89,0.14),_transparent_42%),linear-gradient(180deg,_#fcfaf5_0%,_#f1efe6_100%)] py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 pb-8 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">Pierwszy trop</p>
                <h2 class="font-heading mt-2 text-3xl font-semibold text-stone-950 sm:text-4xl">
                    Najnowsze obiekty dodane do katalogu
                </h2>
                <p class="mt-3 text-base leading-7 text-stone-600">
                    Zacznij od świeżo opublikowanych miejsc i przejdź od inspiracji do planu podróży jednym kliknięciem.
                </p>
            </div>
            <a
                href="{{ route('catalog.index', ['view' => 'list']) }}"
                class="inline-flex min-h-11 items-center justify-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-900 transition hover:border-stone-900 hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Zobacz cały katalog
            </a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($latestObjects as $object)
            <a
                href="{{ route('catalog.show', $object->slug) }}"
                class="group overflow-hidden rounded-[2rem] border border-stone-200/80 bg-white shadow-[0_24px_60px_-32px_rgba(24,29,23,0.4)] transition hover:-translate-y-1 hover:shadow-[0_28px_70px_-30px_rgba(20,83,45,0.45)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                <div class="aspect-[4/3] overflow-hidden bg-stone-200">
                    <img
                        src="{{ $object->card_url }}"
                        alt="{{ $object->title }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                </div>
                <div class="flex h-full flex-col gap-4 p-5">
                    <div class="flex flex-wrap gap-2 text-xs font-medium text-stone-500">
                        @foreach($object->objectTypes->take(2) as $objectType)
                        <span class="rounded-full bg-stone-100 px-3 py-1">{{ $objectType->name }}</span>
                        @endforeach
                    </div>
                    <h3 class="font-heading text-xl font-semibold text-stone-950 transition-colors group-hover:text-emerald-800">
                        {{ $object->title }}
                    </h3>
                    @if($object->voivodeship)
                    <p class="text-sm text-stone-600">
                        {{ $object->voivodeship->name }}
                    </p>
                    @endif
                    <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-emerald-800">
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
<section class="bg-stone-950 py-16 text-stone-50 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.6fr)] lg:items-start">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-200">Przeglądaj według typu</p>
                <h2 class="font-heading mt-2 text-3xl font-semibold text-balance sm:text-4xl">
                    Wejdź do katalogu przez kategorię, nie przypadek.
                </h2>
                <p class="mt-3 max-w-md text-base leading-7 text-stone-300">
                    Wybierz to, czego szukasz: miejsca historii, natury, techniki lub codziennej kultury podróżowania.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($browseTypes as $objectType)
                <a
                    href="{{ route('catalog.index', ['view' => 'list', 'objectTypes' => [$objectType->getKey()]]) }}"
                    class="group flex min-h-44 flex-col justify-between rounded-[1.75rem] border border-white/12 bg-white/6 p-5 transition hover:border-emerald-200/50 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-100 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950">
                    <div>
                        <p class="text-sm text-stone-300">Typ obiektu</p>
                        <h3 class="font-heading mt-3 text-2xl font-semibold text-white">{{ $objectType->name }}</h3>
                        @if($objectType->description)
                        <p class="mt-3 text-sm leading-6 text-stone-300">{{ \Illuminate\Support\Str::limit($objectType->description, 110) }}</p>
                        @endif
                    </div>
                    <div class="mt-6 flex items-center justify-between gap-4">
                        <span class="text-sm text-stone-300">{{ number_format($objectType->published_objects_count, 0, ',', ' ') }} obiektów</span>
                        <span class="text-sm font-semibold text-emerald-200 transition group-hover:translate-x-1">
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

<section class="overflow-hidden bg-stone-100 py-16 sm:py-20">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:px-8">
        <div class="rounded-[2rem] bg-stone-900 px-6 py-8 text-stone-50 sm:px-8 sm:py-10">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-200">Szlak na mapie</p>
            <h2 class="font-heading mt-2 text-3xl font-semibold text-balance sm:text-4xl">
                Zobacz, jak obiekty układają się w realną trasę i sąsiedztwa.
            </h2>
            <p class="mt-4 max-w-xl text-base leading-7 text-stone-300">
                Widok mapy pomaga planować objazdy, odkrywać klastry atrakcji i szukać miejsc między kolejnymi etapami podróży.
            </p>
            <a
                href="{{ route('catalog.index') }}"
                class="mt-8 inline-flex min-h-11 items-center justify-center rounded-full bg-emerald-300 px-6 py-3 text-sm font-semibold text-stone-950 transition hover:bg-emerald-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-100 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-900">
                Otwórz mapę katalogu
            </a>
        </div>
        <div class="homepage-map-glow relative rounded-[2rem] border border-stone-200 bg-[linear-gradient(160deg,_rgba(252,250,245,0.95)_0%,_rgba(221,240,226,0.92)_48%,_rgba(247,243,230,0.96)_100%)] p-6 shadow-[0_30px_80px_-45px_rgba(24,29,23,0.5)] sm:p-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.5rem] border border-stone-200 bg-white/80 p-4 backdrop-blur-sm">
                    <p class="text-sm text-stone-500">Szybki start</p>
                    <p class="mt-3 font-heading text-2xl font-semibold text-stone-950">Mapa</p>
                    <p class="mt-2 text-sm leading-6 text-stone-600">Przesuwaj widok i otwieraj obiekty bez opuszczania kontekstu regionu.</p>
                </div>
                <div class="rounded-[1.5rem] border border-stone-200 bg-stone-950 p-4 text-stone-50">
                    <p class="text-sm text-stone-300">Przełącznik widoków</p>
                    <p class="mt-3 font-heading text-2xl font-semibold">Mapa • Lista • Split</p>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Ten sam katalog, różne sposoby przeglądania zależnie od etapu planowania.</p>
                </div>
            </div>
            <div class="mt-5 grid gap-3">
                <div class="rounded-[1.5rem] border border-emerald-200/60 bg-emerald-50 px-4 py-4 text-sm leading-6 text-emerald-950">
                    Szukaj po województwach, typach obiektów i oznaczeniu UNESCO, aby szybciej zawęzić trasę.
                </div>
                <div class="rounded-[1.5rem] border border-dashed border-stone-300 px-4 py-4 text-sm leading-6 text-stone-600">
                    Widok mapy jest najlepszym wejściem, gdy plan zaczyna się od miejsca, a nie od konkretnej nazwy obiektu.
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
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">Aktualności</p>
                <h2 class="font-heading mt-2 text-3xl font-semibold text-stone-950 sm:text-4xl">
                    Co nowego w katalogu i wokół niego
                </h2>
            </div>
            <a
                href="{{ route('news.index') }}"
                class="inline-flex min-h-11 items-center justify-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-900 transition hover:border-stone-900 hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Zobacz wszystkie
            </a>
        </div>
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach($latestNews as $newsItem)
            <a
                href="{{ route('news.show', $newsItem->slug) }}"
                class="group overflow-hidden rounded-[1.75rem] border border-stone-200 bg-white shadow-[0_24px_60px_-36px_rgba(24,29,23,0.35)] transition hover:-translate-y-1 hover:shadow-[0_28px_70px_-36px_rgba(20,83,45,0.35)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                @if($newsItem->has_cover_image)
                <div class="aspect-[3/2] overflow-hidden bg-stone-200">
                    <img
                        src="{{ $newsItem->cover_thumbnail_url }}"
                        alt="{{ $newsItem->title }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                </div>
                @endif
                <div class="p-5">
                    <time class="text-sm text-stone-500" datetime="{{ $newsItem->published_at->format('Y-m-d') }}">
                        {{ $newsItem->published_at->format('d.m.Y') }}
                    </time>
                    <h3 class="font-heading mt-2 text-2xl font-semibold text-stone-950 transition-colors group-hover:text-emerald-800">
                        {{ $newsItem->title }}
                    </h3>
                    @if($newsItem->excerpt)
                    <p class="mt-3 text-sm leading-6 text-stone-600 line-clamp-3">
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